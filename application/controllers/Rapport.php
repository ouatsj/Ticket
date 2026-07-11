<?php defined('BASEPATH') OR exit('No direct script access allowed');
    
    // include the main labraries TCPDF
    require_once(APPPATH . 'libraries/tcpdf/tcpdf.php');
    class Rapport extends MY_Controller
    {
        public $property = array('title' => 'RAPPORTS');
        public $entreprise = stdClass::class;
        
        public function __construct()
        {
            parent::__construct();
            $this->property['update_success'] = FALSE;
            $this->property['INSERT'] = FALSE;
            $this->property['pagetitle'] = utf8_encode(strftime("%d %b %G", now()));
        }

        /**
         * Plafonds pour les exports PDF lourds (libère le worker PHP, n'impacte pas la vente guichet).
         */
        protected function _rapport_limits()
        {
            @ini_set('memory_limit', '512M');
            @set_time_limit(300);
        }
        
        //tirage des recette
        public function recette($ckey)
        {
          $this->entreprise = $this->m_entreprises->get_key($ckey);
            $date1 = $this->input->post('datedebut');
            $date2 = $this->input->post('datefin');
            $typ = $this->input->post('type');
            $gen = $this->input->post('genre');
            $nm = $this->input->post('nom');
            $gid = $this->input->post('gareconnect');
            $atr = roleattribut_guard_post_hint($this->entreprise->ekey);
            $comp = $this->input->post('_compag');
            $ncomp = $this->m_compagnies->getn($comp);

            $ngrd = $this->m_gare_depart->getno($gid);
            
            $dats = explode("-", $date1);
            $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
            $dats1 = explode("-", $date2);
            $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];

            if($this->session->agent->userole === '4')
            {
              $trirecet = $this->m_recette->trirecette($this->entreprise->ekey, $gid, $date1, $date2, $atr, $comp, $typ, $gen, $nm);
              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('RESERVATIONS');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('L', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', 'B', 10);
                          
              $titre = '<h1 align="center">ETATS DES RECETTES DE '.$ncomp->nom_compagnie.' '.$ngrd->garenom.' DU '. $days .' AU '.$days1.'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead> 
                      <tr> 
                        <th width="10%" align="center"><strong>DATE</strong></th>
                        <th width="10%" align="center"><strong>TYPE</strong></th>
                        <th width="10%" align="center"><strong>GENRE</strong></th> 
                        <th width="15%" align="center"><strong>NOM</strong></th>

                        <th width="20%" align="center"><strong>COMMENTAIRE</strong></th>
                        <th width="15%" align="center"><strong>TOTAL</strong></th>
                      </tr>
                  </thead>
                  <tbody>';
                  $recetglobal = 0;
                  $pdf->SetFont('courier', '', 9);
              foreach ($trirecet as $tris => $rect) {
                  $them .= '<tr>
                      <td width="10%" align="center"><strong>' . $rect->date_recet . '</strong></td>
                      <td width="10%" align="center"><strong>' . $rect->type_recet . '</strong></td>
                      <td width="10%" align="center"><strong>' . $rect->type_personnel . '</strong></td>
                      <td width="15%" align="center"><strong>' . $rect->nom . '</strong></td>
                      <td width="20%" align="center"><strong>' . $rect->commentaire_recet .'</strong></td>

                      <td width="15%" align="center"><strong>' . $rect->montant_recet . '</strong></td>
                      </tr>';
                      $recetglobal +=$rect->montant_recet;
              }
              $them .= '<tr>
                      <td width="65%" align="center"><strong>TOTAL</strong></td>
                      <td width="15%" align="center"><strong> '.number_format($recetglobal, 0, '', ' ').'</strong></td>
                                  
                 </tr>';
              $them .= ' </tbody></table>';
              
              $them.= '<h2>RECETTE TOTAL :'. number_format($recetglobal, 0, '', ' ').' </h2>';
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_015.pdf' . '', 'I');
            }

            if($this->session->agent->userole === '18')
            {
              $trirecet = $this->m_recette->adtrirecette($this->entreprise->ekey, $gid, $date1, $date2, $atr, $comp, $typ, $gen, $nm);
              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('RESERVATIONS');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('L', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', 'B', 10);
                          
              $titre = '<h1 align="center">ETATS DES RECETTES DE '.$ncomp->nom_compagnie.' '.$ngrd->garenom.' DU '. $days .' AU '.$days1.'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead> 
                      <tr> 
                        <th width="10%" align="center"><strong>DATE</strong></th>
                        <th width="10%" align="center"><strong>TYPE</strong></th>
                        <th width="10%" align="center"><strong>GENRE</strong></th> 
                        <th width="15%" align="center"><strong>NOM</strong></th>

                        <th width="20%" align="center"><strong>COMMENTAIRE</strong></th>
                        <th width="15%" align="center"><strong>TOTAL</strong></th>
                      </tr>
                  </thead>
                  <tbody>';
                  $recetglobal = 0;
                  $pdf->SetFont('courier', '', 9);
              foreach ($trirecet as $tris => $rect) {
                  $them .= '<tr>
                      <td width="10%" align="center"><strong>' . $rect->date_recet . '</strong></td>
                      <td width="10%" align="center"><strong>' . $rect->type_recet . '</strong></td>
                      <td width="10%" align="center"><strong>' . $rect->type_personnel . '</strong></td>
                      <td width="15%" align="center"><strong>' . $rect->nom . '</strong></td>
                      <td width="20%" align="center"><strong>' . $rect->commentaire_recet .'</strong></td>

                      <td width="15%" align="center"><strong>' . $rect->montant_recet . '</strong></td>
                      </tr>';
                      $recetglobal +=$rect->montant_recet;
              }
              $them .= '<tr>
                      <td width="65%" align="center"><strong>TOTAL</strong></td>
                      <td width="15%" align="center"><strong> '.number_format($recetglobal, 0, '', ' ').'</strong></td>
                                  
                 </tr>';
              $them .= ' </tbody></table>';
              
              $them.= '<h2>RECETTE TOTAL :'. number_format($recetglobal, 0, '', ' ').' </h2>';
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_015.pdf' . '', 'I');
            }

            if($this->session->agent->userole === '1' OR $this->session->agent->userole === '2')
            {
              $trirecet = $this->m_recette->trirecetteadmin($this->entreprise->ekey, $gid, $date1, $date2, $comp, $typ, $gen, $nm);
              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('RESERVATIONS');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('L', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', 'B', 10);
                          
              $titre = '<h1 align="center">ETATS DES RECETTES DE '.$ncomp->nom_compagnie.' '.$ngrd->garenom.' DU '. $days .' AU '.$days1.'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead> 
                      <tr> 
                        <th width="10%" align="center"><strong>DATE</strong></th>
                        <th width="10%" align="center"><strong>TYPE</strong></th>
                        <th width="10%" align="center"><strong>GENRE</strong></th> 
                        <th width="15%" align="center"><strong>NOM</strong></th>

                        <th width="20%" align="center"><strong>COMMENTAIRE</strong></th>
                        <th width="15%" align="center"><strong>TOTAL</strong></th>
                      </tr>
                  </thead>
                  <tbody>';
                  $recetglobal = 0;
                  $pdf->SetFont('courier', '', 9);
              foreach ($trirecet as $tris => $rect) {
                  $them .= '<tr>
                      <td width="10%" align="center"><strong>' . $rect->date_recet . '</strong></td>
                      <td width="10%" align="center"><strong>' . $rect->type_recet . '</strong></td>
                      <td width="10%" align="center"><strong>' . $rect->type_personnel . '</strong></td>
                      <td width="15%" align="center"><strong>' . $rect->nom . '</strong></td>
                      <td width="20%" align="center"><strong>' . $rect->commentaire_recet .'</strong></td>

                      <td width="15%" align="center"><strong>' . $rect->montant_recet . '</strong></td>
                      </tr>';
                      $recetglobal +=$rect->montant_recet;
              }
              $them .= '<tr>
                      <td width="65%" align="center"><strong>TOTAL</strong></td>
                      <td width="15%" align="center"><strong> '.number_format($recetglobal, 0, '', ' ').'</strong></td>
                                  
                 </tr>';
              $them .= ' </tbody></table>';
              
              $them.= '<h2>RECETTE TOTAL :'. number_format($recetglobal, 0, '', ' ').' </h2>';
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_015.pdf' . '', 'I');
            }
            else
            {
              $trirecets = $this->m_recette->trirecette_adjoint($this->entreprise->ekey, $gid, $atr, $date1, $date2, $comp, $typ, $gen, $nm);
              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('RESERVATIONS');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('L', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', 'B', 10);
                          
              $titre = '<h1 align="center">ETATS DES RECETTES DE '.$ncomp->nom_compagnie.' '.$ngrd->garenom.' DU '. $days .' AU '.$days1.'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead> 
                      <tr> 
                        <th width="10%" align="center"><strong>DATE</strong></th>
                        <th width="10%" align="center"><strong>TYPE</strong></th>
                        <th width="10%" align="center"><strong>GENRE</strong></th> 
                        <th width="15%" align="center"><strong>NOM</strong></th>

                        <th width="20%" align="center"><strong>COMMENTAIRE</strong></th>
                        <th width="15%" align="center"><strong>TOTAL</strong></th>
                      </tr>
                  </thead>
                  <tbody>';
                  $recetglobal = 0;
                  $pdf->SetFont('courier', '', 9);
              foreach ($trirecets as $tris => $rect) {
                  $them .= '<tr>
                      <td width="10%" align="center"><strong>' . $rect->date_recet . '</strong></td>
                      <td width="10%" align="center"><strong>' . $rect->type_recet . '</strong></td>
                      <td width="10%" align="center"><strong>' . $rect->type_personnel . '</strong></td>
                      <td width="15%" align="center"><strong>' . $rect->nom . '</strong></td>
                      <td width="20%" align="center"><strong>' . $rect->commentaire_recet .'</strong></td>

                      <td width="15%" align="center"><strong>' . $rect->montant_recet . '</strong></td>
                      </tr>';
                       $recetglobal +=$rect->montant_recet;
              }
              
              $them .= '<tr>
                      <td width="65%" align="center"><strong>TOTAL</strong></td>
                      <td width="15%" align="center"><strong> '.number_format($recetglobal, 0, '', ' ').'</strong></td>
                                  
                 </tr>';
              $them .= ' </tbody></table>';
              
              $them.= '<h2>RECETTE TOTAL :'. number_format($recetglobal , 0, '', ' ').' </h2>';
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_015.pdf' . '', 'I');
              //============================================================+
              // END OF FILE
              //============================================================+
            }
            
        }

        public function recettecr($ckey)
        {
          $this->entreprise = $this->m_entreprises->get_key($ckey);
            $date1 = $this->input->post('datedebutcr');
            $date2 = $this->input->post('datefincr');
            $typ = $this->input->post('typecr');
            $gen = $this->input->post('genrecr');
            $nm = $this->input->post('nomcr');
            $gid = $this->input->post('gareconnectcr');
            $atr = roleattribut_guard_post_hint($this->entreprise->ekey, 'gareconnect', 'userconnectedcr');
            $comp = $this->input->post('_compagcr');
            
            $ncomp = $this->m_compagnies->getn($comp);
            $serole = $this->m_compte_user->attcpus($atr);

            $ngrd = $this->m_gare_depart->getno($gid);

            $dats = explode("-", $date1);
            $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
            $dats1 = explode("-", $date2);
            $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];

            if($serole->userole === '4')
            {
              $trirecet = $this->m_recette->trirecettecr($this->entreprise->ekey, $gid, $date1, $date2, $atr, $gen, $comp, $nm);
              //var_dump($gid, $date1, $date2, $atr, $typ, $comp, $gen, $nm, $trirecet);
              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('RESERVATIONS');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('L', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', 'B', 10);
                          
              $titre = '<h1 align="center">ETATS DES RECETTES COURRIER '.$ncomp->nom_compagnie.' '.$ngrd->garenom.' DU '. $days .' AU '.$days1.'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead> 
                      <tr> 
                        <th width="10%" align="center"><strong>DATE</strong></th>
                        <th width="10%" align="center"><strong>TYPE</strong></th>
                        <th width="10%" align="center"><strong>GENRE</strong></th> 
                        <th width="15%" align="center"><strong>NOM</strong></th>

                        <th width="20%" align="center"><strong>COMMENTAIRE</strong></th>
                        <th width="15%" align="center"><strong>TOTAL</strong></th>
                      </tr>
                  </thead>
                  <tbody>';
                  $recetglobal = 0;
                  $pdf->SetFont('courier', '', 9);
              foreach ($trirecet as $tris => $rect) {
                  $them .= '<tr>
                      <td width="10%" align="center"><strong>' . $rect->date_recet . '</strong></td>
                      <td width="10%" align="center"><strong>' . $rect->type_recet . '</strong></td>
                      <td width="10%" align="center"><strong>' . $rect->type_personnel . '</strong></td>
                      <td width="15%" align="center"><strong>' . $rect->nom . '</strong></td>
                      <td width="20%" align="center"><strong>' . $rect->commentaire_recet .'</strong></td>

                      <td width="15%" align="center"><strong>' . $rect->montant_recet . '</strong></td>
                      </tr>';
                      $recetglobal +=$rect->montant_recet;
              }
              $them .= '<tr>
                      <td width="65%" align="center"><strong>TOTAL</strong></td>
                      <td width="15%" align="center"><strong> '.number_format($recetglobal, 0, '', ' ').'</strong></td>
                                  
                 </tr>';
              $them .= ' </tbody></table>';
              
              $them.= '<h2>RECETTE TOTAL :'. number_format($recetglobal, 0, '', ' ').' </h2>';
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_015.pdf' . '', 'I');
            }

            if($serole->userole === '18')
            {
              $trirecet = $this->m_recette->adtrirecettecr($this->entreprise->ekey, $gid, $date1, $date2, $atr, $gen, $comp, $nm);
              //var_dump($gid, $date1, $date2, $atr, $typ, $comp, $gen, $nm, $trirecet);
              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('RESERVATIONS');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('L', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', 'B', 10);
                          
              $titre = '<h1 align="center">ETATS DES RECETTES COURRIER '.$ncomp->nom_compagnie.' '.$ngrd->garenom.' DU '. $days .' AU '.$days1.'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead> 
                      <tr> 
                        <th width="10%" align="center"><strong>DATE</strong></th>
                        <th width="10%" align="center"><strong>TYPE</strong></th>
                        <th width="10%" align="center"><strong>GENRE</strong></th> 
                        <th width="15%" align="center"><strong>NOM</strong></th>

                        <th width="20%" align="center"><strong>COMMENTAIRE</strong></th>
                        <th width="15%" align="center"><strong>TOTAL</strong></th>
                      </tr>
                  </thead>
                  <tbody>';
                  $recetglobal = 0;
                  $pdf->SetFont('courier', '', 9);
              foreach ($trirecet as $tris => $rect) {
                  $them .= '<tr>
                      <td width="10%" align="center"><strong>' . $rect->date_recet . '</strong></td>
                      <td width="10%" align="center"><strong>' . $rect->type_recet . '</strong></td>
                      <td width="10%" align="center"><strong>' . $rect->type_personnel . '</strong></td>
                      <td width="15%" align="center"><strong>' . $rect->nom . '</strong></td>
                      <td width="20%" align="center"><strong>' . $rect->commentaire_recet .'</strong></td>

                      <td width="15%" align="center"><strong>' . $rect->montant_recet . '</strong></td>
                      </tr>';
                      $recetglobal +=$rect->montant_recet;
              }
              $them .= '<tr>
                      <td width="65%" align="center"><strong>TOTAL</strong></td>
                      <td width="15%" align="center"><strong> '.number_format($recetglobal, 0, '', ' ').'</strong></td>
                                  
                 </tr>';
              $them .= ' </tbody></table>';
              
              $them.= '<h2>RECETTE TOTAL :'. number_format($recetglobal, 0, '', ' ').' </h2>';
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_015.pdf' . '', 'I');
            }

            elseif($serole->userole === '1' OR $serole->userole === '2')
            {
              $trirecet = $this->m_recette->trirecetteadmincr($this->entreprise->ekey, $gid, $date1, $date2, $gen, $comp, $nm);
              //var_dump($this->entreprise->ekey, $gid, $date1, $date2, $typ, $gen, $comp, $nm, $trirecet);
              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('RESERVATIONS');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('L', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', 'B', 10);
                          
              $titre = '<h1 align="center">ETATS DES RECETTES COURRIER '.$ngrd->garenom.'DU '. $days .' AU '.$days1.'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead> 
                      <tr> 
                        <th width="10%" align="center"><strong>DATE</strong></th>
                        <th width="10%" align="center"><strong>TYPE</strong></th>
                        <th width="10%" align="center"><strong>GENRE</strong></th> 
                        <th width="15%" align="center"><strong>NOM</strong></th>

                        <th width="20%" align="center"><strong>COMMENTAIRE</strong></th>
                        <th width="15%" align="center"><strong>TOTAL</strong></th>
                      </tr>
                  </thead>
                  <tbody>';
                  $recetglobal = 0;
                  $pdf->SetFont('courier', '', 9);
              foreach ($trirecet as $tris => $rect) {
                  $them .= '<tr>
                      <td width="10%" align="center"><strong>' . $rect->date_recet . '</strong></td>
                      <td width="10%" align="center"><strong>' . $rect->type_recet . '</strong></td>
                      <td width="10%" align="center"><strong>' . $rect->type_personnel . '</strong></td>
                      <td width="15%" align="center"><strong>' . $rect->nom . '</strong></td>
                      <td width="20%" align="center"><strong>' . $rect->commentaire_recet .'</strong></td>

                      <td width="15%" align="center"><strong>' . $rect->montant_recet . '</strong></td>
                      </tr>';
                      $recetglobal +=$rect->montant_recet;
              }
              $them .= '<tr>
                      <td width="65%" align="center"><strong>TOTAL</strong></td>
                      <td width="15%" align="center"><strong> '.number_format($recetglobal, 0, '', ' ').'</strong></td>
                                  
                 </tr>';
              $them .= ' </tbody></table>';
              
              $them.= '<h2>RECETTE TOTAL :'. number_format($recetglobal, 0, '', ' ').' </h2>';
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_015.pdf' . '', 'I');
            }
            else
            {
              $trirecets = $this->m_recette->trirecette_adjointcr($this->entreprise->ekey, $gid, $atr, $date1, $date2,  $gen, $comp, $nm);
              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('RESERVATIONS');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('L', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', 'B', 10);
                          
              $titre = '<h1 align="center">ETATS DES RECETTES COURRIER '.$ngrd->garenom.' DU '. $days .' AU '.$days1.'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead> 
                      <tr> 
                        <th width="10%" align="center"><strong>DATE</strong></th>
                        <th width="10%" align="center"><strong>TYPE</strong></th>
                        <th width="10%" align="center"><strong>GENRE</strong></th> 
                        <th width="15%" align="center"><strong>NOM</strong></th>

                        <th width="20%" align="center"><strong>COMMENTAIRE</strong></th>
                        <th width="15%" align="center"><strong>TOTAL</strong></th>
                      </tr>
                  </thead>
                  <tbody>';
                  $recetglobal = 0;
                  $pdf->SetFont('courier', '', 9);
              foreach ($trirecets as $tris => $rect) {
                  $them .= '<tr>
                      <td width="10%" align="center"><strong>' . $rect->date_recet . '</strong></td>
                      <td width="10%" align="center"><strong>' . $rect->type_recet . '</strong></td>
                      <td width="10%" align="center"><strong>' . $rect->type_personnel . '</strong></td>
                      <td width="15%" align="center"><strong>' . $rect->nom . '</strong></td>
                      <td width="20%" align="center"><strong>' . $rect->commentaire_recet .'</strong></td>

                      <td width="15%" align="center"><strong>' . $rect->montant_recet . '</strong></td>
                      </tr>';
                       $recetglobal +=$rect->montant_recet;
              }
              
              $them .= '<tr>
                      <td width="65%" align="center"><strong>TOTAL</strong></td>
                      <td width="15%" align="center"><strong> '.number_format($recetglobal, 0, '', ' ').'</strong></td>
                                  
                 </tr>';
              $them .= ' </tbody></table>';
              
              $them.= '<h2>RECETTE TOTAL :'. number_format($recetglobal , 0, '', ' ').' </h2>';
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_015.pdf' . '', 'I');
              //============================================================+
              // END OF FILE
              //============================================================+
            }
            
        }

        //recette exo bagages

        public function exercicesbag($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
              $dt1 = $this->input->post('datedebutbag');
              $dt2 = $this->input->post('datefinbag');
              $lign = $this->input->post('axelignebag');
              $comp = $this->input->post('_compagbag');
              $gid = $this->input->post('departgarbag');
              $ncomp = $this->m_compagnies->getn($comp);

                $dats = explode("-", $dt1);
                $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                $dats1 = explode("-", $dt2);
                  $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
            if($this->session->agent->userole === '1' OR $this->session->agent->userole === '2')
            {


              $reportick = $this->m_bagage->reportbgcpt($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);

                $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                // set document information
                $pdf->SetCreator(PDF_CREATOR);
                $pdf->SetAuthor('NET SOLUTIONS');
                $pdf->SetTitle('LISTE-');
                $pdf->SetSubject('CBT_RAKIETA');
                $pdf->SetKeywords('--');
                
                $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
                // remove default header/footer
                $pdf->setPrintHeader(true);
                $pdf->setPrintFooter(false);
                
                // set default monospaced font
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
                $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                // set margins
                $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                
                
                // set auto page breaks
                $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
                
                // set image scale factor
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
                
                // set font
                
                
                // add a page
                $pdf->AddPage('L', 'A4', 0);
                
                // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                // GROUPE DE GAUCHE
                $pdf->SetFont('courier', '', 9);
                            
                $titre = '<h1 align="center">RECAP EX MENSUEL BABAGE '.$ncomp->nom_compagnie.' DU '. $days .' AU '.$days1.'</h1>';
                $them = '<table border="1" cellpadding="0">
                    <thead> 
                        <tr> 
                          <th width="20%" align="center"><strong>LIGNE</strong></th>
                          <th width="15%" align="center"><strong>NBR_BAGAGES</strong></th> 
                          <th width="20%" align="center"><strong>PRIX_UNITAIRE</strong></th>
                          <th width="20%" align="center"><strong>PRIX_TOTAL</strong></th>
                        </tr>
                    </thead>
                    <tbody>';
                    $etatglobale = 0;
                    $nb = 0;
                    $bg = 0;
                foreach ($reportick as $departick => $lement) {
                    $them .= '<tr>
                        <td width="20%" align="left"><strong>' . $lement->nom_ligne . '</strong></td>
                        <td width="15%" align="center"><strong>' . $lement->codid_bagage . '</strong></td>
                        <td width="20%" align="center"><strong>' . number_format($lement->prix_bagage, 0, '', ' ') . '</strong></td>
                        <td width="20%" align="right"><strong>' . number_format(($lement->codid_bagage * $lement->prix_bagage), 0, '', ' ') . '</strong></td>
                        </tr>';
                         $etatglobale += $lement->codid_bagage * $lement->prix_bagage;
                         $nb += $lement->codid_bagage;
                         $bg += $lement->prix_bagage;
                }

                    $them .= '<tr>
                          <td width="20%" align="left"><strong>TOTAL</strong></td>
                          <td width="15%" align="center"><strong> '.$nb.'</strong></td>
                          <td width="20%" align="center"><strong></strong></td>
                          <td width="20%" align="right"><strong> '.number_format($etatglobale, 0, '', ' ').'</strong></td>
                          
                     </tr>';
                    
                $them .= ' </tbody></table>';
                $them.= '<h2>SOMME:'. number_format($etatglobale, 0, '', ' ') .' </h2>';
                 
                $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
                $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
                ob_end_clean();
                //Close and output PDF document
                $pdf->Output('example_013.pdf' . '', 'I');
                //============================================================+
                // END OF FILE
                //============================================================+
            }else
            {

              
              $reportick = $this->m_bagage->reportbgcpt($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
                  
              
              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('CBT_RAKIETA');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('L', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                          
              $titre = '<h1 align="center">RECAP EX MENSUEL BABAGE '.$ncomp->nom_compagnie.' DU '. $days .' AU '.$days1.'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead> 
                      <tr> 
                        <th width="20%" align="center"><strong>LIGNE</strong></th>
                        <th width="15%" align="center"><strong>NBR_BAGAGES</strong></th> 
                        <th width="20%" align="center"><strong>PRIX_UNITAIRE</strong></th>
                        <th width="20%" align="center"><strong>PRIX_TOTAL</strong></th>
                      </tr>
                  </thead>
                  <tbody>';
                  $etatglobale = 0;
                  $nb = 0;
                  $nbrt = 0;
                  $bg = 0;
              foreach ($reportick as $departick => $lement) {
                  $them .= '<tr>
                      <td width="20%" align="left"><strong>' . $lement->nom_ligne . '</strong></td>
                      <td width="15%" align="center"><strong>' . $lement->codid_bagage . '</strong></td>
                      <td width="20%" align="center"><strong>' . number_format($lement->prix_bagage, 0, '', ' ') . '</strong></td>
                      <td width="20%" align="right"><strong>' . number_format(($lement->codid_bagage * $lement->prix_bagage), 0, '', ' ') . '</strong></td>
                      </tr>';
                       $etatglobale += $lement->codid_bagage * $lement->prix_bagage;
                       $nb += $lement->codid_bagage;
                       $bg += $lement->prix_bagage;
              }

                    $them .= '<tr>
                        <td width="20%" align="left"><strong>TOTAL</strong></td>
                        <td width="15%" align="center"><strong> '.$nb.'</strong></td>
                       <td width="20%" align="center"><strong></strong></td>
                        <td width="20%" align="right"><strong> '.number_format($etatglobale, 0, '', ' ').'</strong></td>
                        
                   </tr>';
                  
              $them .= ' </tbody></table>';
              $them.= '<h2>SOMME:'. number_format($etatglobale, 0, '', ' ') .' </h2>';
               
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_013.pdf' . '', 'I');
              //============================================================+
              // END OF FILE
              //============================================================+
            }
        }

        public function exercicesbagesc($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
              $dt1 = $this->input->post('datedebutbagesc');
              $dt2 = $this->input->post('datefinbagesc');
              $lign = $this->input->post('axelignebagesc');
              $comp = $this->input->post('_compagbagesc');
              $gid = $this->input->post('departgarbagesc');
              $ncomp = $this->m_compagnies->getn($comp);

                $dats = explode("-", $dt1);
                $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                $dats1 = explode("-", $dt2);
                $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];

              $reportick = $this->m_bagageesc->reportbgcpt($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);

                $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                // set document information
                $pdf->SetCreator(PDF_CREATOR);
                $pdf->SetAuthor('NET SOLUTIONS');
                $pdf->SetTitle('LISTE-');
                $pdf->SetSubject('CBT_RAKIETA');
                $pdf->SetKeywords('--');
                
                $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
                // remove default header/footer
                $pdf->setPrintHeader(true);
                $pdf->setPrintFooter(false);
                
                // set default monospaced font
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
                $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                // set margins
                $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                
                
                // set auto page breaks
                $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
                
                // set image scale factor
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
                
                // set font
                
                
                // add a page
                $pdf->AddPage('L', 'A4', 0);
                
                // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                // GROUPE DE GAUCHE
                $pdf->SetFont('courier', '', 9);
                            
                $titre = '<h1 align="center">RECAP EX MENSUEL BABAGEESCAL '.$ncomp->nom_compagnie.' DU '. $days .' AU '.$days1.'</h1>';
                $them = '<table border="1" cellpadding="0">
                    <thead> 
                        <tr> 
                          <th width="20%" align="center"><strong>LIGNE</strong></th>
                          <th width="15%" align="center"><strong>NBR_BAGAGES</strong></th> 
                          <th width="20%" align="center"><strong>PRIX_UNITAIRE</strong></th>
                          <th width="20%" align="center"><strong>PRIX_TOTAL</strong></th>
                        </tr>
                    </thead>
                    <tbody>';
                    $etatglobale = 0;
                    $nb = 0;
                    $bg = 0;
                foreach ($reportick as $departick => $lement) {
                    $them .= '<tr>
                        <td width="20%" align="left"><strong>' . $lement->nom_ligne . '</strong></td>
                        <td width="15%" align="center"><strong>' . $lement->codid_bagageesc . '</strong></td>
                        <td width="20%" align="center"><strong>' . number_format($lement->prix_bagageesc, 0, '', ' ') . '</strong></td>
                        <td width="20%" align="right"><strong>' . number_format(($lement->codid_bagageesc * $lement->prix_bagageesc), 0, '', ' ') . '</strong></td>
                        </tr>';
                         $etatglobale += $lement->codid_bagageesc * $lement->prix_bagageesc;
                         $nb += $lement->codid_bagageesc;
                         $bg += $lement->prix_bagageesc;
                }

                    $them .= '<tr>
                          <td width="20%" align="left"><strong>TOTAL</strong></td>
                          <td width="15%" align="center"><strong> '.$nb.'</strong></td>
                          <td width="20%" align="center"><strong></strong></td>
                          <td width="20%" align="right"><strong> '.number_format($etatglobale, 0, '', ' ').'</strong></td>
                          
                     </tr>';
                    
                $them .= ' </tbody></table>';
                $them.= '<h2>SOMME:'. number_format($etatglobale, 0, '', ' ') .' </h2>';
                 
                $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
                $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
                ob_end_clean();
                //Close and output PDF document
                $pdf->Output('example_013.pdf' . '', 'I');
                //============================================================+
                // END OF FILE
                //============================================================+
        }
        
        public function exercicesbagop($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
              $dt1 = $this->input->post('datedebutbagop');
              $dt2 = $this->input->post('datefinbagop');
              $lign = $this->input->post('axelignebagop');
              $comp = $this->input->post('_compagbagop');
              $gid = $this->input->post('departgarbagop');
              $cais = $this->input->post('vendeuseidop');
              $ncomp = $this->m_compagnies->getn($comp);

              $uc = $this->m_utilisateur->u($cais);
              if($uc == NULL){
                $us = '';
              }else{
                $us = $uc->first_name.' '.$uc->last_name;
              }

                $dats = explode("-", $dt1);
                $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                $dats1 = explode("-", $dt2);
                $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
            if($this->session->agent->userole === '1' OR $this->session->agent->userole === '2')
            {


              $reportick = $this->m_bagage->reportbgcptop($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $cais, $lign);

                $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                // set document information
                $pdf->SetCreator(PDF_CREATOR);
                $pdf->SetAuthor('NET SOLUTIONS');
                $pdf->SetTitle('LISTE-');
                $pdf->SetSubject('CBT_RAKIETA');
                $pdf->SetKeywords('--');
                
                $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
                // remove default header/footer
                $pdf->setPrintHeader(true);
                $pdf->setPrintFooter(false);
                
                // set default monospaced font
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
                $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                // set margins
                $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                
                
                // set auto page breaks
                $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
                
                // set image scale factor
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
                
                // set font
                
                
                // add a page
                $pdf->AddPage('L', 'A4', 0);
                
                // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                // GROUPE DE GAUCHE
                $pdf->SetFont('courier', '', 9);
                            
                $titre = '<h1 align="center">EXERCICE MENSUEL BABAGE '.$us.' '.$ncomp->nom_compagnie.' DU '. $days .' AU '.$days1.'</h1>';
                $them = '<table border="1" cellpadding="0">
                    <thead> 
                        <tr> 
                          <th width="20%" align="center"><strong>LIGNE</strong></th>
                          <th width="15%" align="center"><strong>NBR_BAGAGES</strong></th> 
                          <th width="20%" align="center"><strong>PRIX_UNITAIRE</strong></th>
                          <th width="20%" align="center"><strong>PRIX_TOTAL</strong></th>
                        </tr>
                    </thead>
                    <tbody>';
                    $etatglobale = 0;
                    $nb = 0;
                    $bg = 0;
                foreach ($reportick as $departick => $lement) {
                    $them .= '<tr>
                        <td width="20%" align="left"><strong>' . $lement->nom_ligne . '</strong></td>
                        <td width="15%" align="center"><strong>' . $lement->codid_bagage . '</strong></td>
                        <td width="20%" align="center"><strong>' . number_format($lement->prix_bagage, 0, '', ' ') . '</strong></td>
                        <td width="20%" align="right"><strong>' . number_format(($lement->codid_bagage * $lement->prix_bagage), 0, '', ' ') . '</strong></td>
                        </tr>';
                         $etatglobale += $lement->codid_bagage * $lement->prix_bagage;
                         $nb += $lement->codid_bagage;
                         $bg += $lement->prix_bagage;
                }

                    $them .= '<tr>
                          <td width="20%" align="left"><strong>TOTAL</strong></td>
                          <td width="15%" align="center"><strong> '.$nb.'</strong></td>
                          <td width="20%" align="center"><strong></strong></td>
                          <td width="20%" align="right"><strong> '.number_format($etatglobale, 0, '', ' ').'</strong></td>
                          
                     </tr>';
                    
                $them .= ' </tbody></table>';
                $them.= '<h2>SOMME:'. number_format($etatglobale, 0, '', ' ') .' </h2>';
                 
                $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
                $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
                ob_end_clean();
                //Close and output PDF document
                $pdf->Output('example_013.pdf' . '', 'I');
                //============================================================+
                // END OF FILE
                //============================================================+
            }else
            {

              $reportick = $this->m_bagage->reportbgcptop($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $cais, $lign);
                  
              
              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('CBT_RAKIETA');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('L', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                          
              $titre = '<h1 align="center">EXERCICE MENSUEL BABAGE '.$us.' '.$ncomp->nom_compagnie.' DU '. $days .' AU '.$days1.'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead> 
                      <tr> 
                        <th width="20%" align="center"><strong>LIGNE</strong></th>
                        <th width="15%" align="center"><strong>NBR_BAGAGES</strong></th> 
                        <th width="20%" align="center"><strong>PRIX_UNITAIRE</strong></th>
                        <th width="20%" align="center"><strong>PRIX_TOTAL</strong></th>
                      </tr>
                  </thead>
                  <tbody>';
                  $etatglobale = 0;
                  $nb = 0;
                  $nbrt = 0;
                  $bg = 0;
              foreach ($reportick as $departick => $lement) {
                  $them .= '<tr>
                      <td width="20%" align="left"><strong>' . $lement->nom_ligne . '</strong></td>
                      <td width="15%" align="center"><strong>' . $lement->codid_bagage . '</strong></td>
                      <td width="20%" align="center"><strong>' . number_format($lement->prix_bagage, 0, '', ' ') . '</strong></td>
                      <td width="20%" align="right"><strong>' . number_format(($lement->codid_bagage * $lement->prix_bagage), 0, '', ' ') . '</strong></td>
                      </tr>';
                       $etatglobale += $lement->codid_bagage * $lement->prix_bagage;
                       $nb += $lement->codid_bagage;
                       $bg += $lement->prix_bagage;
              }

                    $them .= '<tr>
                        <td width="20%" align="left"><strong>TOTAL</strong></td>
                        <td width="15%" align="center"><strong> '.$nb.'</strong></td>
                       <td width="20%" align="center"><strong></strong></td>
                        <td width="20%" align="right"><strong> '.number_format($etatglobale, 0, '', ' ').'</strong></td>
                        
                   </tr>';
                  
              $them .= ' </tbody></table>';
              $them.= '<h2>SOMME:'. number_format($etatglobale, 0, '', ' ') .' </h2>';
               
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_013.pdf' . '', 'I');
              //============================================================+
              // END OF FILE
              //============================================================+
            }
        }

        public function exercicesbagopesc($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
              $dt1 = $this->input->post('datedebutbagopesc');
              $dt2 = $this->input->post('datefinbagopesc');
              $lign = $this->input->post('axelignebagopesc');
              $comp = $this->input->post('_compagbagopesc');
              $gid = $this->input->post('departgarbagopesc');
              $cais = $this->input->post('vendeuseidopesc');
              $ncomp = $this->m_compagnies->getn($comp);

              $uc = $this->m_utilisateur->u($cais);
              if($uc == NULL){
                $us = '';
              }else{
                $us = $uc->first_name.' '.$uc->last_name;
              }

                $dats = explode("-", $dt1);
                $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                $dats1 = explode("-", $dt2);
                $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
            if($this->session->agent->userole === '1' OR $this->session->agent->userole === '2')
            {


              $reportickesc = $this->m_bagageesc->reportbgcptop($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $cais, $lign);

                $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                // set document information
                $pdf->SetCreator(PDF_CREATOR);
                $pdf->SetAuthor('NET SOLUTIONS');
                $pdf->SetTitle('LISTE-');
                $pdf->SetSubject('CBT_RAKIETA');
                $pdf->SetKeywords('--');
                
                $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
                // remove default header/footer
                $pdf->setPrintHeader(true);
                $pdf->setPrintFooter(false);
                
                // set default monospaced font
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
                $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                // set margins
                $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                
                
                // set auto page breaks
                $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
                
                // set image scale factor
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
                
                // set font
                
                
                // add a page
                $pdf->AddPage('L', 'A4', 0);
                
                // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                // GROUPE DE GAUCHE
                $pdf->SetFont('courier', '', 9);
                            
                $titre = '<h1 align="center">EXERCICE MENSUEL BABAGEESCAL '.$us.' '.$ncomp->nom_compagnie.' DU '. $days .' AU '.$days1.'</h1>';
                $them = '<table border="1" cellpadding="0">
                    <thead> 
                        <tr> 
                          <th width="20%" align="center"><strong>LIGNE</strong></th>
                          <th width="15%" align="center"><strong>NBR_BAGAGES</strong></th> 
                          <th width="20%" align="center"><strong>PRIX_UNITAIRE</strong></th>
                          <th width="20%" align="center"><strong>PRIX_TOTAL</strong></th>
                        </tr>
                    </thead>
                    <tbody>';
                    $etatglobaleesc = 0;
                    $nbesc = 0;
                    $bgesc = 0;
                foreach ($reportickesc as $departick => $lements) {
                    $them .= '<tr>
                        <td width="20%" align="left"><strong>' . $lements->nom_ligne . '</strong></td>
                        <td width="15%" align="center"><strong>' . $lements->codid_bagageesc . '</strong></td>
                        <td width="20%" align="center"><strong>' . number_format($lements->prix_bagageesc, 0, '', ' ') . '</strong></td>
                        <td width="20%" align="right"><strong>' . number_format(($lements->codid_bagageesc * $lements->prix_bagageesc), 0, '', ' ') . '</strong></td>
                        </tr>';
                         $etatglobaleesc += $lements->codid_bagageesc * $lements->prix_bagageesc;
                         $nbesc += $lements->codid_bagageesc;
                         $bgesc += $lements->prix_bagageesc;
                }

                    $them .= '<tr>
                          <td width="20%" align="left"><strong>TOTAL</strong></td>
                          <td width="15%" align="center"><strong> '.$nbesc.'</strong></td>
                          <td width="20%" align="center"><strong></strong></td>
                          <td width="20%" align="right"><strong> '.number_format($etatglobaleesc, 0, '', ' ').'</strong></td>
                          
                     </tr>';
                    
                $them .= ' </tbody></table>';
                $them.= '<h2>SOMME:'. number_format($etatglobaleesc, 0, '', ' ') .' </h2>';
                 
                $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
                $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
                ob_end_clean();
                //Close and output PDF document
                $pdf->Output('example_013.pdf' . '', 'I');
                //============================================================+
                // END OF FILE
                //============================================================+
            }else
            {

              $reportickesc = $this->m_bagageesc->reportbgcptop($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $cais, $lign);
                  
              
              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('CBT_RAKIETA');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('L', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                          
              $titre = '<h1 align="center">EXERCICE MENSUEL BABAGEESCAL '.$us.' '.$ncomp->nom_compagnie.' DU '. $days .' AU '.$days1.'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead> 
                      <tr> 
                        <th width="20%" align="center"><strong>LIGNE</strong></th>
                        <th width="15%" align="center"><strong>NBR_BAGAGES</strong></th> 
                        <th width="20%" align="center"><strong>PRIX_UNITAIRE</strong></th>
                        <th width="20%" align="center"><strong>PRIX_TOTAL</strong></th>
                      </tr>
                  </thead>
                  <tbody>';
                  $etatglobaleesc = 0;
                  $nbesc = 0;
                  $nbrtesc = 0;
                  $bgesc = 0;
              foreach ($reportickesc as $departick => $lements) {
                  $them .= '<tr>
                      <td width="20%" align="left"><strong>' . $lements->nom_ligne . '</strong></td>
                      <td width="15%" align="center"><strong>' . $lements->codid_bagageesc. '</strong></td>
                      <td width="20%" align="center"><strong>' . number_format($lements->prix_bagageesc, 0, '', ' ') . '</strong></td>
                      <td width="20%" align="right"><strong>' . number_format(($lements->codid_bagageesc * $lements->prix_bagageesc), 0, '', ' ') . '</strong></td>
                      </tr>';
                       $etatglobaleesc += $lements->codid_bagageesc * $lements->prix_bagageesc;
                       $nbesc += $lements->codid_bagageesc;
                       $bgesc += $lements->prix_bagageesc;
              }

                    $them .= '<tr>
                        <td width="20%" align="left"><strong>TOTAL</strong></td>
                        <td width="15%" align="center"><strong> '.$nbesc.'</strong></td>
                       <td width="20%" align="center"><strong></strong></td>
                        <td width="20%" align="right"><strong> '.number_format($etatglobaleesc, 0, '', ' ').'</strong></td>
                        
                   </tr>';
                  
              $them .= ' </tbody></table>';
              $them.= '<h2>SOMME:'. number_format($etatglobaleesc, 0, '', ' ') .' </h2>';
               
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_013.pdf' . '', 'I');
              //============================================================+
              // END OF FILE
              //============================================================+
            }
        }
        //tirage des depenses
        public function depense($ckey)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $date1 = $this->input->post('datedebut');
            $date2 = $this->input->post('datefin');
            $typ = $this->input->post('type');
            $gen = $this->input->post('genre');
            $nm = $this->input->post('nom');
            $comp = $this->input->post('_compag');
            $gid = $this->input->post('gareconnect');
            $atr = roleattribut_guard_post_hint($this->entreprise->ekey);
            $ncomp = $this->m_compagnies->getn($comp);

            $ngrd = $this->m_gare_depart->getno($gid);

            $dats = explode("-", $date1);
              $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
              $dats1 = explode("-", $date2);
              $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
            if(recette_role_is_validateur_principal($this->session->agent->userole))
            {
              $tridepens = $this->m_depense->tridepense($this->entreprise->ekey, $gid, $atr, $comp, $date1, $date2, $gen, $nm);
			
              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('RESERVATIONS');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('L', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                          
              $titre = '<h1 align="center">ETATS DES DEPENSES DE '.$ncomp->nom_compagnie.' '.$ngrd->garenom.' DU '. $days.' AU '.$days1.'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead> 
                      <tr> 
                        <th width="10%" align="center"><strong>DATE</strong></th>
                        <th width="10%" align="center"><strong>TYPE</strong></th>
                        <th width="10%" align="center"><strong>GENRE</strong></th> 
                        <th width="15%" align="center"><strong>NOM</strong></th>

                        <th width="20%" align="center"><strong>COMMENTAIRE</strong></th>
                        <th width="20%" align="center"><strong>MOTIF</strong></th>
                        <th width="15%" align="center"><strong>TOTAL</strong></th>
                      </tr>
                  </thead>
                  <tbody>';
                  $depenseglobal = 0;
              foreach ($tridepens as $trid => $depen) {
                  $them .= '<tr>
                      <td width="10%" align="center"><strong>' . $depen->date_depens . '</strong></td>
                      <td width="10%" align="center"><strong>' . $depen->type_depense . '</strong></td>
                      <td width="10%" align="center"><strong>' . $depen->genre_depens . '</strong></td>
                      <td width="15%" align="center"><strong>' . $depen->nom_perso . '</strong></td>
                      <td width="20%" align="center"><strong>' . $depen->commentaire .'</strong></td>
                      <td width="20%" align="center"><strong>' . $depen->motif .'</strong></td>

                      <td width="15%" align="center"><strong>' . $depen->montant_depens . '</strong></td>
                      </tr>';
                       $depenglobal +=$depen->montant_depens;
              }
              $them .= '<tr>
                      <td width="85%" align="center"><strong>TOTAL</strong></td>
                      <td width="15%" align="center"><strong> '.number_format($depenglobal, 0, '', ' ').'</strong></td>
                                  
                 </tr>';
              $them .= ' </tbody></table>';
              
              $them.= '<h2>DEPENSE TOTAL :'. number_format($depenglobal, 0, '', ' ').' </h2>';
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_016.pdf' . '', 'I');
			
            }

            if(recette_role_is_validateur_adjoint($this->session->agent->userole))
            {
              $tridepens = $this->m_depense->adtridepense($this->entreprise->ekey, $gid, $atr, $comp, $date1, $date2, $gen, $nm);
      
              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('RESERVATIONS');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('L', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                          
              $titre = '<h1 align="center">ETATS DES DEPENSES DE '.$ncomp->nom_compagnie.' '.$ngrd->garenom.' DU '. $days.' AU '.$days1.'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead> 
                      <tr> 
                        <th width="10%" align="center"><strong>DATE</strong></th>
                        <th width="10%" align="center"><strong>TYPE</strong></th>
                        <th width="10%" align="center"><strong>GENRE</strong></th> 
                        <th width="15%" align="center"><strong>NOM</strong></th>

                        <th width="20%" align="center"><strong>COMMENTAIRE</strong></th>
                        <th width="20%" align="center"><strong>MOTIF</strong></th>
                        <th width="15%" align="center"><strong>TOTAL</strong></th>
                      </tr>
                  </thead>
                  <tbody>';
                  $depenseglobal = 0;
              foreach ($tridepens as $trid => $depen) {
                  $them .= '<tr>
                      <td width="10%" align="center"><strong>' . $depen->date_depens . '</strong></td>
                      <td width="10%" align="center"><strong>' . $depen->type_depense . '</strong></td>
                      <td width="10%" align="center"><strong>' . $depen->genre_depens . '</strong></td>
                      <td width="15%" align="center"><strong>' . $depen->nom_perso . '</strong></td>
                      <td width="20%" align="center"><strong>' . $depen->commentaire .'</strong></td>
                      <td width="20%" align="center"><strong>' . $depen->motif .'</strong></td>

                      <td width="15%" align="center"><strong>' . $depen->montant_depens . '</strong></td>
                      </tr>';
                       $depenglobal +=$depen->montant_depens;
              }
              $them .= '<tr>
                      <td width="85%" align="center"><strong>TOTAL</strong></td>
                      <td width="15%" align="center"><strong> '.number_format($depenglobal, 0, '', ' ').'</strong></td>
                                  
                 </tr>';
              $them .= ' </tbody></table>';
              
              $them.= '<h2>DEPENSE TOTAL :'. number_format($depenglobal, 0, '', ' ').' </h2>';
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_016.pdf' . '', 'I');
      
            }

            if($this->session->agent->userole === '1' OR $this->session->agent->userole === '2')
            {
              $tridepens = $this->m_depense->tridepenseadmin($this->entreprise->ekey, $gid, $comp, $date1, $date2, $typ, $gen, $nm);
              
              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('RESERVATIONS');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('L', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                          
              $titre = '<h1 align="center">ETATS DES DEPENSES DE '.$ncomp->nom_compagnie.' '.$ngrd->garenom.' DU '. $days.' AU '.$days1.'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead> 
                      <tr> 
                        <th width="10%" align="center"><strong>DATE</strong></th>
                        <th width="10%" align="center"><strong>TYPE</strong></th>
                        <th width="10%" align="center"><strong>GENRE</strong></th> 
                        <th width="15%" align="center"><strong>NOM</strong></th>

                        <th width="20%" align="center"><strong>COMMENTAIRE</strong></th>
                        <th width="20%" align="center"><strong>MOTIF</strong></th>
                        <th width="15%" align="center"><strong>TOTAL</strong></th>
                      </tr>
                  </thead>
                  <tbody>';
                  $depenseglobal = 0;
              foreach ($tridepens as $trid => $depen) {
                  $them .= '<tr>
                      <td width="10%" align="center"><strong>' . $depen->date_depens . '</strong></td>
                      <td width="10%" align="center"><strong>' . $depen->type_depense . '</strong></td>
                      <td width="10%" align="center"><strong>' . $depen->genre_depens . '</strong></td>
                      <td width="15%" align="center"><strong>' . $depen->nom_perso . '</strong></td>
                      <td width="20%" align="center"><strong>' . $depen->commentaire .'</strong></td>
                      <td width="20%" align="center"><strong>' . $depen->motif .'</strong></td>

                      <td width="15%" align="center"><strong>' . $depen->montant_depens . '</strong></td>
                      </tr>';
                       $depenglobal +=$depen->montant_depens;
              }
              $them .= '<tr>
                      <td width="85%" align="center"><strong>TOTAL</strong></td>
                      <td width="15%" align="center"><strong> '.number_format($depenglobal, 0, '', ' ').'</strong></td>
                                  
                 </tr>';
              $them .= ' </tbody></table>';
              
              $them.= '<h2>DEPENSE TOTAL :'. number_format($depenglobal, 0, '', ' ').' </h2>';
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_016.pdf' . '', 'I');
      
            }
            elseif (recette_role_is_saisie($this->session->agent->userole))
            {
              $tridepen = $this->m_depense->tridepense_adjoint($this->entreprise->ekey, $gid, $atr, $date1, $date2, $comp, $typ, $gen, $nm);
              
			
              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('RESERVATIONS');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('L', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                          
              $titre = '<h1 align="center">ETATS DES DEPENSES DE '.$ncomp->nom_compagnie.' '.$ngrd->garenom.' DU '. $days.' AU '.$days1.'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead> 
                      <tr> 
                        <th width="10%" align="center"><strong>DATE</strong></th>
                        <th width="10%" align="center"><strong>TYPE</strong></th>
                        <th width="10%" align="center"><strong>GENRE</strong></th> 
                        <th width="15%" align="center"><strong>NOM</strong></th>

                        <th width="20%" align="center"><strong>COMMENTAIRE</strong></th>
                        <th width="20%" align="center"><strong>MOTIF</strong></th>
                        <th width="15%" align="center"><strong>TOTAL</strong></th>
                      </tr>
                  </thead>
                  <tbody>';
                  $depenseglobal = 0;
              foreach ($tridepen as $trid => $depen) {
                  $them .= '<tr>
                      <td width="10%" align="center"><strong>' . $depen->date_depens . '</strong></td>
                      <td width="10%" align="center"><strong>' . $depen->type_depense . '</strong></td>
                      <td width="10%" align="center"><strong>' . $depen->genre_depens . '</strong></td>
                      <td width="15%" align="center"><strong>' . $depen->nom_perso . '</strong></td>
                      <td width="20%" align="center"><strong>' . $depen->commentaire .'</strong></td>
                      <td width="20%" align="center"><strong>' . $depen->motif .'</strong></td>
                      <td width="15%" align="center"><strong>' . $depen->montant_depens . '</strong></td>
                      </tr>';
                       $depenglobal +=$depen->montant_depens;
              }
              
              $them .= '<tr>
                      <td width="85%" align="center"><strong>TOTAL</strong></td>
                      <td width="15%" align="center"><strong> '.number_format($depenglobal, 0, '', ' ').'</strong></td>
                                  
                 </tr>';
              $them .= ' </tbody></table>';
              
              $them.= '<h2>DEPENSE TOTAL :'. number_format($depenglobal , 0, '', ' ').' </h2>';
              
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_016.pdf' . '', 'I');
              //============================================================+
              // END OF FILE
              //============================================================+
            }
        }
          //tirage des depots
        public function depot($ckey)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);

              $date1 = $this->input->post('datedebut');
              $date2 = $this->input->post('datefin');
              $typ = $this->input->post('type');
              $gen = $this->input->post('genre');
              $nm = $this->input->post('nom');
              $comp = $this->input->post('_compag');
              $gid = $this->input->post('gareconnect');
              $atr = roleattribut_guard_post_hint($this->entreprise->ekey);
              $dats = explode("-", $date1);
              $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
              $dats1 = explode("-", $date2);
              $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];

              $ngrd = $this->m_gare_depart->getno($gid);

              $ncomp = $this->m_compagnies->getn($comp);
            
            if(recette_role_is_validateur_principal($this->session->agent->userole))
            {
              $tridepo = $this->m_depot->tridepot($this->entreprise->ekey, $gid, $atr, $comp, $date1, $date2, $gen, $nm);

              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('DEPOTS');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('L', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                          
              $titre = '<h1 align="center">ETATS DES DEPOTS DE '.$ncomp->nom_compagnie.' '.$ngrd->garenom.' DU '. $days .' AU '.$days1.'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead> 
                      <tr> 
                        <th width="10%" align="center"><strong>DATE</strong></th>
                        <th width="10%" align="center"><strong>TYPE</strong></th>
                        <th width="10%" align="center"><strong>GENRE</strong></th> 
                        <th width="20%" align="center"><strong>NOM</strong></th>
                        <th width="20%" align="center"><strong>COMMENTAIRE</strong></th>
                        <th width="20%" align="center"><strong>TOTAL</strong></th>
                      </tr>
                  </thead>
                  <tbody>';
                  $depotglobal = 0;
              foreach ($tridepo as $tridpo => $depot) {
                  $them .= '<tr>
                      <td width="10%" align="center"><strong>' . $depot->datedepot . '</strong></td>
                      <td width="10%" align="center"><strong>' . $depot->type_depot . '</strong></td>
                      <td width="10%" align="center"><strong>' . $depot->type_personnel . '</strong></td>
                      <td width="20%" align="center"><strong>' . $depot->nom_pre . '</strong></td>
                      <td width="20%" align="center"><strong>' . $depot->commentaire_depot . '</strong></td>

                      <td width="20%" align="center"><strong>' . $depot->montant_depot . '</strong></td>
                      </tr>';
                      $depotglobal +=$depot->montant_depot;
              }
              $them .= '<tr>
                        <td width="70%" align="center"><strong>TOTAL</strong></td>
                        <td width="20%" align="center"><strong> '.number_format($depotglobal, 0, '', ' ').'</strong></td>
                                    
                   </tr>';
              
              $them .= ' </tbody></table>';
              
              $them.= '<h2>DEPOT TOTAL :'. number_format($depotglobal, 0, '', ' ').' </h2>';
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_017.pdf' . '', 'I');
              //============================================================+
              // END OF FILE
              //============================================================+
            }
            if(recette_role_is_validateur_adjoint($this->session->agent->userole))
            {
              $tridepo = $this->m_depot->adtridepot($this->entreprise->ekey, $gid, $atr, $comp, $date1, $date2, $gen, $nm);

              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('DEPOTS');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('L', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                          
              $titre = '<h1 align="center">ETATS DES DEPOTS DE '.$ncomp->nom_compagnie.' '.$ngrd->garenom.' DU '. $days .' AU '.$days1.'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead> 
                      <tr> 
                        <th width="10%" align="center"><strong>DATE</strong></th>
                        <th width="10%" align="center"><strong>TYPE</strong></th>
                        <th width="10%" align="center"><strong>GENRE</strong></th> 
                        <th width="20%" align="center"><strong>NOM</strong></th>
                        <th width="20%" align="center"><strong>COMMENTAIRE</strong></th>
                        <th width="20%" align="center"><strong>TOTAL</strong></th>
                      </tr>
                  </thead>
                  <tbody>';
                  $depotglobal = 0;
              foreach ($tridepo as $tridpo => $depot) {
                  $them .= '<tr>
                      <td width="10%" align="center"><strong>' . $depot->datedepot . '</strong></td>
                      <td width="10%" align="center"><strong>' . $depot->type_depot . '</strong></td>
                      <td width="10%" align="center"><strong>' . $depot->type_personnel . '</strong></td>
                      <td width="20%" align="center"><strong>' . $depot->nom_pre . '</strong></td>
                      <td width="20%" align="center"><strong>' . $depot->commentaire_depot . '</strong></td>

                      <td width="20%" align="center"><strong>' . $depot->montant_depot . '</strong></td>
                      </tr>';
                      $depotglobal +=$depot->montant_depot;
              }
              $them .= '<tr>
                        <td width="70%" align="center"><strong>TOTAL</strong></td>
                        <td width="20%" align="center"><strong> '.number_format($depotglobal, 0, '', ' ').'</strong></td>
                                    
                   </tr>';
              
              $them .= ' </tbody></table>';
              
              $them.= '<h2>DEPOT TOTAL :'. number_format($depotglobal, 0, '', ' ').' </h2>';
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_017.pdf' . '', 'I');
              //============================================================+
              // END OF FILE
              //============================================================+
            }
            if($this->session->agent->userole === '1' OR $this->session->agent->userole === '2')
            {
              $tridepo = $this->m_depot->tridepotadmin($this->entreprise->ekey, $gid, $comp, $date1, $date2, $typ, $gen, $nm);

              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('DEPOTS');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('L', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                          
              $titre = '<h1 align="center">ETATS DES DEPOTS DE '.$ncomp->nom_compagnie .' '.$ngrd->garenom.' DU '. $days.' AU '.$days1.'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead> 
                      <tr> 
                        <th width="10%" align="center"><strong>DATE</strong></th>
                        <th width="10%" align="center"><strong>TYPE</strong></th>
                        <th width="10%" align="center"><strong>GENRE</strong></th> 
                        <th width="20%" align="center"><strong>NOM</strong></th>
                        <th width="20%" align="center"><strong>COMMENTAIRE</strong></th>
                        <th width="20%" align="center"><strong>TOTAL</strong></th>
                      </tr>
                  </thead>
                  <tbody>';
                  $depotglobal = 0;
              foreach ($tridepo as $tridpo => $depot) {
                  $them .= '<tr>
                      <td width="10%" align="center"><strong>' . $depot->datedepot . '</strong></td>
                      <td width="10%" align="center"><strong>' . $depot->type_depot . '</strong></td>
                      <td width="10%" align="center"><strong>' . $depot->type_personnel . '</strong></td>
                      <td width="20%" align="center"><strong>' . $depot->nom_pre . '</strong></td>
                      <td width="20%" align="center"><strong>' . $depot->commentaire_depot . '</strong></td>

                      <td width="20%" align="center"><strong>' . $depot->montant_depot . '</strong></td>
                      </tr>';
                      $depotglobal +=$depot->montant_depot;
              }
              $them .= '<tr>
                        <td width="70%" align="center"><strong>TOTAL</strong></td>
                        <td width="20%" align="center"><strong> '.number_format($depotglobal, 0, '', ' ').'</strong></td>
                                    
                   </tr>';
              
              $them .= ' </tbody></table>';
              
              $them.= '<h2>DEPOT TOTAL :'. number_format($depotglobal, 0, '', ' ').' </h2>';
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_017.pdf' . '', 'I');
            //============================================================+
            // END OF FILE
            //============================================================+
            }
            elseif (recette_role_is_saisie($this->session->agent->userole))
            {
              $tridepo = $this->m_depot->tridepot_adjoint($this->entreprise->ekey, $gid, $atr, $comp, $date1, $date2, $typ, $gen, $nm);

              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('DEPOTS');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('L', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                          
              $titre = '<h1 align="center">ETATS DES DEPOTS DE '.$ncomp->nom_compagnie.' '.$ngrd->garenom.' DU '. $days.' AU '.$days1.'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead> 
                      <tr> 
                        <th width="10%" align="center"><strong>DATE</strong></th>
                        <th width="10%" align="center"><strong>TYPE</strong></th>
                        <th width="10%" align="center"><strong>GENRE</strong></th> 
                        <th width="20%" align="center"><strong>NOM</strong></th>

                        <th width="20%" align="center"><strong>COMMENTAIRE</strong></th>
                        <th width="20%" align="center"><strong>TOTAL</strong></th>
                      </tr>
                  </thead>
                  <tbody>';
                  $depotglobal = 0;
              foreach ($tridepo as $tridpo => $depot) {
                  $them .= '<tr>
                      <td width="10%" align="center"><strong>' . $depot->datedepot . '</strong></td>
                      <td width="10%" align="center"><strong>' . $depot->type_depot . '</strong></td>
                      <td width="10%" align="center"><strong>' . $depot->type_personnel . '</strong></td>
                      <td width="20%" align="center"><strong>' . $depot->nom_pre . '</strong></td>
                      <td width="20%" align="center"><strong>' . $depot->commentaire_depot . '</strong></td>

                      <td width="20%" align="center"><strong>' . $depot->montant_depot . '</strong></td>
                      </tr>';
                       $depotglobal +=$depot->montant_depot;
              }
              
                $them .= '<tr>
                        <td width="70%" align="center"><strong>TOTAL</strong></td>
                        <td width="20%" align="center"><strong> '.number_format($depotglobal, 0, '', ' ').'</strong></td>
                                    
                   </tr>';
              $them .= ' </tbody></table>';
              
              $them.= '<h2>DEPOT TOTAL :'. number_format($depotglobal , 0, '', ' ').' </h2>';
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_017.pdf' . '', 'I');
            }
        }

        public function autredepot($ckey)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);

              $date1 = $this->input->post('datedebut');
              $date2 = $this->input->post('datefin');
              $typ = $this->input->post('type');
              $gen = $this->input->post('genre');
              $nm = $this->input->post('nom');
              $comp = $this->input->post('_compag');
              $gid = $this->input->post('gareconnect');
              $atr = roleattribut_guard_post_hint($this->entreprise->ekey);
              $ncomp = $this->m_compagnies->getn($comp);

              $ngrd = $this->m_gare_depart->getno($gid);

              $dats = explode("-", $date1);
                $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                $dats1 = explode("-", $date2);
                $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
              
              if($this->session->agent->userole === '4')
              {
                $tridepo = $this->m_depot->autretridepot($this->entreprise->ekey, $gid, $atr, $comp, $date1, $date2, $gen, $nm);

                $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                // set document information
                $pdf->SetCreator(PDF_CREATOR);
                $pdf->SetAuthor('NET SOLUTIONS');
                $pdf->SetTitle('LISTE-');
                $pdf->SetSubject('DEPOTS');
                $pdf->SetKeywords('--');
                
                $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
                // remove default header/footer
                $pdf->setPrintHeader(true);
                $pdf->setPrintFooter(false);
                
                // set default monospaced font
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
                $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                // set margins
                $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                
                
                // set auto page breaks
                $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
                
                // set image scale factor
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
                
                // set font
                
                
                // add a page
                $pdf->AddPage('L', 'A4', 0);
                
                // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                // GROUPE DE GAUCHE
                $pdf->SetFont('courier', '', 9);
                            
                $titre = '<h1 align="center">ETATS DES DEPOTS DE '.$ncomp->nom_compagnie.' '.$ngrd->garenom.' DU '. $days .' AU '.$days1.'</h1>';
                $them = '<table border="1" cellpadding="0">
                    <thead> 
                        <tr> 
                          <th width="10%" align="center"><strong>DATE</strong></th>
                          <th width="10%" align="center"><strong>TYPE</strong></th>
                          <th width="10%" align="center"><strong>GENRE</strong></th> 
                          <th width="20%" align="center"><strong>NOM</strong></th>
                          <th width="20%" align="center"><strong>TOTAL</strong></th>
                          <th width="20%" align="center"><strong>COMMENTAIRE</strong></th>
                        </tr>
                    </thead>
                    <tbody>';
                    $depotglobal = 0;
                foreach ($tridepo as $tridpo => $depot) {
                    $them .= '<tr>
                        <td width="10%" align="center"><strong>' . $depot->datedepot . '</strong></td>
                        <td width="10%" align="center"><strong>' . $depot->type_depot . '</strong></td>
                        <td width="10%" align="center"><strong>' . $depot->genre_depot . '</strong></td>
                        <td width="20%" align="center"><strong>' . $depot->nom_pre . '</strong></td>
                        <td width="20%" align="center"><strong>' . $depot->montant_depot . '</strong></td>
                        <td width="20%" align="center"><strong>' . $depot->commentaire_depot . '</strong></td>
                        </tr>';
                        $depotglobal +=$depot->montant_depot;
                }
                
                $them .= ' </tbody></table>';
                
                $them.= '<h2>DEPOT TOTAL :'. number_format($depotglobal, 0, '', ' ').' </h2>';
                $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
                $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
                ob_end_clean();
                //Close and output PDF document
                $pdf->Output('example_017.pdf' . '', 'I');
              }


              if($this->session->agent->userole === '18')
              {
                $tridepo = $this->m_depot->adautretridepot($this->entreprise->ekey, $gid, $atr, $comp, $date1, $date2, $gen, $nm);

                $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                // set document information
                $pdf->SetCreator(PDF_CREATOR);
                $pdf->SetAuthor('NET SOLUTIONS');
                $pdf->SetTitle('LISTE-');
                $pdf->SetSubject('DEPOTS');
                $pdf->SetKeywords('--');
                
                $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
                // remove default header/footer
                $pdf->setPrintHeader(true);
                $pdf->setPrintFooter(false);
                
                // set default monospaced font
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
                $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                // set margins
                $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                
                
                // set auto page breaks
                $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
                
                // set image scale factor
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
                
                // set font
                
                
                // add a page
                $pdf->AddPage('L', 'A4', 0);
                
                // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                // GROUPE DE GAUCHE
                $pdf->SetFont('courier', '', 9);
                            
                $titre = '<h1 align="center">ETATS DES DEPOTS DE '.$ncomp->nom_compagnie.' '.$ngrd->garenom.' DU '. $days .' AU '.$days1.'</h1>';
                $them = '<table border="1" cellpadding="0">
                    <thead> 
                        <tr> 
                          <th width="10%" align="center"><strong>DATE</strong></th>
                          <th width="10%" align="center"><strong>TYPE</strong></th>
                          <th width="10%" align="center"><strong>GENRE</strong></th> 
                          <th width="20%" align="center"><strong>NOM</strong></th>
                          <th width="20%" align="center"><strong>TOTAL</strong></th>
                          <th width="20%" align="center"><strong>COMMENTAIRE</strong></th>
                        </tr>
                    </thead>
                    <tbody>';
                    $depotglobal = 0;
                foreach ($tridepo as $tridpo => $depot) {
                    $them .= '<tr>
                        <td width="10%" align="center"><strong>' . $depot->datedepot . '</strong></td>
                        <td width="10%" align="center"><strong>' . $depot->type_depot . '</strong></td>
                        <td width="10%" align="center"><strong>' . $depot->genre_depot . '</strong></td>
                        <td width="20%" align="center"><strong>' . $depot->nom_pre . '</strong></td>
                        <td width="20%" align="center"><strong>' . $depot->montant_depot . '</strong></td>
                        <td width="20%" align="center"><strong>' . $depot->commentaire_depot . '</strong></td>
                        </tr>';
                        $depotglobal +=$depot->montant_depot;
                }
                
                $them .= ' </tbody></table>';
                
                $them.= '<h2>DEPOT TOTAL :'. number_format($depotglobal, 0, '', ' ').' </h2>';
                $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
                $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
                ob_end_clean();
                //Close and output PDF document
                $pdf->Output('example_017.pdf' . '', 'I');
              }
              else
              {
                $tridepo = $this->m_depot->autretridepot_adjoint($this->entreprise->ekey, $gid, $comp, $atr, $date1, $date2, $typ, $gen, $nm);

                $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                // set document information
                $pdf->SetCreator(PDF_CREATOR);
                $pdf->SetAuthor('NET SOLUTIONS');
                $pdf->SetTitle('LISTE-');
                $pdf->SetSubject('DEPOTS');
                $pdf->SetKeywords('--');
                
                $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
                // remove default header/footer
                $pdf->setPrintHeader(true);
                $pdf->setPrintFooter(false);
                
                // set default monospaced font
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
                $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                // set margins
                $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                
                
                // set auto page breaks
                $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
                
                // set image scale factor
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
                
                // set font
                
                
                // add a page
                $pdf->AddPage('L', 'A4', 0);
                
                // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                // GROUPE DE GAUCHE
                $pdf->SetFont('courier', '', 9);
                            
                $titre = '<h1 align="center">ETATS DES DEPOTS DE '.$ncomp->nom_compagnie.' '.$ngrd->garenom.' DU '. $days.' AU '.$days1.'</h1>';
                $them = '<table border="1" cellpadding="0">
                    <thead> 
                        <tr> 
                          <th width="10%" align="center"><strong>DATE</strong></th>
                          <th width="10%" align="center"><strong>TYPE</strong></th>
                          <th width="10%" align="center"><strong>GENRE</strong></th> 
                          <th width="20%" align="center"><strong>NOM</strong></th>
                          <th width="20%" align="center"><strong>TOTAL</strong></th>
                          <th width="20%" align="center"><strong>COMMENTAIRE</strong></th>
                        </tr>
                    </thead>
                    <tbody>';
                    $depotglobal = 0;
                foreach ($tridepo as $tridpo => $depot) {
                    $them .= '<tr>
                        <td width="10%" align="center"><strong>' . $depot->datedepot . '</strong></td>
                        <td width="10%" align="center"><strong>' . $depot->type_depot . '</strong></td>
                        <td width="10%" align="center"><strong>' . $depot->genre_depot . '</strong></td>
                        <td width="20%" align="center"><strong>' . $depot->nom_pre . '</strong></td>
                        <td width="20%" align="center"><strong>' . $depot->montant_depot . '</strong></td>
                        <td width="20%" align="center"><strong>' . $depot->commentaire_depot . '</strong></td>
                        </tr>';
                        $depotglobal +=$depot->montant_depot;
                }
                
                $them .= ' </tbody></table>';
                
                $them.= '<h2>DEPOT TOTAL :'. number_format($depotglobal , 0, '', ' ').' </h2>';
                $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
                $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
                ob_end_clean();
                //Close and output PDF document
                $pdf->Output('example_017.pdf' . '', 'I');
              }
        }
          
        public function reportsolde($ckey, $iuser, $r, $dpe, $dpo, $d)
        {
            $dats = explode("-", $d);
            $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                            
            $this->entreprise = $this->m_entreprises->get_key($ckey);

            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('DEPOTS');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('L', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                          
              $titre = '<h1 align="center">ETATS DU '. $days.'</h1>';
              $depotglobal=0;
              $them = '<table border="1" cellpadding="0">
                  <thead> 
                      <tr> 
                        <th width="20%" align="center"><strong>RECETTES</strong></th>
                        <th width="20%" align="center"><strong>DEPENSES</strong></th>
                        <th width="20%" align="center"><strong>DEPOTS</strong></th>
                      </tr>
                  </thead>
                  <tbody>';
                  
                  $them .= '<tr>
                      
                      <td width="20%" align="center"><strong>' . $r. '</strong></td>
                      <td width="20%" align="center"><strong>' . $dep. '</strong></td>
                      <td width="20%" align="center"><strong>' . $depo . '</strong></td>
                      </tr>';
                       $depotglobal=$r-$dep-$depo;
              $them .= '<tr>
                        <td width="40%" align="center"><strong>TOTAL</strong></td>
                        <td width="20%" align="center"><strong> '.number_format($depotglobal, 0, '', ' ').'</strong></td>
                                    
                   </tr>';
              $them .= ' </tbody></table>';
              
              $them.= '<h2>SOLDE :'. number_format($depotglobal, 0, '', ' ').' </h2>';
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_017.pdf' . '', 'I');
              //============================================================+
              // END OF FILE
              //============================================================+

        }

        public function solde($ckey, $iuser, $dpo, $d){

            $dats = explode("-", $d);
            $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                            
            $this->entreprise = $this->m_entreprises->get_key($ckey);

            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('DEPOTS');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('L', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                          
              $titre = '<h1 align="center">ETATS DU '. $days.'</h1>';
              $depotglobal=0;
              $them = '<table border="1" cellpadding="0">
                  <thead> 
                      <tr> 
                        <th width="20%" align="center"><strong>SOLDE</strong></th>
                      </tr>
                  </thead>
                  <tbody>';
                  
                  $them .= '<tr>
                      
                      
                      <td width="20%" align="center"><strong>' . $depo . '</strong></td>
                      </tr>';
                       $depotglobal+=$depo;
              
              $them .= ' </tbody></table>';
              
              $them.= '<h2>SOLDE :'. number_format($depotglobal, 0, '', ' ').' </h2>';
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_017.pdf' . '', 'I');
              //============================================================+
              // END OF FILE
              //============================================================+

        }

          //tri comptable
          //tirage de liste encaissement
        public function triencaissement($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $ivd = $this->input->post('vendeuseid');
            $ddbt = $this->input->post('dated');
            $dfin = $this->input->post('datef');
            $comp = $this->input->post('_compag');
            $gid = $this->input->post('departgar');
            $ncgd = $this->m_gare_depart->getn($gid);
            $gar = $ncgd->nom_gaep;

            $ncomp = $this->m_compagnies->getn($comp);

              $dats = explode("-", $ddbt);
              $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                $dats1 = explode("-", $dfin);
                $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
            if($this->session->agent->userole === '1' OR $this->session->agent->userole === '2')
            {
              $triversements = $this->m_passager->versefiltreadmin($this->entreprise->ekey, $gid, $ddbt, $dfin, $comp, $ivd);
              $triversemes = $this->m_non_passager->versefiltadmin($this->entreprise->ekey, $gid, $ddbt, $dfin, $comp, $ivd);

              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('RECAPT');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('L', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                          
              $titre = '<h1 align="center">VERSEMENT PAR OPERATEUR TICKET DE '.$ncomp->nom_compagnie.' '.$gar.' DU '. $days .' AU '.$days1.' </h1>';
              $them = '<table align="center" border="1" cellpadding="0">
                  <thead> 
                      <tr> 
                        <th width="25%" align="left"><strong>NOM</strong></th>
                        <th width="30%" align="center"><strong>LIGNE</strong></th>
                        <th width="30%" align="right"><strong>MONTANT</strong></th>
                        </tr>
                  </thead>
                  <tbody>';
                  $etatversement = 0;
                  $globaletats= 0;
              foreach ($triversements as $trier => $item) {
                  $them .= '<tr>
                      <td width="25%" align="left"><strong>' . $item->username . '</strong></td>
                      <td width="30%" align="center"><strong>' . $item->nom_ligne . '</strong></td>
                      <td width="30%" align="right"><strong>' . number_format($item->total, 0, '', ' ') . '</strong></td>
                      </tr>';
                            $etatversement += $item->total;
              }
              foreach ($triversemes as $trier1 => $item1) {
                $aler4 = explode("-", $item1->nom_ligne);
                    $allerretour4 = $aler4[1]. '-' .$aler4[0];
                $them .= '<tr>
                    <td width="25%" align="left"><strong>' . $item1->username . '</strong></td>
                    <td width="30%" align="center"><strong>' . $allerretour4 . '</strong></td>
                    <td width="30%" align="right"><strong>' . number_format($item1->totalr, 0, '', ' ') . '</strong></td>
                    </tr>';
                          $globaletats += $item1->totalr;
              }
              $them .= '<tr>
                        <td width="55%" align="left"><strong>TOTAL</strong></td>
                        <td width="30%" align="right"><strong> '.number_format($etatversement + $globaletats, 0, '', ' ').'</strong></td>
                                    
                   </tr>';
              $them .= ' </tbody></table>';
              $them.= '<h2>SOMME:'. number_format($etatversement + $globaletats, 0, '', ' ') .' </h2>';
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_014.pdf' . '', 'I');
            }
            else
            {

              $triversements = $this->m_passager->versefiltre($this->entreprise->ekey, $gid, $ddbt, $dfin, $comp, $ivd);
              $triversemes = $this->m_non_passager->versefilt($this->entreprise->ekey, $gid, $ddbt, $dfin, $comp, $ivd);

              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('RECAPT');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('L', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                          
              $titre = '<h1 align="center">VERSEMENT PAR OPERATEUR TICKET DE '.$ncomp->nom_compagnie.' '.$gar.' DU '. $days .' AU '.$days1.' </h1>';
              $them = '<table align="center" border="1" cellpadding="0">
                  <thead> 
                      <tr> 
                        <th width="25%" align="center"><strong>NOM</strong></th>
                        <th width="30%" align="center"><strong>LIGNE</strong></th>
                        <th width="30%" align="center"><strong>MONTANT</strong></th>
                        </tr>
                  </thead>
                  <tbody>';
                  $etatversement = 0;
                  $globaletats= 0;
                foreach ($triversements as $trier => $item) {
                    $them .= '<tr>
                        <td width="25%" align="left"><strong>' . $item->username . '</strong></td>
                        <td width="30%" align="center"><strong>' . $item->nom_ligne . '</strong></td>
                        <td width="30%" align="right"><strong>' . number_format($item->total, 0, '', ' ') . '</strong></td>
                        </tr>';
                              $etatversement += $item->total;
                }
                foreach ($triversemes as $trier1 => $item1) {
                  $aler4 = explode("-", $item1->nom_ligne);
                      $allerretour4 = $aler4[1]. '-' .$aler4[0];
                  $them .= '<tr>
                    <td width="25%" align="left"><strong>' . $item1->username . '</strong></td>
                    <td width="30%" align="center"><strong>' . $allerretour4 . '</strong></td>
                    <td width="30%" align="right"><strong>' . number_format($item1->totalr, 0, '', ' ') . '</strong></td>
                    </tr>';
                          $globaletats += $item1->totalr;
                }
                  $them .= '<tr>
                          <td width="55%" align="left"><strong>TOTAL</strong></td>
                          <td width="30%" align="right"><strong> '.number_format($etatversement + $globaletats, 0, '', ' ').'</strong></td>
                                      
                     </tr>';
                $them .= ' </tbody></table>';
                $them.= '<h2>SOMME:'. number_format($etatversement + $globaletats, 0, '', ' ') .' </h2>';
                $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
                $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
                ob_end_clean();
                //Close and output PDF document
                $pdf->Output('example_014.pdf' . '', 'I');
                //============================================================+
                // END OF FILE
                //============================================================+
            }
        }

        public function triencaissementsg($ckey, $g, $sg)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $ivd = $this->input->post('vendeuseidsg');
            $ddbt = $this->input->post('datedsg');
            $dfin = $this->input->post('datefsg');
            $comp = $this->input->post('_compagsg');
            $gid = $this->input->post('departgarsg');
            $ncgd = $this->m_gare_depart->getn($gid);

            $gar = $ncgd->nom_gaep;

            $sggd = $this->m_sousgare->sget($this->entreprise->ekey, $gid, $sg);

            $nsgar = $sggd->nomsousgare;


            $ncomp = $this->m_compagnies->getn($comp);

              $dats = explode("-", $ddbt);
              $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                $dats1 = explode("-", $dfin);
                $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
            
              $triversements = $this->m_passager->versefiltreadminsg($this->entreprise->ekey, $gid, $ddbt, $dfin, $comp, $sg, $ivd);
              $triversemes = $this->m_non_passager->versefiltadminsg($this->entreprise->ekey, $gid, $ddbt, $dfin, $comp, $sg, $ivd);

              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('RECAPT');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('L', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                          
              $titre = '<h1 align="center">RECETTE TICKET DE '.$ncomp->nom_compagnie.' '.$gar.' '.$nsgar.' DU '. $days .' AU '.$days1.' </h1>';
              $them = '<table align="center" border="1" cellpadding="0">
                  <thead> 
                      <tr> 
                        <th width="25%" align="left"><strong>NOM</strong></th>
                        <th width="30%" align="center"><strong>LIGNE</strong></th>
                        <th width="30%" align="right"><strong>MONTANT</strong></th>
                        </tr>
                  </thead>
                  <tbody>';
                  $etatversement = 0;
                  $globaletats= 0;
              foreach ($triversements as $trier => $item) {
                  $them .= '<tr>
                      <td width="25%" align="left"><strong>' . $item->username . '</strong></td>
                      <td width="30%" align="center"><strong>' . $item->nom_ligne . '</strong></td>
                      <td width="30%" align="right"><strong>' . number_format($item->total, 0, '', ' ') . '</strong></td>
                      </tr>';
                            $etatversement += $item->total;
              }
              foreach ($triversemes as $trier1 => $item1) {
                $aler4 = explode("-", $item1->nom_ligne);
                    $allerretour4 = $aler4[1]. '-' .$aler4[0];
                $them .= '<tr>
                    <td width="25%" align="left"><strong>' . $item1->username . '</strong></td>
                    <td width="30%" align="center"><strong>' . $allerretour4 . '</strong></td>
                    <td width="30%" align="right"><strong>' . number_format($item1->totalr, 0, '', ' ') . '</strong></td>
                    </tr>';
                          $globaletats += $item1->totalr;
              }
              $them .= '<tr>
                        <td width="55%" align="left"><strong>TOTAL</strong></td>
                        <td width="30%" align="right"><strong> '.number_format($etatversement + $globaletats, 0, '', ' ').'</strong></td>
                                    
                   </tr>';
              $them .= ' </tbody></table>';
              $them.= '<h2>SOMME:'. number_format($etatversement + $globaletats, 0, '', ' ') .' </h2>';
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_014.pdf' . '', 'I');
    
        }
        //tirage de liste encaissement
        public function triencaissements($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
                $ivd = $this->input->post('vendeuseid');
                $ddbt = $this->input->post('dated');
                $dfin = $this->input->post('datef');
                $comp = $this->input->post('_compag');
                $gid = $this->input->post('departgar');
                $uc = $this->m_utilisateur->u($ivd);
              if($uc == NULL){
                $us = '';
              }else{
                $us = $uc->first_name.' '.$uc->last_name;
              }
              $ncomp = $this->m_compagnies->getn($comp);

              $ncgd = $this->m_gare_depart->getn($gid);
                $gar = $ncgd->nom_gaep;

              $dats = explode("-", $ddbt);
              $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
              $dats1 = explode("-", $dfin);                          
              $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
            

                //$triversements = $this->m_comptes_guichet->versfiltre($this->entreprise->ekey, $gid, $ddbt, $dfin, $comp, $ivd);

                $triversements = $this->m_recette->versfiltreadmin($this->entreprise->ekey, $gid, $ddbt, $dfin, $comp, $us);

                $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                // set document information
                $pdf->SetCreator(PDF_CREATOR);
                $pdf->SetAuthor('NET SOLUTIONS');
                $pdf->SetTitle('LISTE-');
                $pdf->SetSubject('RECAPT');
                $pdf->SetKeywords('--');
                
                $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
                // remove default header/footer
                $pdf->setPrintHeader(true);
                $pdf->setPrintFooter(false);
                
                // set default monospaced font
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
                $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                // set margins
                $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                
                
                // set auto page breaks
                $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
                
                // set image scale factor
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
                
                // set font
                
                
                // add a page
                $pdf->AddPage('L', 'A4', 0);
                
                // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                // GROUPE DE GAUCHE
                $pdf->SetFont('courier', '', 9);
                            
                $titre = '<h1 align="center">ETAT DES VERSEMENTS '.$us.' '.$ncomp->nom_compagnie.' '.$gar.' DU '. $days.' AU '.$days1.' </h1>';
                $them = '<table align="center" border="1" cellpadding="0">
                    <thead> 
                        <tr> 
                          <th width="20%" align="left"><strong>DATE VERSEMENTT</strong></th>
                          <th width="30%" align="right"><strong>MONTANT</strong></th>
                          </tr>
                    </thead>
                    <tbody>';
                    $etatversement = 0;
                foreach ($triversements as $trier => $item) {
                  $datsar = explode("-", $item->date_recet);

                  $daysar = $datsar[2]. '-'. $datsar[1]. '-' .$datsar[0];

                    $them .= '<tr>
                        <td width="20%" align="left"><strong>'.$daysar.'</strong></td>
                        <td width="30%" align="right"><strong>' . number_format($item->montant_recet, 0, '', ' ') . '</strong></td>
                        </tr>';
                              $etatversement += $item->montant_recet;
                }
                $them .= '<tr>
                          <td width="20%" align="left"><strong>TOTAL</strong></td>
                          <td width="30%" align="right"><strong> '.number_format($etatversement, 0, '', ' ').'</strong></td>
                                      
                     </tr>';
                    
                $them .= ' </tbody></table>';
                $them.= '<h2>SOMME:'. number_format($etatversement, 0, '', ' ') .' </h2>';
                $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
                $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
                ob_end_clean();
                //Close and output PDF document
                $pdf->Output('example_014.pdf' . '', 'I');
                //============================================================+
                // END OF FILE
                //============================================================+
            
        }
        

        public function triencaissementscour($ckey, $g)
        {
              
              $this->entreprise = $this->m_entreprises->get_key($ckey);

                $ivd = $this->input->post('vendeuseidcour');
                $ddbt = $this->input->post('datedcour');
                $dfin = $this->input->post('datefcour');
                $comp = $this->input->post('_compagcour');
                $gid = $this->input->post('departgarcour');
                $uc = $this->m_utilisateur->u($ivd);
              if($uc == NULL){
                $us = '';
              }else{
                $us = $uc->first_name.' '.$uc->last_name;
              }
              $ncomp = $this->m_compagnies->getn($comp);

              $ncgd = $this->m_gare_depart->getn($gid);
                $gar = $ncgd->nom_gaep;

              $dats = explode("-", $ddbt);
              $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
              $dats1 = explode("-", $dfin);
              $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];

            
                //$triversements = $this->m_comptes_courrierrecet->versfiltreadmincour($this->entreprise->ekey, $gid, $ddbt, $dfin, $comp, $);

                $triversements = $this->m_recette->versfiltreadmincr($this->entreprise->ekey, $gid, $ddbt, $dfin, $comp, $us);

                $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                // set document information
                $pdf->SetCreator(PDF_CREATOR);
                $pdf->SetAuthor('NET SOLUTIONS');
                $pdf->SetTitle('LISTE-');
                $pdf->SetSubject('RECAPT');
                $pdf->SetKeywords('--');
                
                $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
                // remove default header/footer
                $pdf->setPrintHeader(true);
                $pdf->setPrintFooter(false);
                
                // set default monospaced font
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
                $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                // set margins
                $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                
                
                // set auto page breaks
                $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
                
                // set image scale factor
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
                
                // set font
                
                
                // add a page
                $pdf->AddPage('L', 'A4', 0);
                
                // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                // GROUPE DE GAUCHE
                $pdf->SetFont('courier', '', 9);
                            
                $titre = '<h1 align="center">VERSEMENT COURRIER GUICHETIER  '.$us.' '.$ncomp->nom_compagnie.' '.$gar.' DU '. $days.' AU '.$days1.' </h1>';
                $them = '<table align="center" border="1" cellpadding="0">
                    <thead> 
                        <tr> 
                          <th width="20%" align="center"><strong>DATE ARRET</strong></th>
                          <th width="30%" align="center"><strong>MONTANT</strong></th>
                          </tr>
                    </thead>
                    <tbody>';
                    $etatversement = 0;
                foreach ($triversements as $trier => $item) {

                  $datsar = explode("-", $item->date_recet);

                  $daysar = $datsar[2]. '-'. $datsar[1]. '-' .$datsar[0];

                    $them .= '<tr>
                        <td width="20%" align="left"><strong>'.$daysar.'</strong></td>
                        <td width="30%" align="right"><strong>' . number_format($item->montant_recet, 0, '', ' ') . '</strong></td>
                        </tr>';
                              $etatversement += $item->montant_recet;
                }
                $them .= '<tr>
                          <td width="20%" align="left"><strong>TOTAL</strong></td>
                          <td width="30%" align="right"><strong> '.number_format($etatversement, 0, '', ' ').'</strong></td>
                                      
                     </tr>';
                $them .= ' </tbody></table>';
                $them.= '<h2>SOMME:'. number_format($etatversement, 0, '', ' ') .' </h2>';
                $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
                $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
                ob_end_clean();
                //Close and output PDF document
                $pdf->Output('example_014.pdf' . '', 'I');
                //============================================================+
                // END OF FILE
                //============================================================+
            
        }

        public function triencaissementsbag($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
                $ivd = $this->input->post('vendeuseidbag');
                $ddbt = $this->input->post('datedbag');
                $dfin = $this->input->post('datefbag');
                $comp = $this->input->post('_compagbag');
                $gid = $this->input->post('departgarbag');
                $uc = $this->m_utilisateur->u($ivd);
              if($uc == NULL){
                $us = '';
              }else{
                $us = $uc->first_name.' '.$uc->last_name;
              }
              $ncomp = $this->m_compagnies->getn($comp);

              $ncgd = $this->m_gare_depart->getn($gid);
                $gar = $ncgd->nom_gaep;

              $dats = explode("-", $ddbt);
              $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
              $dats1 = explode("-", $dfin);                          
              $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
        

                $triversementsbag = $this->m_recette->versfiltreadminbg($this->entreprise->ekey, $gid, $ddbt, $dfin, $comp, $us);

                $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                // set document information
                $pdf->SetCreator(PDF_CREATOR);
                $pdf->SetAuthor('NET SOLUTIONS');
                $pdf->SetTitle('LISTE-');
                $pdf->SetSubject('RECAPT');
                $pdf->SetKeywords('--');
                
                $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
                // remove default header/footer
                $pdf->setPrintHeader(true);
                $pdf->setPrintFooter(false);
                
                // set default monospaced font
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
                $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                // set margins
                $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                
                
                // set auto page breaks
                $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
                
                // set image scale factor
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
                
                // set font
                
                
                // add a page
                $pdf->AddPage('L', 'A4', 0);
                
                // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                // GROUPE DE GAUCHE
                $pdf->SetFont('courier', '', 9);
                            
                $titre = '<h1 align="center">ETAT DES VERSEMENTS BABAGE '.$us.' '.$ncomp->nom_compagnie.' '.$gar.' DU '. $days.' AU '.$days1.' </h1>';
                $them = '<table align="center" border="1" cellpadding="0">
                    <thead> 
                        <tr> 
                          <th width="20%" align="left"><strong>DATE VERSEMENT</strong></th>
                          <th width="30%" align="right"><strong>MONTANT</strong></th>
                          </tr>
                    </thead>
                    <tbody>';
                    $etatversementbag = 0;
                foreach ($triversementsbag as $trier => $itemb) {
                  $datsar = explode("-", $itemb->date_recet);

                  $daysar = $datsar[2]. '-'. $datsar[1]. '-' .$datsar[0];

                    $them .= '<tr>
                        <td width="20%" align="left"><strong>'.$daysar.'</strong></td>
                        <td width="30%" align="right"><strong>' . number_format($itemb->montant_recet, 0, '', ' ') . '</strong></td>
                        </tr>';
                              $etatversementbag += $itemb->montant_recet;
                }
                $them .= '<tr>
                          <td width="20%" align="left"><strong>TOTAL</strong></td>
                          <td width="30%" align="right"><strong> '.number_format($etatversementbag, 0, '', ' ').'</strong></td>
                                      
                     </tr>';
                    
                $them .= ' </tbody></table>';
                $them.= '<h2>SOMME:'. number_format($etatversementbag, 0, '', ' ') .' </h2>';
                $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
                $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
                ob_end_clean();
                //Close and output PDF document
                $pdf->Output('example_014.pdf' . '', 'I');
                //============================================================+
                // END OF FILE
                //============================================================+
            
        }

        public function triencaissementsexo($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $ivd = $this->input->post('vendeuseidexo');
            $ddbt = $this->input->post('datedexo');
            $dfin = $this->input->post('datefexo');
            $comp = $this->input->post('_compagexo');
            $gid = $this->input->post('departgarexo');
            $uc = $this->m_utilisateur->u($ivd);

            $ncgd = $this->m_gare_depart->getn($gid);
                $gar = $ncgd->nom_gaep;

            if($uc == NULL){
              $us = '';
            }
            else
            {
              $us = $uc->first_name.' '.$uc->last_name;
            }
            $ncomp = $this->m_compagnies->getn($comp);
            $dats = explode("-", $ddbt);
            $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
            $dats1 = explode("-", $dfin);                          
            $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
            
              if($comp == 5002){

                  $onreport = $this->m_passager->listereportverscptgle($this->entreprise->ekey, $comp, $ddbt, $dfin, $gid, $ivd);
                  $retourreport = $this->m_non_passager->listereportversretourcpte($this->entreprise->ekey, $comp, $ddbt, $dfin, $gid, $ivd);

              }

              else
              {


                $onreport = $this->m_passager->listereportverscptglexo($this->entreprise->ekey, $comp, $ddbt, $dfin, $gid, $ivd);
                $retourreport = $this->m_non_passager->listereportversretourcptexo($this->entreprise->ekey, $comp, $ddbt, $dfin, $gid, $ivd);
              }
                $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                // set document information
                $pdf->SetCreator(PDF_CREATOR);
                $pdf->SetAuthor('NET SOLUTIONS');
                $pdf->SetTitle('LISTE-');
                $pdf->SetSubject('RECAPT');
                $pdf->SetKeywords('--');
                
                $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
                // remove default header/footer
                $pdf->setPrintHeader(true);
                $pdf->setPrintFooter(false);
                
                // set default monospaced font
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
                $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                // set margins
                $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                
                
                // set auto page breaks
                $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
                
                // set image scale factor
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
                
                // set font
                
                
                // add a page
                $pdf->AddPage('L', 'A4', 0);
                
                // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                // GROUPE DE GAUCHE
                $pdf->SetFont('courier', '', 9);
                            
                $titre = '<h1 align="center">BROUILLARD(EXERCICE)TICKET '.$us.' '.$ncomp->nom_compagnie.' '.$gar.' DU '.$days.' AU '.$days1.' </h1>';
                $them = '<table align="center" border="1" cellpadding="0">
                    <thead> 
                        <tr> 
                          <th width="20%" align="left"><strong>DATE VENTE</strong></th>
                          <th width="30%" align="right"><strong>MONTANT</strong></th>
                          </tr>
                    </thead>
                    <tbody>';
                    $p = 0;
                    $pr = 0;
                foreach ($onreport as $departh => $element) {
                  
                  $datsar1 = explode("-", $element->datep_create);

                  $daysar1 = $datsar1[2]. '-'. $datsar1[1]. '-' .$datsar1[0];

                    $them .= '<tr>
                        <td width="20%" align="left"><strong>' . $daysar1 . '</strong></td>
                        
                        <td width="30%" align="right"><strong>' . number_format(($element->total), 0, '', ' ') . '</strong></td>
                        </tr>';
                         
                         $p += $element->total;
                        
                }
                $them .= '<tr>
                        <td width="50%" align="center">RETOUR<strong></strong></td>
                        </tr>';
                foreach ($retourreport as $retours => $retour) {

                  $datsar2 = explode("-", $retour->datevente);

                  $daysar2 = $datsar2[2]. '-'. $datsar2[1]. '-' .$datsar2[0];
                   
                    $them .= '<tr>
                        <td width="20%" align="left"><strong>' .$daysar2 . '</strong></td>
                        
                        <td width="30%" align="right"><strong>' . number_format(($retour->totalr), 0, '', ' ') . '</strong></td>
                        </tr>';
                         
                         $pr += $retour->totalr;
                }
              
                $them .= '<tr>
                          <td width="20%" align="left"><strong>TOTAL</strong></td>
                          <td width="30%" align="right"><strong> '.number_format($p + $pr, 0, '', ' ').'</strong></td>
                                      
                     </tr>';
                    
                $them .= ' </tbody></table>';

                
                $them.= '<h2>SOMME:'. number_format($p + $pr, 0, '', ' ') .' </h2>';
                $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
                $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
                ob_end_clean();
                //Close and output PDF document
                $pdf->Output('example_014.pdf' . '', 'I');
                //============================================================+
                // END OF FILE
                //============================================================+
            
        }

        public function triencaissementsexoesc($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $ivd = $this->input->post('vendeuseidexoesc');
            $ddbt = $this->input->post('datedexoesc');
            $dfin = $this->input->post('datefexoesc');
            $comp = $this->input->post('_compagexoesc');
            $gid = $this->input->post('departgarexoesc');
            $uc = $this->m_utilisateur->u($ivd);

            $ncgd = $this->m_gare_depart->getn($gid);
                $gar = $ncgd->nom_gaep;

            if($uc == NULL){
              $us = '';
            }
            else
            {
              $us = $uc->first_name.' '.$uc->last_name;
            }
            $ncomp = $this->m_compagnies->getn($comp);
            $dats = explode("-", $ddbt);
            $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
            $dats1 = explode("-", $dfin);                          
            $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
            
                $onreport = $this->m_escalclients->listereportverscptglexo($this->entreprise->ekey, $comp, $gid, $ddbt, $dfin, $ivd);
              
                $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                // set document information
                $pdf->SetCreator(PDF_CREATOR);
                $pdf->SetAuthor('NET SOLUTIONS');
                $pdf->SetTitle('LISTE-');
                $pdf->SetSubject('RECAPT');
                $pdf->SetKeywords('--');
                
                $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
                // remove default header/footer
                $pdf->setPrintHeader(true);
                $pdf->setPrintFooter(false);
                
                // set default monospaced font
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
                $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                // set margins
                $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                
                
                // set auto page breaks
                $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
                
                // set image scale factor
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
                
                // set font
                
                
                // add a page
                $pdf->AddPage('L', 'A4', 0);
                
                // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                // GROUPE DE GAUCHE
                $pdf->SetFont('courier', '', 9);
                            
                $titre = '<h1 align="center">BROUILLARD(EXERCICE)TICKET ESCAL'.$us.' '.$ncomp->nom_compagnie.' '.$gar.' DU '.$days.' AU '.$days1.' </h1>';
                $them = '<table align="center" border="1" cellpadding="0">
                    <thead> 
                        <tr> 
                          <th width="20%" align="left"><strong>DATE VENTE</strong></th>
                          <th width="30%" align="right"><strong>MONTANT</strong></th>
                          </tr>
                    </thead>
                    <tbody>';
                    $p = 0;
                    $pr = 0;
                foreach ($onreport as $departh => $element) {
                  
                  $datsar1 = explode("-", $element->datedepescal);

                  $daysar1 = $datsar1[2]. '-'. $datsar1[1]. '-' .$datsar1[0];

                    $them .= '<tr>
                        <td width="20%" align="left"><strong>' . $daysar1 . '</strong></td>
                        
                        <td width="30%" align="right"><strong>' . number_format(($element->tota), 0, '', ' ') . '</strong></td>
                        </tr>';
                         
                         $p += $element->tota;
                        
                }
                
              
                $them .= '<tr>
                          <td width="20%" align="left"><strong>TOTAL</strong></td>
                          <td width="30%" align="right"><strong> '.number_format($p, 0, '', ' ').'</strong></td>
                                      
                     </tr>';
                    
                $them .= ' </tbody></table>';

                
                $them.= '<h2>SOMME:'. number_format($p, 0, '', ' ') .' </h2>';
                $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
                $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
                ob_end_clean();
                //Close and output PDF document
                $pdf->Output('example_014.pdf' . '', 'I');
                //============================================================+
                // END OF FILE
                //============================================================+
            
        }

        public function triencaissementsexobag($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $ivd = $this->input->post('vendeuseidexobg');
            $ddbt = $this->input->post('datedexobg');
            $dfin = $this->input->post('datefexobg');
            $comp = $this->input->post('_compagexobg');
            $gid = $this->input->post('departgarexobg');
            $uc = $this->m_utilisateur->u($ivd);

            $ncgd = $this->m_gare_depart->getn($gid);
                $gar = $ncgd->nom_gaep;

            if($uc == NULL){
              $us = '';
            }
            else
            {
              $us = $uc->first_name.' '.$uc->last_name;
            }
            $ncomp = $this->m_compagnies->getn($comp);
            $dats = explode("-", $ddbt);
            $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
            $dats1 = explode("-", $dfin);                          
            $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
            
              
                $onreportbg = $this->m_bagage->listereportverscptglexo($this->entreprise->ekey, $comp, $ddbt, $dfin, $gid, $ivd);
                
                $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                // set document information
                $pdf->SetCreator(PDF_CREATOR);
                $pdf->SetAuthor('NET SOLUTIONS');
                $pdf->SetTitle('LISTE-');
                $pdf->SetSubject('RECAPT');
                $pdf->SetKeywords('--');
                
                $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
                // remove default header/footer
                $pdf->setPrintHeader(true);
                $pdf->setPrintFooter(false);
                
                // set default monospaced font
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
                $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                // set margins
                $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                
                // set auto page breaks
                $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
                
                // set image scale factor
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
                
                // set font
                
                
                // add a page
                $pdf->AddPage('L', 'A4', 0);
                
                // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                // GROUPE DE GAUCHE
                $pdf->SetFont('courier', '', 9);
                            
                $titre = '<h1 align="center">BROUILLARD(EXERCICE)BAGAGES '.$us.' '.$ncomp->nom_compagnie.' '.$gar.' DU '.$days.' AU '.$days1.' </h1>';
                $them = '<table align="center" border="1" cellpadding="0">
                    <thead> 
                        <tr> 
                          <th width="20%" align="left"><strong>DATE VENTE</strong></th>
                          <th width="30%" align="right"><strong>MONTANT</strong></th>
                          </tr>
                    </thead>
                    <tbody>';
                    $ps = 0;
                foreach ($onreportbg as $departh => $elements) {
                  
                  $datsar1 = explode("-", $elements->date_create);

                  $daysar1 = $datsar1[2]. '-'. $datsar1[1]. '-' .$datsar1[0];

                    $them .= '<tr>
                        <td width="20%" align="left"><strong>' . $daysar1 . '</strong></td>
                        
                        <td width="30%" align="right"><strong>' . number_format(($elements->total), 0, '', ' ') . '</strong></td>
                        </tr>';
                         
                         $ps += $elements->total;
                }
                
                $them .= ' </tbody></table>';
                
                $them.= '<h2>SOMME:'. number_format($ps, 0, '', ' ') .' </h2>';
                $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
                $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
                ob_end_clean();
                //Close and output PDF document
                $pdf->Output('example_014.pdf' . '', 'I');
                //============================================================+
                // END OF FILE
                //============================================================+
            
        }

        public function triencaissementsexobagesc($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $ivd = $this->input->post('vendeuseidexobgesc');
            $ddbt = $this->input->post('datedexobgesc');
            $dfin = $this->input->post('datefexobgesc');
            $comp = $this->input->post('_compagexobgesc');
            $gid = $this->input->post('departgarexobgesc');

            $uc = $this->m_utilisateur->u($ivd);

            $ncgd = $this->m_gare_depart->getn($gid);
                $gar = $ncgd->nom_gaep;

            if($uc == NULL){
              $us = '';
            }
            else
            {
              $us = $uc->first_name.' '.$uc->last_name;
            }
            $ncomp = $this->m_compagnies->getn($comp);
            $dats = explode("-", $ddbt);
            $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
            $dats1 = explode("-", $dfin);                          
            $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
            
              
                $onreportbgesc = $this->m_bagageesc->listereportverscptglexo($this->entreprise->ekey, $comp, $ddbt, $dfin, $gid, $ivd);

                //var_dump($onreportbgesc, $ivd, $ddbt, $dfin, $comp,$gid);
                
                $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                // set document information
                $pdf->SetCreator(PDF_CREATOR);
                $pdf->SetAuthor('NET SOLUTIONS');
                $pdf->SetTitle('LISTE-');
                $pdf->SetSubject('RECAPT');
                $pdf->SetKeywords('--');
                
                $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
                // remove default header/footer
                $pdf->setPrintHeader(true);
                $pdf->setPrintFooter(false);
                
                // set default monospaced font
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
                $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                // set margins
                $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                
                // set auto page breaks
                $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
                
                // set image scale factor
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
                
                // set font
                
                
                // add a page
                $pdf->AddPage('L', 'A4', 0);
                
                // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                // GROUPE DE GAUCHE
                $pdf->SetFont('courier', '', 9);
                            
                $titre = '<h1 align="center">BROUILLARD(EXERCICE)BAGAGES ESCAL '.$us.' '.$ncomp->nom_compagnie.' '.$gar.' DU '.$days.' AU '.$days1.' </h1>';
                $them = '<table align="center" border="1" cellpadding="0">
                    <thead> 
                        <tr> 
                          <th width="20%" align="left"><strong>DATE VENTE</strong></th>
                          <th width="30%" align="right"><strong>MONTANT</strong></th>
                          </tr>
                    </thead>
                    <tbody>';
                    $ps = 0;
                foreach ($onreportbgesc as $departh => $elements) {
                  
                  $datsar1 = explode("-", $elements->date_createesc);

                  $daysar1 = $datsar1[2]. '-'. $datsar1[1]. '-' .$datsar1[0];

                    $them .= '<tr>
                        <td width="20%" align="left"><strong>' . $daysar1 . '</strong></td>
                        
                        <td width="30%" align="right"><strong>' . number_format(($elements->total), 0, '', ' ') . '</strong></td>
                        </tr>';
                         
                         $ps += $elements->total;
                }
                
                $them .= ' </tbody></table>';
                
                $them.= '<h2>SOMME:'. number_format($ps, 0, '', ' ') .' </h2>';
                $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
                $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
                ob_end_clean();
                //Close and output PDF document
                $pdf->Output('example_014.pdf' . '', 'I');
                //============================================================+
                // END OF FILE
                //============================================================+
            
        }
        //depenses courrier

        public function tridepensescour($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
                $ivd = $this->input->post('caissiercourdep');
                $ddbt = $this->input->post('datedebutcourdep');
                $dfin = $this->input->post('datefincourdep');
                $comp = $this->input->post('_compagcourdep');
                $gid = $this->input->post('departgarcourdep');

                $uc = $this->m_utilisateur->u($ivd);
              if($uc == NULL){
                $us = '';
              }else{
                $us = $uc->first_name.' '.$uc->last_name;
              }
                $ncomp = $this->m_compagnies->getn($comp);

                $dats = explode("-", $ddbt);
                          
                  $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                  $dats1 = explode("-", $dfin);
                  $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];

            
                $tridepenses = $this->m_comptes_courrierdepens->depsfiltrecour($this->entreprise->ekey, $gid, $ddbt, $dfin, $comp, $ivd);
                $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                // set document information
                $pdf->SetCreator(PDF_CREATOR);
                $pdf->SetAuthor('NET SOLUTIONS');
                $pdf->SetTitle('LISTE-');
                $pdf->SetSubject('RECAPTDEPENSES');
                $pdf->SetKeywords('--');
                
                $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
                // remove default header/footer
                $pdf->setPrintHeader(true);
                $pdf->setPrintFooter(false);
                
                // set default monospaced font
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
                $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                // set margins
                $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                
                
                // set auto page breaks
                $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
                
                // set image scale factor
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
                
                // set font
                
                
                // add a page
                $pdf->AddPage('L', 'A4', 0);
                
                // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                // GROUPE DE GAUCHE
                $pdf->SetFont('courier', '', 9);
                            
                $titre = '<h1 align="center">RECAP DEPENSE COURRIER  '.$us.' '.$ncomp->nom_compagnie.' DU '. $days.' AU '.$days1.' </h1>';
                $them = '<table align="center" border="1" cellpadding="0">
                    <thead> 
                        <tr> 
                          <th width="20%" align="center"><strong>DATE</strong></th>
                          <th width="30%" align="center"><strong>MONTANT</strong></th>
                          </tr>
                    </thead>
                    <tbody>';
                    $etatdepenses = 0;
                foreach ($tridepenses as $trier => $items) {
                  
                  $datsar = explode("-", $items->comptdatearretdepens);

                  $daysar = $datsar[2]. '-'. $datsar[1]. '-' .$datsar[0];
                    $them .= '<tr>
                        <td width="20%" align="left"><strong>'.$daysar.'</strong></td>
                        <td width="30%" align="right"><strong>' . number_format($items->comptemontdepens, 0, '', ' ') . '</strong></td>
                        </tr>';
                              $etatdepenses += $items->comptemontdepens;
                }
                $them .= '<tr>
                          <td width="20%" align="left"><strong>TOTAL</strong></td>
                          <td width="30%" align="right"><strong> '.number_format($etatdepenses, 0, '', ' ').'</strong></td>
                                      
                     </tr>';
                    
                $them .= ' </tbody></table>';
                $them.= '<h2>SOMME:'. number_format($etatdepenses, 0, '', ' ') .' </h2>';
                $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
                $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
                ob_end_clean();
                //Close and output PDF document
                $pdf->Output('example_014.pdf' . '', 'I');
                //============================================================+
                // END OF FILE
                //============================================================+
            
        }
        //reportcourrier

        public function reportscour($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);

              $dt1 = $this->input->post('datedebutcour');
              $dt2 = $this->input->post('datefincour');
              $cais = $this->input->post('caissiercour');
              $lign = $this->input->post('axelignecour');
              $comp = $this->input->post('_compagcour');
              $gid = $this->input->post('departgarcour');

              $uc = $this->m_utilisateur->u($cais);
              if($uc == NULL){
                $us = '';
              }else{
                $us = $uc->first_name.' '.$uc->last_name;
              }
              $dats = explode("-", $dt1);
              $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
              $dats1 = explode("-", $dt2);
              $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];

              $recettereport = $this->m_courrier_expedier->listereportcour($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $cais, $lign);

              $transfertreport = $this->m_courrier_recet->reporttransfert($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $cais);

              //var_dump($recettereport, $transfertreport);
              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('RECAPTULATIF');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('L', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                          
              $titre = '<h1 align="center">REPORT GLOBAL DES COURRIERS  '.$us.' DU '. $days .' AU '.$days1.'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead> 
                      <tr> 
                        
                        <th width="20%" align="center"><strong>LIGNE</strong></th>
                        <th width="15%" align="center"><strong>NOMBRE</strong></th> 
                        <th width="20%" align="center"><strong>PRIX_UNITAIRE</strong></th>
                        <th width="20%" align="center"><strong>PRIX_TOTAL</strong></th>
                      </tr>
                  </thead>
                  <tbody>';
                  $tglobal = 0;
                  $tglobalretour = 0;
                  $nb = 0;
                  $nbtr = 0;
                  $p = 0;
                  $pr = 0;
                  $nombr = 0;
                  $fraisenvoie = 0;
                  $frai = 0;
                  
              foreach ($recettereport as $departh => $element) {
                  $them .= '<tr>
                      
                      <td width="20%" align="left"><strong>' . $element->nom_ligne . '</strong></td>
                      <td width="15%" align="center"><strong>' . $element->nombres . '</strong></td>
                      <td width="20%" align="center"><strong>' . number_format($element->prixcolis, 0, '', ' ') . '</strong></td>
                      <td width="20%" align="right"><strong>' . number_format($element->montant, 0, '', ' ') . '</strong></td>
                      </tr>';
                       $tglobal += $element->montant;
                       $nb += round($element->nombres);
                       $p += $element->prixcolis;
                      }
        
                  /*foreach ($transfertreport as $retours => $retrs) {
                     
                     $nombr = $retrs->nombres;
                     $fraisenvoie = $retrs->montant;
                     $frai = $retrs->frais;

                    $them .= '<tr>
                        
                        <td width="20%" align="left"><strong></strong></td>
                        <td width="15%" align="center"><strong>' . $retrs->nombres . '</strong></td>
                        <td width="20%" align="center"><strong>' . number_format($fraisenvoie, 0, '', ' ') . '</strong></td>
                        <td width="20%" align="right"><strong>' . number_format($frai, 0, '', ' ') . '</strong></td>
                        </tr>';
                          $tglobalretour +=$retrs->frais;
                          $nbrt += round($nombr);
                            $pr += $retrs->fraisenvoi;
                        }*/
                      $them .= '<tr>
                        <td width="20%" align="left"><strong>TOTAL</strong></td>
                        <td width="15%" align="center"><strong> '.($nb).'</strong></td>
                        <td width="20%" align="center"><strong></strong></td>
                        <td width="20%" align="right"><strong> '.number_format($tglobal, 0, '', ' ').'</strong></td>
                        
                   </tr>';
              $them .= ' </tbody></table>';
              $them.= '<h2>SOMME:'. number_format($tglobal, 0, '', ' ') .' </h2>';
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_013.pdf' . '', 'I');
              //============================================================+
              // END OF FILE
              //============================================================+
            
        }

        public function reports($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);

              $dt1 = $this->input->post('datedebut');
              $dt2 = $this->input->post('datefin');
              $cais = $this->input->post('caissier');
              $lign = $this->input->post('axeligne');
              $comp = $this->input->post('_compag');
              $gid = $this->input->post('departgar');
              $uc = $this->m_utilisateur->u($cais);
              if($uc == NULL){
                $us = '';
              }else{
                $us = $uc->first_name.' '.$uc->last_name;
              }
              $dats = explode("-", $dt1);
              $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
              $dats1 = explode("-", $dt2);

              $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
              $onreport = $this->m_passager->listereport($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $cais, $lign);
              $retourreport = $this->m_non_passager->listereportretour($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $cais, $lign);

              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('RECAPTULATIF');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('L', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                          
              $titre = '<h1 align="center">ETAT GLOBAL TICKET GUICHETIER '.$us.' DU '. $days .' AU '.$days1.'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead> 
                      <tr> 
                        
                        <th width="20%" align="center"><strong>LIGNE</strong></th>
                        <th width="15%" align="center"><strong>NBR_TICKETS</strong></th> 
                        <th width="20%" align="center"><strong>PRIX_UNITAIRE</strong></th>
                        <th width="20%" align="center"><strong>PRIX_TOTAL</strong></th>
                      </tr>
                  </thead>
                  <tbody>';
                  $tglobal = 0;
                  $tglobalretour = 0;
                  $nb = 0;
                  $nbrt = 0;
                  $p = 0;
                  $pr = 0;
              foreach ($onreport as $departh => $element) {
                  $them .= '<tr>
                      
                      <td width="20%" align="left"><strong>' . $element->nom_ligne . '</strong></td>
                      <td width="15%" align="center"><strong>' . $element->codepassager . '</strong></td>
                      <td width="20%" align="center"><strong>' . number_format($element->prixvente, 0, '', ' ') . '</strong></td>
                      <td width="20%" align="right"><strong>' . number_format($element->total, 0, '', ' ') . '</strong></td>
                      </tr>';
                       $tglobal +=$element->total;
                       $nb +=round($element->codepassager);
                       $p += $element->prixvente;
                      }
        
                  foreach ($retourreport as $retours => $retour) {
                     $aler6 = explode("-", $retour->nom_ligne);
                    $allerretour6 = $aler6[1]. '-' .$aler6[0];
                    $them .= '<tr>
                        
                        <td width="20%" align="left"><strong>' . $allerretour6 . '</strong></td>
                        <td width="15%" align="center"><strong>' . $retour->code_non_pass . '</strong></td>
                        <td width="20%" align="center"><strong>' . number_format($retour->prixretour, 0, '', ' ') . '</strong></td>
                        <td width="20%" align="right"><strong>' . number_format($retour->totalr, 0, '', ' ') . '</strong></td>
                        </tr>';
                          $tglobalretour +=$retour->totalr;
                          $nbrt +=round($retour->code_non_pass);
                            $pr += $retour->prixretour;
                        }
                      $them .= '<tr>
                        <td width="20%" align="left"><strong>TOTAL</strong></td>
                        <td width="15%" align="center"><strong> '.($nb+$nbrt).'</strong></td>
                        <td width="20%" align="center"><strong></strong></td>
                        <td width="20%" align="right"><strong> '.number_format($tglobal + $tglobalretour, 0, '', ' ').'</strong></td>
                        
                   </tr>';
              $them .= ' </tbody></table>';
              $them.= '<h2>SOMME:'. number_format($tglobal + $tglobalretour, 0, '', ' ') .' </h2>';
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_012.pdf' . '', 'I');
              //============================================================+
              // END OF FILE
              //============================================================+
        }

        //escal

        public function reportsesc($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);

              $dt1 = $this->input->post('datedebutesc');
              $dt2 = $this->input->post('datefinesc');
              $cais = $this->input->post('caissieresc');
              $lign = $this->input->post('axeligneesc');
              $comp = $this->input->post('_compagesc');
              $gid = $this->input->post('departgaresc');
              $uc = $this->m_utilisateur->u($cais);
              if($uc == NULL){
                $us = '';
              }else{
                $us = $uc->first_name.' '.$uc->last_name;
              }
              $dats = explode("-", $dt1);
              $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
              $dats1 = explode("-", $dt2);

              $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
              $onreport = $this->m_escalclients->listereportesc($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $cais, $lign);
              
              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('RECAPTULATIF');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('L', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                          
              $titre = '<h1 align="center">ETAT GLOBAL TICKET GUICHETIER ESCAL'.$us.' DU '. $days .' AU '.$days1.'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead> 
                      <tr> 
                        <th width="20%" align="center"><strong>LIGNE</strong></th>
                        <th width="15%" align="center"><strong>NBR_TICKETS</strong></th> 
                        <th width="20%" align="center"><strong>PRIX_UNITAIRE</strong></th>
                        <th width="20%" align="center"><strong>PRIX_TOTAL</strong></th>
                      </tr>
                  </thead>
                  <tbody>';
                  $tglobal = 0;
                  $tglobalretour = 0;
                  $nb = 0;
                  $nbrt = 0;
                  $p = 0;
                  $pr = 0;
              foreach ($onreport as $departh => $element) {
                  $them .= '<tr>
                      
                      <td width="20%" align="left"><strong>' . $element->nom_ligne . '</strong></td>
                      <td width="15%" align="center"><strong>' . $element->escalp . '</strong></td>
                      <td width="20%" align="center"><strong>' . number_format($element->prixescal, 0, '', ' ') . '</strong></td>
                      <td width="20%" align="right"><strong>' . number_format($element->tota, 0, '', ' ') . '</strong></td>
                      </tr>';
                       $tglobal +=$element->tota;
                       $nb +=$element->escalp;
                       $p += $element->prixescal;
                    }
        
                  
                      $them .= '<tr>
                        <td width="20%" align="left"><strong>TOTAL</strong></td>
                        <td width="15%" align="center"><strong> '.($nb).'</strong></td>
                        <td width="20%" align="center"><strong></strong></td>
                        <td width="20%" align="right"><strong> '.number_format($tglobal, 0, '', ' ').'</strong></td>
                        
                   </tr>';
              $them .= ' </tbody></table>';
              $them.= '<h2>SOMME:'. number_format($tglobal, 0, '', ' ') .' </h2>';
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_012.pdf' . '', 'I');
              //============================================================+
              // END OF FILE
              //============================================================+
        }
        //recap
        public function reporticket($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
              $dt1 = $this->input->post('datedebut');
              $dt2 = $this->input->post('datefin');
              $lign = $this->input->post('axeligne');
              $comp = $this->input->post('_compag');
              $gid = $this->input->post('departgar');
                $dats = explode("-", $dt1);
                  $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                  $dats1 = explode("-", $dt2);
                  $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
              $reportick = $this->m_passager->reporticket($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
              $reportickreour = $this->m_non_passager->reporticketretour($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);

              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('CBT_RAKIETA');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('L', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                          
              $titre = '<h1 align="center">RECAP GLOBAL TICKET DU '. $days.' AU '.$days1.'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead> 
                      <tr> 
                        <th width="20%" align="left"><strong>LIGNE</strong></th>
                        <th width="15%" align="center"><strong>NBR_TICKETS</strong></th> 
                        <th width="20%" align="center"><strong>PRIX_UNITAIRE</strong></th>
                        <th width="20%" align="center"><strong>PRIX_TOTAL</strong></th>
                      </tr>
                  </thead>
                  <tbody>';
                  $etatglobal = 0;
                  $etatretours = 0;
                  $nb = 0;
                  $nbrt = 0;
                  $p = 0;
                  $pr = 0;
              foreach ($reportick as $departick => $lement) {
                  $them .= '<tr>
                      <td width="20%" align="left"><strong>' . $lement->nom_ligne . '</strong></td>
                      <td width="15%" align="center"><strong>' . $lement->codepassager . '</strong></td>
                      <td width="20%" align="center"><strong>' . number_format($lement->prixvente, 0, '', ' ') . '</strong></td>
                      <td width="20%" align="right"><strong>' . number_format($lement->total, 0, '', ' ') . '</strong></td>
                      </tr>';
                      $etatglobal +=$lement->total;
                      $nb += round($lement->codepassager);
                       $p += $lement->prixvente;
              }
            
        
            foreach ($reportickreour as $etatretour => $etatreto) {
              $aler1 = explode("-", $etatreto->nom_ligne);
                    $allerretour1 = $aler1[1]. '-' .$aler1[0];
              $them .= '<tr>
                  <td width="20%" align="left"><strong>' . $allerretour1 . '</strong></td>
                  <td width="15%" align="center"><strong>' . $etatreto->code_non_pass . '</strong></td>
                  <td width="20%" align="center"><strong>' . number_format($etatreto->prixretour, 0, '', ' ') . '</strong></td>
                  <td width="20%" align="right"><strong>' . number_format($etatreto->totalr, 0, '', ' ') . '</strong></td>
                  </tr>';
                    $etatretours +=$etatreto->totalr;
                    $nbrt +=round($etatreto->code_non_pass);
                      $pr += $etatreto->prixretour;
                  }
                $them .= '<tr>
                        <td width="20%" align="center"><strong>TOTAL</strong></td>
                        <td width="15%" align="center"><strong> '.($nb+$nbrt).'</strong></td>
                        <td width="20%" align="center"><strong></strong></td>
                        <td width="20%" align="right"><strong> '.number_format($etatglobal + $etatretours, 0, '', ' ').'</strong></td>
                        
                   </tr>';
            $them .= ' </tbody></table>';
            $them.= '<h2>SOMME:'. number_format($etatglobal + $etatretours, 0, '', ' ') .' </h2>';
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_013.pdf' . '', 'I');
              //============================================================+
              // END OF FILE
              //============================================================+
        }

        public function reporticketesc($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
              $dt1 = $this->input->post('datedebutesc');
              $dt2 = $this->input->post('datefinesc');
              $lign = $this->input->post('axeligneesc');
              $comp = $this->input->post('_compagesc');
              $gid = $this->input->post('departgaresc');

              $ncomp = $this->m_compagnies->getn($comp);

                $dats = explode("-", $dt1);
                $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                $dats1 = explode("-", $dt2);
                $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
                
                $reportick = $this->m_escalclients->reporticketcptad($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
              

              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('CBT_RAKIETA');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('L', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                          
              $titre = '<h1 align="center">RECAP GLOBAL TICKET ESCAL DU '. $days.' AU '.$days1.'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead> 
                      <tr> 
                        <th width="20%" align="left"><strong>LIGNE</strong></th>
                        <th width="15%" align="center"><strong>NBR_TICKETS</strong></th> 
                        <th width="20%" align="center"><strong>PRIX_UNITAIRE</strong></th>
                        <th width="20%" align="center"><strong>PRIX_TOTAL</strong></th>
                      </tr>
                  </thead>
                  <tbody>';
                  $etatglobale = 0;
                  $nb = 0;
                  $p = 0;
                    foreach ($reportick as $departick => $lement) {
                      $them .= '<tr>
                          <td width="20%" align="left"><strong>' . $lement->nom_ligne . '</strong></td>
                          <td width="15%" align="center"><strong>' . round($lement->escalp) . '</strong></td>
                          <td width="20%" align="center"><strong>' . number_format($lement->prixescal, 0, '', ' ') . '</strong></td>
                          <td width="20%" align="right"><strong>' . number_format(($lement->escalp)*($lement->prixescal), 0, '', ' ') . '</strong></td>
                          </tr>';
                           $etatglobale += ($lement->escalp)*($lement->prixescal);
                           $nb +=$lement->escalp;
                           $p += $lement->prixescal;
                    }
                    $them .= '<tr>
                        <td width="20%" align="center"><strong>TOTAL</strong></td>
                        <td width="15%" align="center"><strong> '.($nb
                          ).'</strong></td>
                        <td width="20%" align="center"><strong></strong></td>
                        <td width="20%" align="right"><strong> '.number_format($etatglobal, 0, '', ' ').'</strong></td>
                   </tr>';
            $them .= ' </tbody></table>';
            $them.= '<h2>SOMME:'. number_format($etatglobal + $etatretours, 0, '', ' ') .' </h2>';
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_013.pdf' . '', 'I');
              //============================================================+
              // END OF FILE
              //============================================================+
        }

        //recap globa bagage

        public function reportbag($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
              $dt1 = $this->input->post('datedebutbg');
              $dt2 = $this->input->post('datefinbg');
              $lign = $this->input->post('axelignebg');
              $comp = $this->input->post('_compagbg');
              $gid = $this->input->post('departgarbg');
                $dats = explode("-", $dt1);
                $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                $dats1 = explode("-", $dt2);
                $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
              $reportick = $this->m_bagage->reportbag($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
              
              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('CBT_RAKIETA');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('L', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                          
              $titre = '<h1 align="center">RECAP GLOBAL BAGAGES DU '. $days.' AU '.$days1.'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead> 
                      <tr> 
                          <th width="20%" align="center"><strong>LIGNE</strong></th>
                          <th width="15%" align="center"><strong>NBR_BAGAGES</strong></th> 
                          <th width="20%" align="center"><strong>PRIX_UNITAIRE</strong></th>
                          <th width="20%" align="center"><strong>PRIX_TOTAL</strong></th>
                        </tr>
                    </thead>
                    <tbody>';
                    $etatglobale = 0;
                    $nb = 0;
                    $bg = 0;
                foreach ($reportick as $departick => $lement) {
                    $them .= '<tr>
                        <td width="20%" align="left"><strong>' . $lement->nom_ligne . '</strong></td>
                        <td width="15%" align="center"><strong>' . $lement->codid_bagage . '</strong></td>
                        <td width="20%" align="center"><strong>' . number_format($lement->prix_bagage, 0, '', ' ') . '</strong></td>
                        <td width="20%" align="right"><strong>' . number_format(($lement->codid_bagage * $lement->prix_bagage), 0, '', ' ') . '</strong></td>
                        </tr>';
                         $etatglobale += $lement->codid_bagage * $lement->prix_bagage;
                         $nb += $lement->codid_bagage;
                         $bg += $lement->prix_bagage;
                }

                    $them .= '<tr>
                          <td width="20%" align="left"><strong>TOTAL</strong></td>
                          <td width="15%" align="center"><strong> '.$nb.'</strong></td>
                          <td width="20%" align="center"><strong></strong></td>
                          <td width="20%" align="right"><strong> '.number_format($etatglobale, 0, '', ' ').' </strong></td>
                          
                     </tr>';
                    
                $them .= ' </tbody></table>';
                $them.= '<h2>SOMME:'. number_format($etatglobale, 0, '', ' ') .' </h2>';
                 
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_013.pdf' . '', 'I');
              //============================================================+
              // END OF FILE
              //============================================================+
        }

        public function reportbagesc($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
              $dt1 = $this->input->post('datedebutbgesc');
              $dt2 = $this->input->post('datefinbgesc');
              $lign = $this->input->post('axelignebgesc');
              $comp = $this->input->post('_compagbgesc');
              $gid = $this->input->post('departgarbgesc');
                $dats = explode("-", $dt1);
                $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                $dats1 = explode("-", $dt2);
                $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
              $reportick = $this->m_bagageesc->reportbag($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $lign);
              
              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('CBT_RAKIETA');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('L', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                          
              $titre = '<h1 align="center">RECAP GLOBAL BAGAGES ESCAL DU '. $days.' AU '.$days1.'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead> 
                      <tr> 
                          <th width="20%" align="center"><strong>LIGNE</strong></th>
                          <th width="15%" align="center"><strong>NBR_BAGAGES</strong></th> 
                          <th width="20%" align="center"><strong>PRIX_UNITAIRE</strong></th>
                          <th width="20%" align="center"><strong>PRIX_TOTAL</strong></th>
                        </tr>
                    </thead>
                    <tbody>';
                    $etatglobale = 0;
                    $nb = 0;
                    $bg = 0;
                foreach ($reportick as $departick => $lement) {
                    $them .= '<tr>
                        <td width="20%" align="left"><strong>' . $lement->nom_ligne . '</strong></td>
                        <td width="15%" align="center"><strong>' . $lement->codid_bagageesc . '</strong></td>
                        <td width="20%" align="center"><strong>' . number_format($lement->prix_bagageesc, 0, '', ' ') . '</strong></td>
                        <td width="20%" align="right"><strong>' . number_format(($lement->codid_bagageesc * $lement->prix_bagageesc), 0, '', ' ') . '</strong></td>
                        </tr>';
                         $etatglobale += $lement->codid_bagageesc * $lement->prix_bagageesc;
                         $nb += $lement->codid_bagageesc;
                         $bg += $lement->prix_bagageesc;
                }

                    $them .= '<tr>
                          <td width="20%" align="left"><strong>TOTAL</strong></td>
                          <td width="15%" align="center"><strong> '.$nb.'</strong></td>
                          <td width="20%" align="center"><strong></strong></td>
                          <td width="20%" align="right"><strong> '.number_format($etatglobale, 0, '', ' ').' </strong></td>
                          
                     </tr>';
                    
                $them .= ' </tbody></table>';
                $them.= '<h2>SOMME:'. number_format($etatglobale, 0, '', ' ') .' </h2>';
                 
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_013.pdf' . '', 'I');
              //============================================================+
              // END OF FILE
              //============================================================+
        }

        public function reportbaggl($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
              $dt1 = $this->input->post('datedebutbagopgl');
              $dt2 = $this->input->post('datefinbagopgl');
              $lign = $this->input->post('axelignebagopgl');
              $comp = $this->input->post('_compagbagopgl');
              $gid = $this->input->post('departgarbagopgl');
              $cais = $this->input->post('vendeuseidopgl');
              $ncomp = $this->m_compagnies->getn($comp);

              $uc = $this->m_utilisateur->u($cais);
              if($uc == NULL){
                $us = '';
              }else{
                $us = $uc->first_name.' '.$uc->last_name;
              }
                $dats = explode("-", $dt1);
                $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                $dats1 = explode("-", $dt2);
                $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
              
              $reportick = $this->m_bagage->reportbaggl($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $cais, $lign);
              
              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('CBT_RAKIETA');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('L', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                          
              $titre = '<h1 align="center">ETAT GLOBAL BAGAGES '.$us.' DU '. $days.' AU '.$days1.'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead> 
                      <tr> 
                          <th width="20%" align="center"><strong>LIGNE</strong></th>
                          <th width="15%" align="center"><strong>NBR_BAGAGES</strong></th> 
                          <th width="20%" align="center"><strong>PRIX_UNITAIRE</strong></th>
                          <th width="20%" align="center"><strong>PRIX_TOTAL</strong></th>
                        </tr>
                    </thead>
                    <tbody>';
                    $etatglobale = 0;
                    $nb = 0;
                    $bg = 0;
                foreach ($reportick as $departick => $lement) {
                    $them .= '<tr>
                        <td width="20%" align="left"><strong>' . $lement->nom_ligne . '</strong></td>
                        <td width="15%" align="center"><strong>' . $lement->codid_bagage . '</strong></td>
                        <td width="20%" align="center"><strong>' . number_format($lement->prix_bagage, 0, '', ' ') . '</strong></td>
                        <td width="20%" align="right"><strong>' . number_format(($lement->codid_bagage * $lement->prix_bagage), 0, '', ' ') . '</strong></td>
                        </tr>';
                         $etatglobale += $lement->codid_bagage * $lement->prix_bagage;
                         $nb += $lement->codid_bagage;
                         $bg += $lement->prix_bagage;
                }

                    $them .= '<tr>
                          <td width="20%" align="left"><strong>TOTAL</strong></td>
                          <td width="15%" align="center"><strong> '.$nb.'</strong></td>
                          <td width="20%" align="center"><strong></strong></td>
                          <td width="20%" align="right"><strong> '.number_format($etatglobale, 0, '', ' ').' </strong></td>
                          
                     </tr>';
                    
                $them .= ' </tbody></table>';
                $them.= '<h2>SOMME:'. number_format($etatglobale, 0, '', ' ') .' </h2>';
                 
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_013.pdf' . '', 'I');
              //============================================================+
              // END OF FILE
              //============================================================+
        }
        
        public function reportbagglesc($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
              $dt1 = $this->input->post('datedebutbagopglesc');
              $dt2 = $this->input->post('datefinbagopglesc');
              $lign = $this->input->post('axelignebagopglesc');
              $comp = $this->input->post('_compagbagopglesc');
              $gid = $this->input->post('departgarbagopglesc');
              $cais = $this->input->post('vendeuseidopglesc');
              $ncomp = $this->m_compagnies->getn($comp);

              $uc = $this->m_utilisateur->u($cais);
              if($uc == NULL){
                $us = '';
              }else
              {
                $us = $uc->first_name.' '.$uc->last_name;
              }
                $dats = explode("-", $dt1);
                $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                $dats1 = explode("-", $dt2);
                $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
              
              $reportickesc = $this->m_bagageesc->reportbaggl($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $cais, $lign);
              
              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('CBT_RAKIETA');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('L', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                          
              $titre = '<h1 align="center">ETAT GLOBAL BAGAGES ESCAL '.$us.' DU '. $days.' AU '.$days1.'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead> 
                      <tr> 
                          <th width="20%" align="center"><strong>LIGNE</strong></th>
                          <th width="15%" align="center"><strong>NBR_BAGAGES</strong></th> 
                          <th width="20%" align="center"><strong>PRIX_UNITAIRE</strong></th>
                          <th width="20%" align="center"><strong>PRIX_TOTAL</strong></th>
                        </tr>
                    </thead>
                    <tbody>';
                    $etatglobaleesc = 0;
                    $nbesc = 0;
                    $bgesc = 0;
                foreach ($reportickesc as $departick => $lements) {
                    $them .= '<tr>
                        <td width="20%" align="left"><strong>' . $lements->nom_ligne . '</strong></td>
                        <td width="15%" align="center"><strong>' . $lements->codid_bagageesc . '</strong></td>
                        <td width="20%" align="center"><strong>' . number_format($lements->prix_bagageesc, 0, '', ' ') . '</strong></td>
                        <td width="20%" align="right"><strong>' . number_format(($lements->codid_bagageesc * $lements->prix_bagageesc), 0, '', ' ') . '</strong></td>
                        </tr>';
                         $etatglobaleesc += $lements->codid_bagageesc * $lements->prix_bagageesc;
                         $nbesc += $lements->codid_bagageesc;
                         $bgesc += $lements->prix_bagageesc;
                }

                    $them .= '<tr>
                          <td width="20%" align="left"><strong>TOTAL</strong></td>
                          <td width="15%" align="center"><strong> '.$nbesc.'</strong></td>
                          <td width="20%" align="center"><strong></strong></td>
                          <td width="20%" align="right"><strong> '.number_format($etatglobaleesc, 0, '', ' ').' </strong></td>
                          
                     </tr>';
                    
                $them .= ' </tbody></table>';
                $them.= '<h2>SOMME:'. number_format($etatglobaleesc, 0, '', ' ') .' </h2>';
                 
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_013.pdf' . '', 'I');
              //============================================================+
              // END OF FILE
              //============================================================+
        }

        public function exercices($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
              $dt1 = $this->input->post('datedebut');
              $dt2 = $this->input->post('datefin');
              $lign = $this->input->post('axeligne');
              $comp = $this->input->post('_compag');
              $gid = $this->input->post('departgar');
              $ncomp = $this->m_compagnies->getn($comp);

                $dats = explode("-", $dt1);
                $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                $dats1 = explode("-", $dt2);
                  $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
            if($this->session->agent->userole === '1' OR $this->session->agent->userole === '2')
            {

              if($comp == 5002){
                  $reportick = $this->m_passager->reporticket($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
                $reportickretors = $this->m_non_passager->reporticketretour($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);

              }
              else
              {
                  $reportick = $this->m_passager->reporticketcptadmin($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
                  $reportickretors = $this->m_non_passager->reporticketretourcptadmin($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
              }
              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('CBT_RAKIETA');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('L', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                          
              $titre = '<h1 align="center">RECAP EX MENSUEL TICKET '.$ncomp->nom_compagnie.' DU '. $days .' AU '.$days1.'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead> 
                      <tr> 
                        <th width="20%" align="center"><strong>LIGNE</strong></th>
                        <th width="15%" align="center"><strong>NBR_TICKETS</strong></th> 
                        <th width="20%" align="center"><strong>PRIX_UNITAIRE</strong></th>
                        <th width="20%" align="center"><strong>PRIX_TOTAL</strong></th>
                      </tr>
                  </thead>
                  <tbody>';
                  $etatglobale = 0;
                  $etaglobals = 0;
                  $nb = 0;
                  $nbrt = 0;
                  $p = 0;
                  $pr = 0;
              foreach ($reportick as $departick => $lement) {
                  $them .= '<tr>
                      <td width="20%" align="left"><strong>' . $lement->nom_ligne . '</strong></td>
                      <td width="15%" align="center"><strong>' . round($lement->codepassager) . '</strong></td>
                      <td width="20%" align="center"><strong>' . number_format($lement->prixvente, 0, '', ' ') . '</strong></td>
                      <td width="20%" align="right"><strong>' . number_format(round($lement->codepassager)*($lement->prixvente), 0, '', ' ') . '</strong></td>
                      </tr>';
                       $etatglobale += round($lement->codepassager)*($lement->prixvente);
                       $nb +=round($lement->codepassager);
                       $p += $lement->prixvente;
              }
              foreach ($reportickretors as $etatretour => $etatretou) {
                $aler2 = explode("-", $etatretou->nom_ligne);
                    $allerretour2 = $aler2[1]. '-' .$aler2[0];
                $them .= '<tr>
                    <td width="20%" align="left"><strong>' . $allerretour2 . '</strong></td>
                    <td width="15%" align="center"><strong>' . round($etatretou->code_non_pass) . '</strong></td>
                    <td width="20%" align="center"><strong>' . number_format($etatretou->prixretour, 0, '', ' ') . '</strong></td>
                    <td width="20%" align="right"><strong>' . number_format(round($etatretou->code_non_pass)*($etatretou->prixretour), 0, '', ' ') . '</strong></td>
                    </tr>';
                      $etaglobals += round($etatretou->code_non_pass)*($etatretou->prixretour);
                      $nbrt +=round($etatretou->code_non_pass);
                      $pr += $etatretou->prixretour;
                    }

                    $them .= '<tr>
                        <td width="20%" align="left"><strong>TOTAL</strong></td>
                        <td width="15%" align="center"><strong> '.($nb+$nbrt).'</strong></td>
                        <td width="20%" align="center"><strong></strong></td>
                        <td width="20%" align="right"><strong> '.number_format($etatglobale + $etaglobals, 0, '', ' ').'</strong></td>
                        
                   </tr>';
                  
              $them .= ' </tbody></table>';
              $them.= '<h2>SOMME:'. number_format($etatglobale + $etaglobals, 0, '', ' ') .' </h2>';
               
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_013.pdf' . '', 'I');
              //============================================================+
              // END OF FILE
              //============================================================+
            }else
            {

              if($comp == 5002){
                  $reportick = $this->m_passager->reporticket($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
                $reportickreour = $this->m_non_passager->reporticketretour($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);

              }
              else{


                  $reportick = $this->m_passager->reporticketcpt($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
                  $reportickretors = $this->m_non_passager->reporticketretourcpt($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
              }
              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('CBT_RAKIETA');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('L', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                          
              $titre = '<h1 align="center">RECAP EX MENSUEL TICKET '.$ncomp->nom_compagnie.' DU '. $days .' AU '.$days1.'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead> 
                      <tr> 
                        <th width="20%" align="center"><strong>LIGNE</strong></th>
                        <th width="15%" align="center"><strong>NBR_TICKETS</strong></th> 
                        <th width="20%" align="center"><strong>PRIX_UNITAIRE</strong></th>
                        <th width="20%" align="center"><strong>PRIX_TOTAL</strong></th>
                      </tr>
                  </thead>
                  <tbody>';
                  $etatglobale = 0;
                  $etaglobals = 0;
                  $nb = 0;
                  $nbrt = 0;
                  $p = 0;
                  $pr = 0;
              foreach ($reportick as $departick => $lement) {
                  $them .= '<tr>
                      <td width="20%" align="left"><strong>' . $lement->nom_ligne . '</strong></td>
                      <td width="15%" align="center"><strong>' . round($lement->codepassager) . '</strong></td>
                      <td width="20%" align="center"><strong>' . number_format($lement->prixvente, 0, '', ' ') . '</strong></td>
                      <td width="20%" align="right"><strong>' . number_format(round($lement->codepassager)*($lement->prixvente), 0, '', ' ') . '</strong></td>
                      </tr>';
                       $etatglobale += round($lement->codepassager)*($lement->prixvente);
                       $nb +=round($lement->codepassager);
                       $p += $lement->prixvente;
              }
              foreach ($reportickretors as $etatretour => $etatretou) {
                $aler2 = explode("-", $etatretou->nom_ligne);
                    $allerretour2 = $aler2[1]. '-' .$aler2[0];
                $them .= '<tr>
                    <td width="20%" align="left"><strong>' . $allerretour2 . '</strong></td>
                    <td width="15%" align="center"><strong>' . round($etatretou->code_non_pass) . '</strong></td>
                    <td width="20%" align="center"><strong>' . number_format($etatretou->prixretour, 0, '', ' ') . '</strong></td>
                    <td width="20%" align="right"><strong>' . number_format(round($etatretou->code_non_pass)*($etatretou->prixretour), 0, '', ' ') . '</strong></td>
                    </tr>';
                      $etaglobals += round($etatretou->code_non_pass)*($etatretou->prixretour);
                      $nbrt +=round($etatretou->code_non_pass);
                      $pr += $etatretou->prixretour;
                    }

                    $them .= '<tr>
                        <td width="20%" align="left"><strong>TOTAL</strong></td>
                        <td width="15%" align="center"><strong> '.($nb+$nbrt).'</strong></td>
                        <td width="20%" align="center"><strong></strong></td>
                        <td width="20%" align="right"><strong> '.number_format($etatglobale + $etaglobals, 0, '', ' ').'</strong></td>
                        
                   </tr>';
                  
              $them .= ' </tbody></table>';
              $them.= '<h2>SOMME:'. number_format($etatglobale + $etaglobals, 0, '', ' ') .' </h2>';
               
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_013.pdf' . '', 'I');
              //============================================================+
              // END OF FILE
              //============================================================+
            }
        }

        public function exerclarer($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
              $dt1 = $this->input->post('datedebutdc');
              $dt2 = $this->input->post('datefindc');
              $lign = $this->input->post('axelignedc');
              $comp = $this->input->post('_compagdc');
              $gid = $this->input->post('departgardc');

              $ngrd = $this->m_gare_depart->getno($gid);

              $ncomp = $this->m_compagnies->getn($comp);

                $dats = explode("-", $dt1);
                $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                $dats1 = explode("-", $dt2);
                $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];

              if($comp == 5002){
                  $reportick = $this->m_passager->reporticketgr($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
                  $reportickreour = $this->m_non_passager->reporticketretourgr($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);

              }
              else{
                  $reportick = $this->m_passager->reporticketcptgr($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
                  $reportickretors = $this->m_non_passager->reporticketretourcptgr($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
              }

              foreach ($reportick as $departick => $lement) {

                  //"UPDATE passager SET exop =1";
                  $exopassager = array(
                        'exop' => 1
                     );

                    $pa = $this->m_passager->update($lement->code_passager, $lement->code_ticket, $exopassager);
              }

              foreach ($reportickretors as $deparrtick => $rlement) {
                  
                  $exonpassager = array(
                        'exonp' => 1
                    );

                    $np = $this->m_non_passager->update($rlement->code_non_pass, $rlement->codeticket, $exonpassager);
              }
              
              $re = '';

              if ($pa != FALSE){
               $re = 'REUSSIE';
              }else{
               $re = 'NON REUSSIE';
              } 

              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('CBT_RAKIETA');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('L', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                          
              $titre = '<h1 align="center">DECLARATION DES TICKETS '.$ncomp->nom_compagnie.' '. $ngrd->garenom .' DU '. $days .' AU '.$days1.' '.$re.'</h1>';
               
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_016.pdf' . '', 'I');
              //============================================================+
              // END OF FILE
              //============================================================+
        } 
        //declarer
        public function exerdeclarer($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
              $dt1 = $this->input->post('datedebutd');
              $dt2 = $this->input->post('datefind');
              $lign = $this->input->post('axeligned');
              $comp = $this->input->post('_compagd');
              $gid = $this->input->post('departgard');

              $ngrd = $this->m_gare_depart->getno($gid);

              $ncomp = $this->m_compagnies->getn($comp);

                $dats = explode("-", $dt1);
                $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                $dats1 = explode("-", $dt2);
                  $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
            
              $reportick = $this->m_passager->reporticketcptd($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
              $reportickretors = $this->m_non_passager->reporticketretourcptd($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);

              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('CBT_RAKIETA');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('L', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                          
              $titre = '<h1 align="center">ETATS DES DECLARATION DES TICKETS '.$ncomp->nom_compagnie.' '. $ngrd->garenom .' DU '. $days .' AU '.$days1.'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead> 
                      <tr> 
                        <th width="20%" align="center"><strong>LIGNE</strong></th>
                        <th width="15%" align="center"><strong>NBR_TICKETS</strong></th> 
                        <th width="20%" align="center"><strong>PRIX_UNITAIRE</strong></th>
                        <th width="20%" align="center"><strong>PRIX_TOTAL</strong></th>
                      </tr>
                  </thead>
                  <tbody>';
                  $etatglobale = 0;
                  $etaglobals = 0;
                  $nb = 0;
                  $nbrt = 0;
                  $p = 0;
                  $pr = 0;
              foreach ($reportick as $departick => $lement) {
                  $them .= '<tr>
                      <td width="20%" align="left"><strong>' . $lement->nom_ligne . '</strong></td>
                      <td width="15%" align="center"><strong>' . round($lement->codepassager) . '</strong></td>
                      <td width="20%" align="center"><strong>' . number_format($lement->prixvente, 0, '', ' ') . '</strong></td>
                      <td width="20%" align="right"><strong>' . number_format(round($lement->codepassager)*($lement->prixvente), 0, '', ' ') . '</strong></td>
                      </tr>';
                       $etatglobale += round($lement->codepassager)*($lement->prixvente);
                       $nb +=round($lement->codepassager);
                       $p += $lement->prixvente;
              }
              foreach ($reportickretors as $etatretour => $etatretou) {
                $aler2 = explode("-", $etatretou->nom_ligne);
                    $allerretour2 = $aler2[1]. '-' .$aler2[0];
                $them .= '<tr>
                    <td width="20%" align="left"><strong>' . $allerretour2 . '</strong></td>
                    <td width="15%" align="center"><strong>' . round($etatretou->code_non_pass) . '</strong></td>
                    <td width="20%" align="center"><strong>' . number_format($etatretou->prixretour, 0, '', ' ') . '</strong></td>
                    <td width="20%" align="right"><strong>' . number_format(round($etatretou->code_non_pass)*($etatretou->prixretour), 0, '', ' ') . '</strong></td>
                    </tr>';
                      $etaglobals += round($etatretou->code_non_pass)*($etatretou->prixretour);
                      $nbrt +=round($etatretou->code_non_pass);
                      $pr += $etatretou->prixretour;
                    }

                    $them .= '<tr>
                        <td width="20%" align="left"><strong>TOTAL</strong></td>
                        <td width="15%" align="center"><strong> '.($nb+$nbrt).'</strong></td>
                        <td width="20%" align="center"><strong></strong></td>
                        <td width="20%" align="right"><strong> '.number_format($etatglobale + $etaglobals, 0, '', ' ').'</strong></td>
                        
                   </tr>';
                  
              $them .= ' </tbody></table>';
              $them.= '<h2>SOMME:'. number_format($etatglobale + $etaglobals, 0, '', ' ') .' </h2>';
               
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_013.pdf' . '', 'I');
              //============================================================+
              // END OF FILE
              //============================================================+
        }

         public function exerclarerbg($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
              $dt1 = $this->input->post('datedebutdcbg');
              $dt2 = $this->input->post('datefindcbg');
              $lign = $this->input->post('axelignedcbg');
              $comp = $this->input->post('_compagdcbg');
              $gid = $this->input->post('departgardcbg');

              $ncomp = $this->m_compagnies->getn($comp);

              $ngrd = $this->m_gare_depart->getno($gid);

                $dats = explode("-", $dt1); 
                $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                $dats1 = explode("-", $dt2);
                $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
            
              
                  $reportbag = $this->m_bagage->reportbagcptgr($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
             

              foreach ($reportbag as $departbag => $lement) {

                      $exobagas = array(
                        'exobg' => 1
                      );

                  $pab = $this->m_bagage->update($lement->id_bagage, $exobagas);
              }

              
              $rebg = '';

              if ($pab != FALSE){
               $rebg = 'REUSSIE';
              }else{
               $rebg = 'NON REUSSIE';
              } 

              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('CBT_RAKIETA');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('L', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                          
              $titre = '<h1 align="center">DECLARATION DES BAGAGES '.$ncomp->nom_compagnie.' '. $ngrd->garenom .' DU '. $days .' AU '.$days1.' '.$rebg.'</h1>';
               
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_016.pdf' . '', 'I');
              //============================================================+
              // END OF FILE
              //============================================================+
        }

        public function exerclarerbgesc($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
              $dt1 = $this->input->post('datedebutdcbgesc');
              $dt2 = $this->input->post('datefindcbgesc');
              $lign = $this->input->post('axelignedcbgesc');
              $comp = $this->input->post('_compagdcbgesc');
              $gid = $this->input->post('departgardcbgesc');

              $ncomp = $this->m_compagnies->getn($comp);

              $ngrd = $this->m_gare_depart->getno($gid);

                $dats = explode("-", $dt1);
                $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                $dats1 = explode("-", $dt2);
                $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
            
              
                  $reportbag = $this->m_bagageesc->reportbagcptgr($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $lign);
             

              foreach ($reportbag as $departbag => $lement) {

                      $exobagas = array(
                        'exobagesc' => 1
                      );

                  $pab = $this->m_bagageesc->update($lement->id_bagageesc, $exobagas);
              }

              
              $rebg = '';

              if ($pab != FALSE){
               $rebg = 'REUSSIE';
              }else{
               $rebg = 'NON REUSSIE';
              } 

              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('CBT_RAKIETA');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('L', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                          
              $titre = '<h1 align="center">DECLARATION DES BAGAGES ESCAL '.$ncomp->nom_compagnie.' '. $ngrd->garenom .' DU '. $days .' AU '.$days1.' '.$rebg.'</h1>';
               
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_016.pdf' . '', 'I');
              //============================================================+
              // END OF FILE
              //============================================================+
        }
        
        public function exerdeclarerbg($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
              $dt1 = $this->input->post('datedebutdbg');
              $dt2 = $this->input->post('datefindbg');
              $lign = $this->input->post('axelignedbg');
              $comp = $this->input->post('_compagdbg');
              $gid = $this->input->post('departgardbg');

              $ncomp = $this->m_compagnies->getn($comp);

              $ngrd = $this->m_gare_depart->getno($gid);

                $dats = explode("-", $dt1);
                $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                $dats1 = explode("-", $dt2);
                  $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
            
              $reportbaga = $this->m_bagage->reportbagcptd($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $lign);
              

              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('CBT_RAKIETA');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('L', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                          
              $titre = '<h1 align="center">ETATS DES DECLARATION BAGAGES '.$ncomp->nom_compagnie .' '. $ngrd->garenom .' DU '. $days .' AU '.$days1.'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead> 
                      <tr> 
                          <th width="20%" align="center"><strong>LIGNE</strong></th>
                          <th width="15%" align="center"><strong>NBR_BAGAGES</strong></th> 
                          <th width="20%" align="center"><strong>PRIX_UNITAIRE</strong></th>
                          <th width="20%" align="center"><strong>PRIX_TOTAL</strong></th>
                        </tr>
                    </thead>
                    <tbody>';
                    $etatglobale = 0;
                    $nb = 0;
                    $bg = 0;
                foreach ($reportbaga as $departbag => $lement) {
                    $them .= '<tr>
                        <td width="20%" align="left"><strong>' . $lement->nom_ligne . '</strong></td>
                        <td width="15%" align="center"><strong>' . $lement->codid_bagage . '</strong></td>
                        <td width="20%" align="center"><strong>' . number_format($lement->prix_bagage, 0, '', ' ') . '</strong></td>
                        <td width="20%" align="right"><strong>' . number_format(($lement->codid_bagage * $lement->prix_bagage), 0, '', ' ') . '</strong></td>
                        </tr>';
                         $etatglobale += $lement->codid_bagage * $lement->prix_bagage;
                         $nb += $lement->codid_bagage;
                         $bg += $lement->prix_bagage;
                }

                    $them .= '<tr>
                          <td width="20%" align="left"><strong>TOTAL</strong></td>
                          <td width="15%" align="center"><strong> '.$nb.'</strong></td>
                          <td width="20%" align="center"><strong></strong></td>
                          <td width="20%" align="right"><strong> '.number_format($etatglobale, 0, '', ' ').'</strong></td>
                          
                     </tr>';
                    
                $them .= ' </tbody></table>';
                $them.= '<h2>SOMME:'. number_format($etatglobale, 0, '', ' ') .' </h2>';
               
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_013.pdf' . '', 'I');
              //============================================================+
              // END OF FILE
              //============================================================+

              //var_dump( $reportbaga, $this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $lign, $ngrd->garenom);
        }

        public function exerdeclarerbgesc($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
              $dt1 = $this->input->post('datedebutdbgesc');
              $dt2 = $this->input->post('datefindbgesc');
              $lign = $this->input->post('axelignedbgesc');
              $comp = $this->input->post('_compagdbgesc');
              $gid = $this->input->post('departgardbgesc');

              $ncomp = $this->m_compagnies->getn($comp);

                $ngrd = $this->m_gare_depart->getno($gid);

                $dats = explode("-", $dt1);
                $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                $dats1 = explode("-", $dt2);
                  $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
            
              $reportbaga = $this->m_bagageesc->reportbagcptd($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $lign);
              

              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('CBT_RAKIETA');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('L', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                          
              $titre = '<h1 align="center">ETATS DES DECLARATION BAGAGES ESCAL '.$ncomp->nom_compagnie.' '. $ngrd->garenom .' DU '. $days .' AU '.$days1.'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead> 
                      <tr> 
                          <th width="20%" align="center"><strong>LIGNE</strong></th>
                          <th width="15%" align="center"><strong>NBR_BAGAGES</strong></th> 
                          <th width="20%" align="center"><strong>PRIX_UNITAIRE</strong></th>
                          <th width="20%" align="center"><strong>PRIX_TOTAL</strong></th>
                        </tr>
                    </thead>
                    <tbody>';
                    $etatglobale = 0;
                    $nb = 0;
                    $bg = 0;
                foreach ($reportbaga as $departbag => $lement) {
                    $them .= '<tr>
                        <td width="20%" align="left"><strong>' . $lement->nom_ligne . '</strong></td>
                        <td width="15%" align="center"><strong>' . $lement->codid_bagageesc . '</strong></td>
                        <td width="20%" align="center"><strong>' . number_format($lement->prix_bagageesc, 0, '', ' ') . '</strong></td>
                        <td width="20%" align="right"><strong>' . number_format(($lement->codid_bagageesc * $lement->prix_bagageesc), 0, '', ' ') . '</strong></td>
                        </tr>';
                         $etatglobale += $lement->codid_bagageesc * $lement->prix_bagageesc;
                         $nb += $lement->codid_bagageesc;
                         $bg += $lement->prix_bagageesc;
                }

                    $them .= '<tr>
                          <td width="20%" align="left"><strong>TOTAL</strong></td>
                          <td width="15%" align="center"><strong> '.$nb.'</strong></td>
                          <td width="20%" align="center"><strong></strong></td>
                          <td width="20%" align="right"><strong> '.number_format($etatglobale, 0, '', ' ').'</strong></td>
                          
                     </tr>';
                    
                $them .= ' </tbody></table>';
                $them.= '<h2>SOMME:'. number_format($etatglobale, 0, '', ' ') .' </h2>';
               
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_013.pdf' . '', 'I');
              //============================================================+
              // END OF FILE
              //============================================================+
        }

        //courr

        public function exoclarercourrier($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
              $dt1 = $this->input->post('datedebutcrcl');
              $dt2 = $this->input->post('datefincrcl');
              $lign = $this->input->post('axelignecrcl');
              $comp = $this->input->post('_compagcrcl');
              $gid = $this->input->post('departgarcrcl');
              $tyc = $this->input->post('typcourscl');

              $ngrd = $this->m_gare_depart->getno($gid);

              $ncomp = $this->m_compagnies->getn($comp);

              $ty = 'PLIS';
                $ty2 = 'COLIS';

                if($tyc === 'Gros_plis'){
                  $ty3 = $ty2;
                }elseif($tyc === 'Petit_plis'){
                  $ty3 = $ty;
                }elseif($tyc === ''){
                  $ty3 = $ty.'/'.$ty2;
                }

                $dats = explode("-", $dt1);
                $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                $dats1 = explode("-", $dt2);
                  $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
            
                  $recapcourrier = $this->m_courrier_expedier->recaptexopligr($this->entreprise->ekey, $dt1, $dt2, $gid, $comp, $tyc, $lign);
                  foreach ($recapcourrier as $departcr => $lement) {

                          $exocours = array(
                            'exocr' => 1
                          );

                      $pacr = $this->m_courrier_expedier->update($lement->courrierexpid, $lement->num_cour, $lement->departcolis, $exocours);
                  }

                  
                  $recr = '';

                  if ($pacr != FALSE){
                   $recr = 'REUSSIE';
                  }else{
                   $recr = 'NON REUSSIE';
                  } 


              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('CBT_RAKIETA');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('L', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                          
              $titre = '<h1 align="center">DECLARATION  '.$ncomp->nom_compagnie.' '. $ngrd->garenom.' '.$ty3.' DU '. $days .' AU '.$days1.' '.$recr.'</h1>';
              
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_016.pdf' . '', 'I');
              //============================================================+
              // END OF FILE
              //============================================================+
            
        }

        public function exoclarercourrieresc($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
              $dt1 = $this->input->post('datedebutcrclesc');
              $dt2 = $this->input->post('datefincrclesc');
              $lign = $this->input->post('axelignecrclesc');
              $comp = $this->input->post('_compagcrclesc');
              $gid = $this->input->post('departgarcrclesc');
              $tyc = $this->input->post('typcoursclesc');

              $ngrd = $this->m_gare_depart->getno($gid);

              $ncomp = $this->m_compagnies->getn($comp);

              $ty = 'PLIS';
                $ty2 = 'COLIS';

                if($tyc === 'Gros_plis'){
                  $ty3 = $ty2;
                }elseif($tyc === 'Petit_plis'){
                  $ty3 = $ty;
                }elseif($tyc === ''){
                  $ty3 = $ty.'/'.$ty2;
                }

                $dats = explode("-", $dt1);
                $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                $dats1 = explode("-", $dt2);
                  $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
            
                  $recapcourrier = $this->m_courrier_expedieresc->recaptexopligr($this->entreprise->ekey, $dt1, $dt2, $gid, $comp, $tyc, $lign);
                  foreach ($recapcourrier as $departcr => $lement) {

                          $exocours = array(
                            'exocresc' => 1
                          );

                      $pacr = $this->m_courrier_expedieresc->update($lement->courrierexpidesc, $lement->num_couresc, $lement->departcolisesc, $exocours);
                  }

                  
                  $recr = '';

                  if ($pacr != FALSE){
                   $recr = 'REUSSIE';
                  }else{
                   $recr = 'NON REUSSIE';
                  } 


              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('CBT_RAKIETA');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('L', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                          
              $titre = '<h1 align="center">DECLARATION ESCAL '.$ncomp->nom_compagnie.' '. $ngrd->garenom.' '.$ty3.' DU '. $days .' AU '.$days1.' '.$recr.'</h1>';
              
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_016.pdf' . '', 'I');
              //============================================================+
              // END OF FILE
              //============================================================+
            
        }
        public function exodeclarercourrier($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
              $dt1 = $this->input->post('datedebutcrcld');
              $dt2 = $this->input->post('datefincrcld');
              $lign = $this->input->post('axelignecrcld');
              $comp = $this->input->post('_compagcrcld');
              $gid = $this->input->post('departgarcrcld');
              $tyc = $this->input->post('typcourscld');

                $ngrd = $this->m_gare_depart->getno($gid);
              $ncomp = $this->m_compagnies->getn($comp);

              $ty = 'PLIS';
                $ty2 = 'COLIS';

                if($tyc === 'Gros_plis'){
                  $ty3 = $ty2;
                }elseif($tyc === 'Petit_plis'){
                  $ty3 = $ty;
                }elseif($tyc === ''){
                  $ty3 = $ty.'/'.$ty2;
                }

                $dats = explode("-", $dt1);
                $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                $dats1 = explode("-", $dt2);
                  $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
            
                  $recapcourrier = $this->m_courrier_expedier->recaptexoplid($this->entreprise->ekey, $dt1, $dt2, $gid, $comp, $tyc, $lign);
                
              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('CBT_RAKIETA');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('L', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                          
              $titre = '<h1 align="center">ETATS DES DECLARATION  '.$ncomp->nom_compagnie.' '. $ngrd->garenom .' '.$ty3.' DU '. $days .' AU '.$days1.'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead>                          
                        <tr> 
                          <th width="45%" align="left"><strong>LIGNE</strong></th>
                          
                          <th width="15%" align="center"><strong>NOMBRE</strong></th>
                          <th width="20%" align="center"><strong>PRIX</strong></th>
                          <th width="20%" align="right"><strong>MONTANT</strong></th> 
                          </tr>
                    </thead>
                    <tbody>';
                    $tglobal = 0;
                    $nb = 0;
                    $tglobal1 = 0;
                foreach ($recapcourrier as $departs => $element) {
                    $them .= '<tr>
                    <td width="45%" align="left"><strong>' . $element->nom_ligne . '</strong></td>
                         
                          <td width="15%" align="center"><strong>' . $element->nombres. '</strong></td>
                          <td width="20%" align="center"><strong>' . number_format($element->prixcolis, 0, '', ' ') . '</strong></td>
                          <td width="20%" align="right"><strong>' . number_format($element->nombres * $element->prixcolis, 0, '', ' ') . '</strong></td>
                          </tr>';
                           $tglobal += ($element->nombres) * ($element->prixcolis);
                          $tglobal1 = $tglobal;
                           $nb +=$element->nombres;
                        }
          
                $them .= '<tr>
                            <td width="45%" align="center"><strong>TOTAL</strong></td>
                            <td width="15%" align="center"><strong> '.($nb).'</strong></td>
                            <td width="20%" align="center"><strong></strong></td>
                            <td width="20%" align="right"><strong> '.number_format($tglobal1, 0, '', ' ').'</strong></td>
                            
                       </tr>';

                $them .= ' </tbody></table>';
                $them.= '<h2>SOMME:'. number_format($tglobal1, 0, '', ' ') .' </h2>';

               
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_013.pdf' . '', 'I');
              //============================================================+
              // END OF FILE
              //============================================================+
            
        }
        public function exodeclarercourrieresc($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
              $dt1 = $this->input->post('datedebutcrcldesc');
              $dt2 = $this->input->post('datefincrcldesc');
              $lign = $this->input->post('axelignecrcldesc');
              $comp = $this->input->post('_compagcrcldesc');
              $gid = $this->input->post('departgarcrcldesc');
              $tyc = $this->input->post('typcourscldesc');

                $ngrd = $this->m_gare_depart->getno($gid);
              $ncomp = $this->m_compagnies->getn($comp);

              $ty = 'PLIS';
                $ty2 = 'COLIS';

                if($tyc === 'Gros_plis'){
                  $ty3 = $ty2;
                }elseif($tyc === 'Petit_plis'){
                  $ty3 = $ty;
                }elseif($tyc === ''){
                  $ty3 = $ty.'/'.$ty2;
                }

                $dats = explode("-", $dt1);
                $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                $dats1 = explode("-", $dt2);
                  $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
            
                  $recapcourrier = $this->m_courrier_expedieresc->recaptexoplid($this->entreprise->ekey, $dt1, $dt2, $gid, $comp, $tyc, $lign);
                
              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('CBT_RAKIETA');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('L', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                          
              $titre = '<h1 align="center">ETATS DES DECLARATION ESCAL '.$ncomp->nom_compagnie.' '. $ngrd->garenom .' '.$ty3.' DU '. $days .' AU '.$days1.'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead>                          
                        <tr> 
                          <th width="45%" align="left"><strong>LIGNE</strong></th>
                          
                          <th width="15%" align="center"><strong>NOMBRE</strong></th>
                          <th width="20%" align="center"><strong>PRIX</strong></th>
                          <th width="20%" align="right"><strong>MONTANT</strong></th> 
                          </tr>
                    </thead>
                    <tbody>';
                    $tglobal = 0;
                    $nb = 0;
                    $tglobal1 = 0;
                foreach ($recapcourrier as $departs => $element) {
                    $them .= '<tr>
                    <td width="45%" align="left"><strong>' . $element->nom_ligne . '</strong></td>
                         
                          <td width="15%" align="center"><strong>' . $element->nombresesc. '</strong></td>
                          <td width="20%" align="center"><strong>' . number_format($element->prixcolisesc, 0, '', ' ') . '</strong></td>
                          <td width="20%" align="right"><strong>' . number_format($element->nombresesc * $element->prixcolisesc, 0, '', ' ') . '</strong></td>
                          </tr>';
                           $tglobal += ($element->nombresesc) * ($element->prixcolisesc);
                          $tglobal1 = $tglobal;
                           $nb +=$element->nombresesc;
                        }
          
                $them .= '<tr>
                            <td width="45%" align="center"><strong>TOTAL</strong></td>
                            <td width="15%" align="center"><strong> '.($nb).'</strong></td>
                            <td width="20%" align="center"><strong></strong></td>
                            <td width="20%" align="right"><strong> '.number_format($tglobal1, 0, '', ' ').'</strong></td>
                            
                       </tr>';

                $them .= ' </tbody></table>';
                $them.= '<h2>SOMME:'. number_format($tglobal1, 0, '', ' ') .' </h2>';

               
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_013.pdf' . '', 'I');
              //============================================================+
              // END OF FILE
              //============================================================+
            
        }
        //exo escal
        public function exerciceses($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
              $dt1 = $this->input->post('datedebutes');
              $dt2 = $this->input->post('datefines');
              $lign = $this->input->post('axelignees');
              $comp = $this->input->post('_compages');
              $gid = $this->input->post('departgares');
              $ncomp = $this->m_compagnies->getn($comp);

                $dats = explode("-", $dt1);
                $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                $dats1 = explode("-", $dt2);
                  $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
            
                 $reportick = $this->m_escalclients->reporticketcpt($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
              
              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('CBT_RAKIETA');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('L', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                          
              $titre = '<h1 align="center">RECAP EX MENSUEL TICKET ESCAL '.$ncomp->nom_compagnie.' DU '. $days .' AU '.$days1.'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead> 
                      <tr> 
                        <th width="20%" align="center"><strong>LIGNE</strong></th>
                        <th width="15%" align="center"><strong>NBR_TICKETS</strong></th> 
                        <th width="20%" align="center"><strong>PRIX_UNITAIRE</strong></th>
                        <th width="20%" align="center"><strong>PRIX_TOTAL</strong></th>
                      </tr>
                  </thead>
                  <tbody>';
                  $etatglobale = 0;
                  $nb = 0;
                  $p = 0;
                  foreach ($reportick as $departick => $lement) {
                      $them .= '<tr>
                          <td width="20%" align="left"><strong>' . $lement->nom_ligne . '</strong></td>
                          <td width="15%" align="center"><strong>' . round($lement->escalp) . '</strong></td>
                          <td width="20%" align="center"><strong>' . number_format($lement->prixescal, 0, '', ' ') . '</strong></td>
                          <td width="20%" align="right"><strong>' . number_format(($lement->escalp)*($lement->prixescal), 0, '', ' ') . '</strong></td>
                          </tr>';
                           $etatglobale += ($lement->escalp)*($lement->prixescal);
                           $nb +=$lement->escalp;
                           $p += $lement->prixescal;
                  }
              
                    $them .= '<tr>
                        <td width="20%" align="left"><strong>TOTAL</strong></td>
                        <td width="15%" align="center"><strong> '.($nb).'</strong></td>
                        <td width="20%" align="center"><strong></strong></td>
                        <td width="20%" align="right"><strong> '.number_format($etatglobale, 0, '', ' ').'</strong></td>
                        
                   </tr>';
                  
              $them .= ' </tbody></table>';
              $them.= '<h2>SOMME:'. number_format($etatglobale, 0, '', ' ') .' </h2>';
               
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_013.pdf' . '', 'I');
              //============================================================+
              // END OF FILE
              //============================================================+
        }

        public function exerclareres($ckey, $g)
        {
              $this->entreprise = $this->m_entreprises->get_key($ckey);
              $dt1 = $this->input->post('datedebutdces');
              $dt2 = $this->input->post('datefindces');
              $lign = $this->input->post('axelignedces');
              $comp = $this->input->post('_compagdces');
              $gid = $this->input->post('departgardces');

              $ncomp = $this->m_compagnies->getn($comp);

              $ngrd = $this->m_gare_depart->getno($gid);

                $dats = explode("-", $dt1);
                $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                $dats1 = explode("-", $dt2);
                $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
            
                $reportick = $this->m_escalclients->reporticketcptgr($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);

              foreach ($reportick as $departick => $lement) {

                  $exopassageres = array(
                        'exopes' => 1
                     );

                    $paes = $this->m_escalclients->update($lement->idclescal, $exopassageres);
              }

              
              $rees = '';

              if ($paes != FALSE){
               $rees = 'REUSSIE';
              }else{
               $rees = 'NON REUSSIE';
              } 

              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('CBT_RAKIETA');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('L', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                          
              $titre = '<h1 align="center">DECLARATION DES TICKETS ESCAL '.$ncomp->nom_compagnie.' '. $ngrd->garenom .' DU '. $days .' AU '.$days1.' '.$rees.'</h1>';
               
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_016.pdf' . '', 'I');
           
              //============================================================+
              // END OF FILE
              //============================================================+
        } 
        //declarer
        public function exerdeclareres($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
              $dt1 = $this->input->post('datedebutdes');
              $dt2 = $this->input->post('datefindes');
              $lign = $this->input->post('axelignedes');
              $comp = $this->input->post('_compagdes');
              $gid = $this->input->post('departgardes');


                $ngrd = $this->m_gare_depart->getno($gid);

              $ncomp = $this->m_compagnies->getn($comp);

                $dats = explode("-", $dt1);
                $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                $dats1 = explode("-", $dt2);
                  $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
            
              $reportick = $this->m_escalclients->reporticketcptd($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $lign);

              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('CBT_RAKIETA');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('L', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                          
              $titre = '<h1 align="center">ETATS DES DECLARATION DES TICKETS ESCAL '.$ncomp->nom_compagnie.' '. $ngrd->garenom .' DU '. $days .' AU '.$days1.'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead> 
                      <tr> 
                        <th width="20%" align="center"><strong>LIGNE</strong></th>
                        <th width="15%" align="center"><strong>NBR_TICKETS</strong></th> 
                        <th width="20%" align="center"><strong>PRIX_UNITAIRE</strong></th>
                        <th width="20%" align="center"><strong>PRIX_TOTAL</strong></th>
                      </tr>
                  </thead>
                  <tbody>';
                  $etatglobale = 0;
                  $nb = 0;
                  $p = 0;
                    foreach ($reportick as $departick => $lement) {
                      $them .= '<tr>
                          <td width="20%" align="left"><strong>' . $lement->nom_ligne . '</strong></td>
                          <td width="15%" align="center"><strong>' . round($lement->escalp) . '</strong></td>
                          <td width="20%" align="center"><strong>' . number_format($lement->prixescal, 0, '', ' ') . '</strong></td>
                          <td width="20%" align="right"><strong>' . number_format(($lement->escalp)*($lement->prixescal), 0, '', ' ') . '</strong></td>
                          </tr>';
                           $etatglobale += ($lement->escalp)*($lement->prixescal);
                           $nb +=$lement->escalp;
                           $p += $lement->prixescal;
                    }
              
                    $them .= '<tr>
                        <td width="20%" align="left"><strong>TOTAL</strong></td>
                        <td width="15%" align="center"><strong> '.($nb).'</strong></td>
                        <td width="20%" align="center"><strong></strong></td>
                        <td width="20%" align="right"><strong> '.number_format($etatglobale, 0, '', ' ').'</strong></td>
                        
                   </tr>';
              $them .= ' </tbody></table>';
              $them.= '<h2>SOMME:'. number_format($etatglobale, 0, '', ' ') .' </h2>';
               
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_013.pdf' . '', 'I');
              //============================================================+
              // END OF FILE
              //============================================================+

              //var_dump($reportick, $this->entreprise->ekey, $gid, $dt1, $dt2, $comp);
        }
        /*public function manifest($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
              $dt1 = $this->input->post('datedebut');
              //$dt2 = $this->input->post('datefin');
              $lign = $this->input->post('axeligne');
              $comp = $this->input->post('_compag');
              $gid = $this->input->post('departgar');
                $dats = explode("-", $dt1);
                $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                //$dats1 = explode("-", $dt2);
                  //$days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
            if($this->session->agent->userole === '1' OR $this->session->agent->userole === '2')
            {

              if($comp == 5002){
                  $reportick = $this->m_passager->nifestad($this->entreprise->ekey, $gid, $dt1, $dt1, $comp, $lign);
                $reportickretors = $this->m_non_passager->reporticketretour($this->entreprise->ekey, $gid, $dt1, $dt1, $comp, $lign);

              }
              else
              {
                  $reportick = $this->m_passager->nifestcptadmin($this->entreprise->ekey, $gid, $dt1, $dt1, $comp, $lign);
                  $reportickretors = $this->m_non_passager->reporticketretourcptadmin($this->entreprise->ekey, $gid, $dt1, $dt1, $comp, $lign);
              }
              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('CBT_RAKIETA');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('L', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                          
              $titre = '<h1 align="center">MANIFEST JOURNALIER DU '. $days .'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead> 
                      <tr>
                        <th width="10%" align="center"><strong>HEURE</strong></th> 
                        <th width="20%" align="center"><strong>LIGNE</strong></th>
                        <th width="15%" align="center"><strong>NBR_TICKETS</strong></th> 
                        <th width="20%" align="center"><strong>PRIX_UNITAIRE</strong></th>
                        <th width="20%" align="center"><strong>PRIX_TOTAL</strong></th>
                      </tr>
                  </thead>
                  <tbody>';
                  $etatglobale = 0;
                  $etaglobals = 0;
                  $nb = 0;
                  $nbrt = 0;
                  $p = 0;
                  $pr = 0;
              foreach ($reportick as $departick => $lement) {
                   $g = explode(":", $lement->heure);
                     $them .= '<tr>
                      <td width="10%" align="center"><strong>' .sprintf("%02d:%02d", $g[0], $g[1]). '</strong></td>
                      <td width="20%" align="left"><strong>' . $lement->nom_ligne . '</strong></td>
                      <td width="15%" align="center"><strong>' . round($lement->codepassager) . '</strong></td>
                      <td width="20%" align="center"><strong>' . number_format($lement->prixvente, 0, '', ' ') . '</strong></td>
                      <td width="20%" align="right"><strong>' . number_format(round($lement->codepassager)*($lement->prixvente), 0, '', ' ') . '</strong></td>
                      </tr>';
                       $etatglobale += round($lement->codepassager)*($lement->prixvente);
                       $nb +=round($lement->codepassager);
                       $p += $lement->prixvente;
              }
              foreach ($reportickretors as $etatretour => $etatretou) {
                $aler2 = explode("-", $etatretou->nom_ligne);
                    $allerretour2 = $aler2[1]. '-' .$aler2[0];
                $them .= '<tr>
                    <td width="30%" align="left"><strong>' . $allerretour2 . '</strong></td>
                    <td width="15%" align="center"><strong>' . round($etatretou->code_non_pass) . '</strong></td>
                    <td width="20%" align="center"><strong>' . number_format($etatretou->prixretour, 0, '', ' ') . '</strong></td>
                    <td width="20%" align="right"><strong>' . number_format(round($etatretou->code_non_pass)*($etatretou->prixretour), 0, '', ' ') . '</strong></td>
                    </tr>';
                      $etaglobals += round($etatretou->code_non_pass)*($etatretou->prixretour);
                      $nbrt +=round($etatretou->code_non_pass);
                      $pr += $etatretou->prixretour;
                    }

                    $them .= '<tr>
                        <td width="30%" align="left"><strong>TOTAL</strong></td>
                        <td width="15%" align="center"><strong> '.($nb+$nbrt).'</strong></td>
                        <td width="20%" align="center"><strong></strong></td>
                        <td width="20%" align="right"><strong> '.number_format($etatglobale + $etaglobals, 0, '', ' ').'</strong></td>
                        
                   </tr>';
                  
              $them .= ' </tbody></table>';
              $them.= '<h2>SOMME:'. number_format($etatglobale + $etaglobals, 0, '', ' ') .' </h2>';
               
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_013.pdf' . '', 'I');
              //============================================================+
              // END OF FILE
              //============================================================+
            }else
            {

              if($comp == 5002){
                  $reportick = $this->m_passager->nifestad($this->entreprise->ekey, $gid, $dt1, $dt1, $comp, $lign);
                $reportickreour = $this->m_non_passager->reporticketretour($this->entreprise->ekey, $gid, $dt1, $dt1, $comp, $lign);

              }
              else{


                  $reportick = $this->m_passager->nifest($this->entreprise->ekey, $gid, $dt1, $dt1, $comp, $lign);
                  $reportickretors = $this->m_non_passager->reporticketretourcpt($this->entreprise->ekey, $gid, $dt1, $dt1, $comp, $lign);
              }
              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('CBT_RAKIETA');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('L', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                          
              $titre = '<h1 align="center">MANIFEST JOURNALIER DU '. $days .'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead> 
                      <tr>

                        <th width="10%" align="center"><strong>HEURE</strong></th>
                        <th width="20%" align="center"><strong>LIGNE</strong></th>
                        <th width="15%" align="center"><strong>NBR_TICKETS</strong></th> 
                        <th width="20%" align="center"><strong>PRIX_UNITAIRE</strong></th>
                        <th width="20%" align="center"><strong>PRIX_TOTAL</strong></th>
                      </tr>
                  </thead>
                  <tbody>';
                  $etatglobale = 0;
                  $etaglobals = 0;
                  $nb = 0;
                  $nbrt = 0;
                  $p = 0;
                  $pr = 0;
              foreach ($reportick as $departick => $lement) {
                $g = explode(":", $lement->heure);
                     $them .= '<tr>
                      <td width="10%" align="center"><strong>' .sprintf("%02d:%02d", $g[0], $g[1]). '</strong></td>
                      <td width="20%" align="left"><strong>' . $lement->nom_ligne . '</strong></td>
                      <td width="15%" align="center"><strong>' . round($lement->codepassager) . '</strong></td>
                      <td width="20%" align="center"><strong>' . number_format($lement->prixvente, 0, '', ' ') . '</strong></td>
                      <td width="20%" align="right"><strong>' . number_format(round($lement->codepassager)*($lement->prixvente), 0, '', ' ') . '</strong></td>
                      </tr>';
                       $etatglobale += round($lement->codepassager)*($lement->prixvente);
                       $nb +=round($lement->codepassager);
                       $p += $lement->prixvente;
              }
              foreach ($reportickretors as $etatretour => $etatretou) {
                $aler2 = explode("-", $etatretou->nom_ligne);
                    $allerretour2 = $aler2[1]. '-' .$aler2[0];
                $them .= '<tr>
                    <td width="30%" align="left"><strong>' . $allerretour2 . '</strong></td>
                    <td width="15%" align="center"><strong>' . round($etatretou->code_non_pass) . '</strong></td>
                    <td width="20%" align="center"><strong>' . number_format($etatretou->prixretour, 0, '', ' ') . '</strong></td>
                    <td width="20%" align="right"><strong>' . number_format(round($etatretou->code_non_pass)*($etatretou->prixretour), 0, '', ' ') . '</strong></td>
                    </tr>';
                      $etaglobals += round($etatretou->code_non_pass)*($etatretou->prixretour);
                      $nbrt +=round($etatretou->code_non_pass);
                      $pr += $etatretou->prixretour;
                    }

                    $them .= '<tr>
                        <td width="30%" align="left"><strong>TOTAL</strong></td>
                        <td width="15%" align="center"><strong> '.($nb+$nbrt).'</strong></td>
                        <td width="20%" align="center"><strong></strong></td>
                        <td width="20%" align="right"><strong> '.number_format($etatglobale + $etaglobals, 0, '', ' ').'</strong></td>
                        
                   </tr>';
                  
              $them .= ' </tbody></table>';
              $them.= '<h2>SOMME:'. number_format($etatglobale + $etaglobals, 0, '', ' ') .' </h2>';
               
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_013.pdf' . '', 'I');
              //============================================================+
              // END OF FILE
              //============================================================+
            }
        }*/

        public function manifesthebdo($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
              $dt1 = $this->input->post('datedebut');
              $dt2 = $this->input->post('datefin');
              $lign = $this->input->post('axeligne');
              $comp = $this->input->post('_compag');
              $gid = $this->input->post('departgar');
              $ncomp = $this->m_compagnies->getn($comp);

              //$key1 = mdate($dt1, now());
            

              /*if($dt1 === $dt2)
              {
                $njr = 'JOURNALIER';

              }elseif($dt1 < $dt2)
              {
                $njr = 'HEBDOMADAIRE';
              }
              else
              {
                $njr = 'MENSUEL';
              }*/

                $dats = explode("-", $dt1);
                $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                $dats1 = explode("-", $dt2);
                  $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
            if($this->session->agent->userole === '1' OR $this->session->agent->userole === '2')
            {

              if($comp == 5002){
                  $reportick = $this->m_passager->nifesthebad($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
                $reportickretors = $this->m_non_passager->reporticketretour($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);

              }
              else
              {
                  $reportick = $this->m_passager->nifesthebcptadmin($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
                  $reportickretors = $this->m_non_passager->reporticketretourcptadmin($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
              }
              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('CBT_RAKIETA');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('L', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                          
              $titre = '<h1 align="center">MANIFEST TICKET '.$ncomp->nom_compagnie.' DU '. $days .' AU '.$days1.'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead> 
                      <tr>
                      <th width="10%" align="center"><strong>DATE</strong></th>
                         
                        <th width="20%" align="center"><strong>LIGNE</strong></th>
                        <th width="15%" align="center"><strong>NBR_TICKETS</strong></th> 
                        <th width="20%" align="center"><strong>PRIX_UNITAIRE</strong></th>
                        <th width="20%" align="center"><strong>PRIX_TOTAL</strong></th>
                      </tr>
                  </thead>
                  <tbody>';
                  $etatglobale = 0;
                  $etaglobals = 0;
                  $nb = 0;
                  $nbrt = 0;
                  $p = 0;
                  $pr = 0;
              foreach ($reportick as $departick => $lement) {

                  $datsar1 = explode("-", $lement->datep_create);

                  $daysar1 = $datsar1[2]. '-'. $datsar1[1]. '-' .$datsar1[0];

                   $g = explode(":", $lement->heure);
                     $them .= '<tr>
                      <td width="10%" align="left"><strong>' .$daysar1. '</strong></td>                     
                      
                      <td width="20%" align="left"><strong>' . $lement->nom_ligne . '</strong></td>
                      <td width="15%" align="center"><strong>' . round($lement->codepassager) . '</strong></td>
                      <td width="20%" align="center"><strong>' . number_format($lement->prixvente, 0, '', ' ') . '</strong></td>
                      <td width="20%" align="right"><strong>' . number_format(round($lement->codepassager)*($lement->prixvente), 0, '', ' ') . '</strong></td>
                      </tr>';
                       $etatglobale += round($lement->codepassager)*($lement->prixvente);
                       $nb +=round($lement->codepassager);
                       $p += $lement->prixvente;
              }
              foreach ($reportickretors as $etatretour => $etatretou) {
                $aler2 = explode("-", $etatretou->nom_ligne);
                    $allerretour2 = $aler2[1]. '-' .$aler2[0];
                $them .= '<tr>
                    <td width="30%" align="left"><strong>' . $allerretour2 . '</strong></td>
                    <td width="15%" align="center"><strong>' . round($etatretou->code_non_pass) . '</strong></td>
                    <td width="20%" align="center"><strong>' . number_format($etatretou->prixretour, 0, '', ' ') . '</strong></td>
                    <td width="20%" align="right"><strong>' . number_format(round($etatretou->code_non_pass)*($etatretou->prixretour), 0, '', ' ') . '</strong></td>
                    </tr>';
                      $etaglobals += round($etatretou->code_non_pass)*($etatretou->prixretour);
                      $nbrt +=round($etatretou->code_non_pass);
                      $pr += $etatretou->prixretour;
                    }

                    $them .= '<tr>
                        <td width="30%" align="left"><strong>TOTAL</strong></td>
                        <td width="15%" align="center"><strong> '.($nb+$nbrt).'</strong></td>
                        <td width="20%" align="center"><strong></strong></td>
                        <td width="20%" align="right"><strong> '.number_format($etatglobale + $etaglobals, 0, '', ' ').'</strong></td>
                        
                   </tr>';
                  
              $them .= ' </tbody></table>';
              $them.= '<h2>SOMME:'. number_format($etatglobale + $etaglobals, 0, '', ' ') .' </h2>';
               
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_013.pdf' . '', 'D');
              //============================================================+
              // END OF FILE
              //============================================================+
            }else
            {

              if($comp == 5002){
                  $reportick = $this->m_passager->nifesthebad($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
                $reportickreour = $this->m_non_passager->reporticketretour($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);

              }
              else{


                  $reportick = $this->m_passager->nifestheb($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
                  $reportickretors = $this->m_non_passager->reporticketretourcpt($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
              }
              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('CBT_RAKIETA');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('L', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                          
              $titre = '<h1 align="center">MANIFEST TICKET '.$ncomp->nom_compagnie.' DU '. $days .' AU '.$days1.'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead> 
                      <tr>

                        <th width="10%" align="center"><strong>DATE</strong></th>
                        <th width="20%" align="center"><strong>LIGNE</strong></th>
                        <th width="15%" align="center"><strong>NBR_TICKETS</strong></th> 
                        <th width="20%" align="center"><strong>PRIX_UNITAIRE</strong></th>
                        <th width="20%" align="center"><strong>PRIX_TOTAL</strong></th>
                      </tr>
                  </thead>
                  <tbody>';
                  $etatglobale = 0;
                  $etaglobals = 0;
                  $nb = 0;
                  $nbrt = 0;
                  $p = 0;
                  $pr = 0;
              foreach ($reportick as $departick => $lement) {
                $datsar1 = explode("-", $lement->datep_create);

                  $daysar1 = $datsar1[2]. '-'. $datsar1[1]. '-' .$datsar1[0];

                  $g = explode(":", $lement->heure);
                     $them .= '<tr>
                      <td width="10%" align="center"><strong>' .$daysar1. '</strong></td>
                      <td width="20%" align="left"><strong>' . $lement->nom_ligne . '</strong></td>
                      <td width="15%" align="center"><strong>' . round($lement->codepassager) . '</strong></td>
                      <td width="20%" align="center"><strong>' . number_format($lement->prixvente, 0, '', ' ') . '</strong></td>
                      <td width="20%" align="right"><strong>' . number_format(round($lement->codepassager)*($lement->prixvente), 0, '', ' ') . '</strong></td>
                      </tr>';
                       $etatglobale += round($lement->codepassager)*($lement->prixvente);
                       $nb +=round($lement->codepassager);
                       $p += $lement->prixvente;
              }
              foreach ($reportickretors as $etatretour => $etatretou) {
                $aler2 = explode("-", $etatretou->nom_ligne);
                    $allerretour2 = $aler2[1]. '-' .$aler2[0];
                $them .= '<tr>
                    <td width="30%" align="left"><strong>' . $allerretour2 . '</strong></td>
                    <td width="15%" align="center"><strong>' . round($etatretou->code_non_pass) . '</strong></td>
                    <td width="20%" align="center"><strong>' . number_format($etatretou->prixretour, 0, '', ' ') . '</strong></td>
                    <td width="20%" align="right"><strong>' . number_format(round($etatretou->code_non_pass)*($etatretou->prixretour), 0, '', ' ') . '</strong></td>
                    </tr>';
                      $etaglobals += round($etatretou->code_non_pass)*($etatretou->prixretour);
                      $nbrt +=round($etatretou->code_non_pass);
                      $pr += $etatretou->prixretour;
                    }

                    $them .= '<tr>
                        <td width="30%" align="left"><strong>TOTAL</strong></td>
                        <td width="15%" align="center"><strong> '.($nb+$nbrt).'</strong></td>
                        <td width="20%" align="center"><strong></strong></td>
                        <td width="20%" align="right"><strong> '.number_format($etatglobale + $etaglobals, 0, '', ' ').'</strong></td>
                        
                   </tr>';
                  
              $them .= ' </tbody></table>';
              $them.= '<h2>SOMME:'. number_format($etatglobale + $etaglobals, 0, '', ' ') .' </h2>';
               
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_013.pdf' . '', 'D');
              //============================================================+
              // END OF FILE
              //============================================================+
            }
        }

        public function manifesthebdoesc($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
              $dt1 = $this->input->post('datedebutesc');
              $dt2 = $this->input->post('datefinesc');
              $lign = $this->input->post('axeligneesc');
              $comp = $this->input->post('_compagesc');
              $gid = $this->input->post('departgaresc');
              $ncomp = $this->m_compagnies->getn($comp);

              

                $dats = explode("-", $dt1);
                $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                $dats1 = explode("-", $dt2);
                  $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
            
                $reportick = $this->m_escalclients->nifestheb($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $lign);
                 
              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('CBT_RAKIETA');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('L', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                          
              $titre = '<h1 align="center">MANIFEST TICKET ESCAL '.$ncomp->nom_compagnie.' DU '. $days .' AU '.$days1.'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead> 
                      <tr>

                        <th width="10%" align="center"><strong>DATE</strong></th>
                        <th width="20%" align="center"><strong>LIGNE</strong></th>
                        <th width="15%" align="center"><strong>NBR_TICKETS</strong></th> 
                        <th width="20%" align="center"><strong>PRIX_UNITAIRE</strong></th>
                        <th width="20%" align="center"><strong>PRIX_TOTAL</strong></th>
                      </tr>
                  </thead>
                  <tbody>';
                  $etatglobale = 0;
                  $etaglobals = 0;
                  $nb = 0;
                  $nbrt = 0;
                  $p = 0;
                  $pr = 0;
              foreach ($reportick as $departick => $lement) {
                $datsar1 = explode("-", $lement->datedepescal);

                  $daysar1 = $datsar1[2]. '-'. $datsar1[1]. '-' .$datsar1[0];

                     $them .= '<tr>
                      <td width="10%" align="center"><strong>' .$daysar1. '</strong></td>
                      <td width="20%" align="left"><strong>' . $lement->nom_ligne . '</strong></td>
                      <td width="15%" align="center"><strong>' . round($lement->escalp) . '</strong></td>
                      <td width="20%" align="center"><strong>' . number_format($lement->prixescal, 0, '', ' ') . '</strong></td>
                      <td width="20%" align="right"><strong>' . number_format(round($lement->escalp)*($lement->prixescal), 0, '', ' ') . '</strong></td>
                      </tr>';
                       $etatglobale += round($lement->escalp)*($lement->prixescal);
                       $nb +=round($lement->escalp);
                       $p += $lement->prixescal;
              }

                    $them .= '<tr>
                        <td width="30%" align="left"><strong>TOTAL</strong></td>
                        <td width="15%" align="center"><strong> '.$nb.'</strong></td>
                        <td width="20%" align="center"><strong></strong></td>
                        <td width="20%" align="right"><strong> '.number_format($etatglobale, 0, '', ' ').'</strong></td>
                        
                   </tr>';
                  
              $them .= ' </tbody></table>';
              $them.= '<h2>SOMME:'. number_format($etatglobale, 0, '', ' ') .' </h2>';
               
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_013.pdf' . '', 'D');
              //============================================================+
              // END OF FILE
              //============================================================+
            
        }
        //recapt courrier
        public function exocourrier($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
              $dt1 = $this->input->post('datedebutcr');
              $dt2 = $this->input->post('datefincr');
              $lign = $this->input->post('axelignecr');
              $comp = $this->input->post('_compagcr');
              $gid = $this->input->post('departgarcr');
              $tyc = $this->input->post('typcours');

              $ncomp = $this->m_compagnies->getn($comp);

              $ty = 'PLIS';
                $ty2 = 'COLIS';

                if($tyc === 'Gros_plis'){
                  $ty3 = $ty2;
                }elseif($tyc === 'Petit_plis'){
                  $ty3 = $ty;
                }elseif($tyc === ''){
                  $ty3 = $ty.'/'.$ty2;
                }

                $dats = explode("-", $dt1);
                $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                $dats1 = explode("-", $dt2);
                  $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
            
                  $recapcourrier = $this->m_courrier_expedier->recaptexopli($this->entreprise->ekey, $dt1, $dt2, $gid, $comp, $tyc, $lign);
                
              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('CBT_RAKIETA');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('L', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                          
              $titre = '<h1 align="center">RECAP EX MENSUEL  '.$ncomp->nom_compagnie.' '.$ty3.' DU '. $days .' AU '.$days1.'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead>                          
                        <tr> 
                          <th width="45%" align="left"><strong>LIGNE</strong></th>
                          
                          <th width="15%" align="center"><strong>NOMBRE</strong></th>
                          <th width="20%" align="center"><strong>PRIX</strong></th>
                          <th width="20%" align="right"><strong>MONTANT</strong></th> 
                          </tr>
                    </thead>
                    <tbody>';
                    $tglobal = 0;
                    $nb = 0;
                foreach ($recapcourrier as $departs => $element) {
                    $them .= '<tr>
                    <td width="45%" align="left"><strong>' . $element->nom_ligne . '</strong></td>
                         
                          <td width="15%" align="center"><strong>' . $element->nombres. '</strong></td>
                          <td width="20%" align="center"><strong>' . number_format($element->prixcolis, 0, '', ' ') . '</strong></td>
                          <td width="20%" align="right"><strong>' . number_format($element->nombres * $element->prixcolis, 0, '', ' ') . '</strong></td>
                          </tr>';
                           $tglobal += ($element->nombres) * ($element->prixcolis);
                          $tglobal1 = $tglobal;
                           $nb +=$element->nombres;
                        }
          
                $them .= '<tr>
                            <td width="45%" align="center"><strong>TOTAL</strong></td>
                            <td width="15%" align="center"><strong> '.($nb).'</strong></td>
                            <td width="20%" align="center"><strong></strong></td>
                            <td width="20%" align="right"><strong> '.number_format($tglobal1, 0, '', ' ').'</strong></td>
                            
                       </tr>';

                $them .= ' </tbody></table>';
                $them.= '<h2>SOMME:'. number_format($tglobal1, 0, '', ' ') .' </h2>';

               
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_013.pdf' . '', 'I');
              //============================================================+
              // END OF FILE
              //============================================================+
            
        }


        public function exocourrieresc($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
              $dt1 = $this->input->post('datedebutcresc');
              $dt2 = $this->input->post('datefincresc');
              $lign = $this->input->post('axelignecresc');
              $comp = $this->input->post('_compagcresc');
              $gid = $this->input->post('departgarcresc');
              $tyc = $this->input->post('typcoursesc');

              $ncomp = $this->m_compagnies->getn($comp);

              $ty = 'PLIS';
                $ty2 = 'COLIS';

                if($tyc === 'Gros_plis'){
                  $ty3 = $ty2;
                }elseif($tyc === 'Petit_plis'){
                  $ty3 = $ty;
                }elseif($tyc === ''){
                  $ty3 = $ty.'/'.$ty2;
                }

                $dats = explode("-", $dt1);
                $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                $dats1 = explode("-", $dt2);
                  $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
            
                  $recapcourrier = $this->m_courrier_expedieresc->recaptexopli($this->entreprise->ekey, $dt1, $dt2, $gid, $comp, $tyc, $lign);
                
              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('CBT_RAKIETA');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('L', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                          
              $titre = '<h1 align="center">RECAP EX MENSUEL ESCAL '.$ncomp->nom_compagnie.' '.$ty3.' DU '. $days .' AU '.$days1.'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead>                          
                        <tr> 
                          <th width="45%" align="left"><strong>LIGNE</strong></th>
                          
                          <th width="15%" align="center"><strong>NOMBRE</strong></th>
                          <th width="20%" align="center"><strong>PRIX</strong></th>
                          <th width="20%" align="right"><strong>MONTANT</strong></th> 
                          </tr>
                    </thead>
                    <tbody>';
                    $tglobal = 0;
                    $nb = 0;
                foreach ($recapcourrier as $departs => $element) {
                    $them .= '<tr>
                    <td width="45%" align="left"><strong>' . $element->nom_ligne . '</strong></td>
                         
                          <td width="15%" align="center"><strong>' . $element->nombresesc. '</strong></td>
                          <td width="20%" align="center"><strong>' . number_format($element->prixcolisesc, 0, '', ' ') . '</strong></td>
                          <td width="20%" align="right"><strong>' . number_format($element->nombresesc * $element->prixcolisesc, 0, '', ' ') . '</strong></td>
                          </tr>';
                           $tglobal += ($element->nombresesc) * ($element->prixcolisesc);
                          $tglobal1 = $tglobal;
                           $nb +=$element->nombresesc;
                        }
          
                $them .= '<tr>
                            <td width="45%" align="center"><strong>TOTAL</strong></td>
                            <td width="15%" align="center"><strong> '.($nb).'</strong></td>
                            <td width="20%" align="center"><strong></strong></td>
                            <td width="20%" align="right"><strong> '.number_format($tglobal1, 0, '', ' ').'</strong></td>
                            
                       </tr>';

                $them .= ' </tbody></table>';
                $them.= '<h2>SOMME:'. number_format($tglobal1, 0, '', ' ') .' </h2>';

               
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_014.pdf' . '', 'I');
              //============================================================+
              // END OF FILE
              //============================================================+
            
        }

        public function courriermanifestheb($ckey, $g)
        {
          $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = $this->input->post('datedebutheb');
            $dt2 = $this->input->post('datefinheb');
            $lign = $this->input->post('axeligneheb');
            $comp = $this->input->post('_compagheb');
            $gid = $this->input->post('departgarheb');
            $tyc = $this->input->post('typcoursheb');
            $ncomp = $this->m_compagnies->getn($comp);
              $ty = 'PLIS';
                $ty2 = 'COLIS';

                if($tyc === 'Gros_plis'){
                  $ty3 = $ty2;
                }elseif($tyc === 'Petit_plis'){
                  $ty3 = $ty;
                }elseif($tyc === ''){
                  $ty3 = $ty.'/'.$ty2;
                }

                $dats = explode("-", $dt1);
                $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                $dats1 = explode("-", $dt2);
                $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
            
                  $recapcourrierjr = $this->m_courrier_expedier->recaptexopliheb($this->entreprise->ekey, $dt1, $dt2, $gid, $comp, $tyc, $lign);
                
              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('CBT_RAKIETA');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('L', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                          
              $titre = '<h1 align="center">MANIFEST '.$ncomp->nom_compagnie.' '.$ty3.' DU '. $days .' AU '.$days1.'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead>                          
                        <tr> 
                          <th width="10%" align="center"><strong>DATE</strong></th>
                          
                          <th width="25%" align="left"><strong>LIGNE</strong></th>
                          <th width="15%" align="center"><strong>NOMBRE</strong></th>
                          <th width="20%" align="center"><strong>PRIX</strong></th>
                          <th width="20%" align="right"><strong>MONTANT</strong></th> 
                          </tr>
                    </thead>
                    <tbody>';
                    $tglobal = 0;
                    $nb = 0;
                foreach ($recapcourrierjr as $departs => $element) {
                    $g = explode(":", $element->heure);

                    $datss = explode("-", $element->dateenvoi);
                    $dayss = $datss[2]. '-'. $datss[1]. '-' .$datss[0];
                     $them .= '<tr>
                     <td width="10%" align="left"><strong>' .$dayss. '</strong></td>  
                        
                         <td width="25%" align="left"><strong>' . $element->nom_ligne . '</strong></td>
                          <td width="15%" align="center"><strong>' . $element->nombres. '</strong></td>
                          <td width="20%" align="center"><strong>' . number_format($element->prixcolis, 0, '', ' ') . '</strong></td>
                          <td width="20%" align="right"><strong>' . number_format($element->nombres * $element->prixcolis, 0, '', ' ') . '</strong></td>
                          </tr>';
                           $tglobal += ($element->nombres) * ($element->prixcolis);
                          $tglobal1 = $tglobal;
                           $nb +=$element->nombres;
                        }
          
                $them .= '<tr>
                            <td width="35%" align="center"><strong>TOTAL</strong></td>
                            <td width="15%" align="center"><strong> '.($nb).'</strong></td>
                            <td width="20%" align="center"><strong></strong></td>
                            <td width="20%" align="right"><strong> '.number_format($tglobal1, 0, '', ' ').'</strong></td>
                            
                       </tr>';

                $them .= ' </tbody></table>';
                $them.= '<h2>SOMME:'. number_format($tglobal1, 0, '', ' ') .' </h2>';
               
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_017.pdf' . '', 'D');
              //============================================================+
              // END OF FILE
              //============================================================+    
        }

        public function courriermanifesthebesc($ckey, $g)
        {
          $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = $this->input->post('datedebuthebesc');
            $dt2 = $this->input->post('datefinhebesc');
            $lign = $this->input->post('axelignehebesc');
            $comp = $this->input->post('_compaghebesc');
            $gid = $this->input->post('departgarhebesc');
            $tyc = $this->input->post('typcourshebesc');
            $ncomp = $this->m_compagnies->getn($comp);
              $ty = 'PLIS';
                $ty2 = 'COLIS';

                if($tyc === 'Gros_plis'){
                  $ty3 = $ty2;
                }elseif($tyc === 'Petit_plis'){
                  $ty3 = $ty;
                }elseif($tyc === ''){
                  $ty3 = $ty.'/'.$ty2;
                }

                $dats = explode("-", $dt1);
                $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                $dats1 = explode("-", $dt2);
                $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
            
                  $recapcourrierjr = $this->m_courrier_expedieresc->recaptexopliheb($this->entreprise->ekey, $dt1, $dt2, $gid, $comp, $tyc, $lign);
                
              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('CBT_RAKIETA');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('L', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                          
              $titre = '<h1 align="center">MANIFEST ESCAL  '.$ncomp->nom_compagnie.' '.$ty3.' DU '. $days .' AU '.$days1.'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead>                          
                        <tr> 
                          <th width="10%" align="center"><strong>DATE</strong></th>
                          
                          <th width="25%" align="left"><strong>LIGNE</strong></th>
                          <th width="15%" align="center"><strong>NOMBRE</strong></th>
                          <th width="20%" align="center"><strong>PRIX</strong></th>
                          <th width="20%" align="right"><strong>MONTANT</strong></th> 
                          </tr>
                    </thead>
                    <tbody>';
                    $tglobal = 0;
                    $nb = 0;
                foreach ($recapcourrierjr as $departs => $element) {
                    $g = explode(":", $element->heure);

                    $datss = explode("-", $element->dateenvoiesc);
                    $dayss = $datss[2]. '-'. $datss[1]. '-' .$datss[0];
                     $them .= '<tr>
                     <td width="10%" align="left"><strong>' .$dayss. '</strong></td>  
                        
                         <td width="25%" align="left"><strong>' . $element->nom_ligne . '</strong></td>
                          <td width="15%" align="center"><strong>' . $element->nombresesc. '</strong></td>
                          <td width="20%" align="center"><strong>' . number_format($element->prixcolisesc, 0, '', ' ') . '</strong></td>
                          <td width="20%" align="right"><strong>' . number_format($element->nombresesc * $element->prixcolisesc, 0, '', ' ') . '</strong></td>
                          </tr>';
                           $tglobal += ($element->nombresesc) * ($element->prixcolisesc);
                          $tglobal1 = $tglobal;
                           $nb +=$element->nombresesc;
                        }
          
                $them .= '<tr>
                            <td width="35%" align="center"><strong>TOTAL</strong></td>
                            <td width="15%" align="center"><strong> '.($nb).'</strong></td>
                            <td width="20%" align="center"><strong></strong></td>
                            <td width="20%" align="right"><strong> '.number_format($tglobal1, 0, '', ' ').'</strong></td>
                            
                       </tr>';

                $them .= ' </tbody></table>';
                $them.= '<h2>SOMME:'. number_format($tglobal1, 0, '', ' ') .' </h2>';
               
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_017.pdf' . '', 'D');
              //============================================================+
              // END OF FILE
              //============================================================+    
        }

        public function bagagemanifestheb($ckey, $g)
        {
          $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = $this->input->post('datedebuthebbg');
            $dt2 = $this->input->post('datefinhebbg');
            $lign = $this->input->post('axelignehebbg');
            $comp = $this->input->post('_compaghebbg');
            $gid = $this->input->post('departgarhebbg');
            $ncomp = $this->m_compagnies->getn($comp);
              

                $dats = explode("-", $dt1);
                $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                $dats1 = explode("-", $dt2);
                $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
            
                  $recapbgjr = $this->m_bagage->recaptexobgheb($this->entreprise->ekey, $dt1, $dt2, $gid, $comp, $lign);
                
              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('CBT_RAKIETA');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('L', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                          
              $titre = '<h1 align="center">MANIFEST BAGAGES '.$ncomp->nom_compagnie.' DU '. $days .' AU '.$days1.'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead>                          
                        <tr> 
                          <th width="10%" align="center"><strong>DATE</strong></th>
                          
                          <th width="25%" align="left"><strong>LIGNE</strong></th>
                          <th width="15%" align="center"><strong>NOMBRE</strong></th>
                          <th width="20%" align="center"><strong>PRIX</strong></th>
                          <th width="20%" align="right"><strong>MONTANT</strong></th> 
                          </tr>
                    </thead>
                    <tbody>';
                    $tglobal = 0;
                    $nb = 0;
                foreach ($recapbgjr as $departs => $element) {
                    
                    //$g = explode(":", $element->heure);

                    $datss = explode("-", $element->date_create);
                    $dayss = $datss[2]. '-'. $datss[1]. '-' .$datss[0];
                     $them .= '<tr>
                     <td width="10%" align="left"><strong>' .$dayss. '</strong></td>  
                        
                         <td width="25%" align="left"><strong>' . $element->nom_ligne . '</strong></td>
                          <td width="15%" align="center"><strong>' . $element->codid_bagage. '</strong></td>
                          <td width="20%" align="center"><strong>' . number_format($element->prix_bagage, 0, '', ' ') . '</strong></td>
                          <td width="20%" align="right"><strong>' . number_format($element->codid_bagage * $element->prix_bagage, 0, '', ' ') . '</strong></td>
                          </tr>';
                           $tglobal += ($element->codid_bagage) * ($element->prix_bagage);
                          $tglobal1 = $tglobal;
                           $nb +=$element->codid_bagage;
                        }
          
                $them .= '<tr>
                            <td width="35%" align="center"><strong>TOTAL</strong></td>
                            <td width="15%" align="center"><strong> '.($nb).'</strong></td>
                            <td width="20%" align="center"><strong></strong></td>
                            <td width="20%" align="right"><strong> '.number_format($tglobal1, 0, '', ' ').'</strong></td>
                            
                       </tr>';

                $them .= ' </tbody></table>';
                $them.= '<h2>SOMME:'. number_format($tglobal1, 0, '', ' ') .' </h2>';
               
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('bagage.pdf' . '', 'D');
              //============================================================+
              // END OF FILE
              //============================================================+    
        }

        public function bagageescmanifestheb($ckey, $g)
        {
          $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = $this->input->post('datedebuthebbge');
            $dt2 = $this->input->post('datefinhebbge');
            $lign = $this->input->post('axelignehebbge');
            $comp = $this->input->post('_compaghebbge');
            $gid = $this->input->post('departgarhebbge');
            $ncomp = $this->m_compagnies->getn($comp);
              

                $dats = explode("-", $dt1);
                $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                $dats1 = explode("-", $dt2);
                $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
            
                  $recapbgescjr = $this->m_bagageesc->recaptexobgescheb($this->entreprise->ekey, $dt1, $dt2, $gid, $comp, $lign);
                
              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('CBT_RAKIETA');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('L', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                          
              $titre = '<h1 align="center">MANIFEST BAGAGESESCAL '.$ncomp->nom_compagnie.' DU '. $days .' AU '.$days1.'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead>                          
                        <tr> 
                          <th width="10%" align="center"><strong>DATE</strong></th>
                          
                          <th width="25%" align="left"><strong>LIGNE</strong></th>
                          <th width="15%" align="center"><strong>NOMBRE</strong></th>
                          <th width="20%" align="center"><strong>PRIX</strong></th>
                          <th width="20%" align="right"><strong>MONTANT</strong></th> 
                          </tr>
                    </thead>
                    <tbody>';
                    $tglobal = 0;
                    $nb = 0;
                foreach ($recapbgescjr as $departs => $element) {
                    
                    //$g = explode(":", $element->heure);

                    $datss = explode("-", $element->date_createesc);
                    $dayss = $datss[2]. '-'. $datss[1]. '-' .$datss[0];
                     $them .= '<tr>
                     <td width="10%" align="left"><strong>' .$dayss. '</strong></td>  
                        
                         <td width="25%" align="left"><strong>' . $element->nom_ligne . '</strong></td>
                          <td width="15%" align="center"><strong>' . $element->codid_bagageesc. '</strong></td>
                          <td width="20%" align="center"><strong>' . number_format($element->prix_bagageesc, 0, '', ' ') . '</strong></td>
                          <td width="20%" align="right"><strong>' . number_format($element->codid_bagageesc * $element->prix_bagageesc, 0, '', ' ') . '</strong></td>
                          </tr>';
                           $tglobal += ($element->codid_bagageesc) * ($element->prix_bagageesc);
                          $tglobal1 = $tglobal;
                           $nb +=$element->codid_bagageesc;
                        }
          
                $them .= '<tr>
                            <td width="35%" align="center"><strong>TOTAL</strong></td>
                            <td width="15%" align="center"><strong> '.($nb).'</strong></td>
                            <td width="20%" align="center"><strong></strong></td>
                            <td width="20%" align="right"><strong> '.number_format($tglobal1, 0, '', ' ').'</strong></td>
                            
                       </tr>';

                $them .= ' </tbody></table>';
                $them.= '<h2>SOMME:'. number_format($tglobal1, 0, '', ' ') .' </h2>';
               
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('bagageesc.pdf' . '', 'D');
              //============================================================+
              // END OF FILE
              //============================================================+    
        }
        
        public function exoreports($ckey, $g)
        {
          $this->entreprise = $this->m_entreprises->get_key($ckey);

              $dt1 = $this->input->post('datedebut');
              $dt2 = $this->input->post('datefin');
              $cais = $this->input->post('caissier');
              $lign = $this->input->post('axeligne');
              $comp = $this->input->post('_compag');
              $gid = $this->input->post('departgar');
              $uc = $this->m_utilisateur->u($cais);

              $ncomp = $this->m_compagnies->getn($comp);

              if($uc == NULL){

                $us = '';

              }else{
                $us = $uc->first_name.' '.$uc->last_name;
              }
              $dats = explode("-", $dt1);
              $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
              $dats1 = explode("-", $dt2);
              $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
            
            if($this->session->agent->userole === '1' OR $this->session->agent->userole === '2')
            {
                
                if($comp == 5002){

                    $onreport = $this->m_passager->listereport($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $cais, $lign);
                    $retourreport = $this->m_non_passager->listereportretour($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $cais, $lign);

                }
                else
                {
                    $onreport = $this->m_passager->listereportcptadmin($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $cais, $lign);
                    $retourreport = $this->m_non_passager->listereportretourcptadmin($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $cais, $lign);
                }

                  $etatglobale = 0;
                  $etaglobals = 0;
                  $nb = 0;
                  $nbrt = 0;
                  $p = 0;
                  $pr = 0;

                // Préparer le nom du fichier CSV
                $filename = 'EXERCICE MENSUEL TICKET GUICHETIER '.$us.'_' . $ncomp->nom_compagnie . '_' . $days . '_AU_' . $days1 . '.csv';

                header('Content-Type: text/csv; charset=utf-8');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                $output = fopen('php://output', 'w');
                fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM 
                // Avant le foreach, juste après fopen()
                  stream_filter_prepend($output, 'convert.iconv.UTF-8/UTF-8');

                // Forcer le séparateur ;
                $delimiter = ';';

                // En-têtes
                $header = ['LIGNE', 'NBR_TICKETS', 'PRIX_UNITAIRE', 'PRIX_TOTAL'];
                fputcsv($output, $header, $delimiter);
                foreach ($onreport as $pm) {
                  // PASSAGERS ALLER
                  $etatglobale += $pm->total;
                      $nb +=round($pm->codepassager);
                      $p += $pm->prixvente;
                      
                  $row = [
                    $pm->nom_ligne,
                    $pm->codepassager,
                    number_format($pm->prixvente, 0, '', ' '),
                    number_format($pm->total, 0, '', ' ')// garder un nombre propre
                  ];
                  fputcsv($output, $row, $delimiter);
                }
                // Ligne RETOUR
                fputcsv($output, ['RETOUR', '', '', ''], $delimiter);

                // PASSAGERS RETOUR
                foreach ($retourreport as $rm) {
                  $aler2 = explode("-", $rm->nom_ligne);
                    $allerretour2 = $aler2[1]. '-' .$aler2[0];
                      $etaglobals += $rm->totalr;
                      $nbrt += round($rm->code_non_pass);
                    $pr += $rm->prixretour;
                  $row = [
                    $allerretour2,
                    $rm->code_non_pass,
                    number_format($rm->prixretour, 0, '', ' '),
                    number_format($rm->totalr, 0, '', ' ')
                  ];
                  fputcsv($output, $row, $delimiter);
                }

                fputcsv($output, ['TOTAL', '', '', $nb+$nbrt], $delimiter);

                fputcsv($output, ['SOMME:', '', '', number_format($etatglobale + $etaglobals, 0, '', ' ')], $delimiter);
                
                fclose($output);
                exit;
 
            }
            else
            {
                if($comp == 5002){

                    $onreport = $this->m_passager->listereport($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $cais, $lign);
                    $retourreport = $this->m_non_passager->listereportretour($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $cais, $lign);

                }
                else
                {
                    $onreport = $this->m_passager->listereportcpt($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $cais, $lign);
                    $retourreport = $this->m_non_passager->listereportretourcpt($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $cais, $lign);
                }
                

                $etatglobale = 0;
                  $etaglobals = 0;
                  $nb = 0;
                  $nbrt = 0;
                  $p = 0;
                  $pr = 0;

                // Préparer le nom du fichier CSV
                $filename = 'EXERCICE MENSUEL TICKET GUICHETIER '.$us.'_' . $ncomp->nom_compagnie . '_' . $days . '_AU_' . $days1 . '.csv';

                header('Content-Type: text/csv; charset=utf-8');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                $output = fopen('php://output', 'w');
                fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM 
                // Avant le foreach, juste après fopen()
                  stream_filter_prepend($output, 'convert.iconv.UTF-8/UTF-8');

                // Forcer le séparateur ;
                $delimiter = ';';

                // En-têtes
                $header = ['LIGNE', 'NBR_TICKETS', 'PRIX_UNITAIRE', 'PRIX_TOTAL'];
                fputcsv($output, $header, $delimiter);

                // PASSAGERS ALLER
                foreach ($onreport as $pm) {
 
                    $etatglobale += $pm->total;
                      $nb +=round($pm->codepassager);
                      //$p += $pm->prixvente;
                      
                  $row = [
                    $pm->nom_ligne,
                    $pm->codepassager,
                    number_format($pm->prixvente, 0, '', ' '),
                    number_format($pm->total, 0, '', ' ')
                    // garder un nombre propre
                  ];
                  fputcsv($output, $row, $delimiter);
                }
                // Ligne RETOUR
                fputcsv($output, ['RETOUR', '', '', '', '', ''], $delimiter);

                // PASSAGERS RETOUR
                foreach ($retourreport as $rm) {
                  $aler2 = explode("-", $rm->nom_ligne);
                    $allerretour2 = $aler2[1]. '-' .$aler2[0];
                      $etaglobals += $rm->totalr;
                      $nbrt += round($rm->code_non_pass);
                    //$pr += $rm->prixretour;
                  $row = [
                    $allerretour2,
                    $rm->code_non_pass,
                    number_format($rm->prixretour, 0, '', ' '),
                    number_format($rm->totalr, 0, '', ' ')
                  ];
                  fputcsv($output, $row, $delimiter);
                }

                fputcsv($output, ['TOTAL', '', '', '', $nb+$nbrt], $delimiter);

                fputcsv($output, ['SOMME:', '', '', '', number_format($etatglobale + $etaglobals, 0, '', ' ')], $delimiter);
                
                fclose($output);
                exit;
 
            }    
        }
        //escal

        public function exoreportsesc($ckey, $g)
        {
          $this->entreprise = $this->m_entreprises->get_key($ckey);

              $dt1 = $this->input->post('datedebutesc');
              $dt2 = $this->input->post('datefinesc');
              $cais = $this->input->post('caissieresc');
              $lign = $this->input->post('axeligneesc');
              $comp = $this->input->post('_compagesc');
              $gid = $this->input->post('departgaresc');
              $uc = $this->m_utilisateur->u($cais);

              $ncomp = $this->m_compagnies->getn($comp);

              if($uc == NULL){

                $us = '';

              }else{
                $us = $uc->first_name.' '.$uc->last_name;
              }
              $dats = explode("-", $dt1);
              $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
              $dats1 = explode("-", $dt2);
              $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
              
                $onreport = $this->m_escalclients->listereportcptesc($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $cais, $lign);
                    

                $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                // set document information
                $pdf->SetCreator(PDF_CREATOR);
                $pdf->SetAuthor('NET SOLUTIONS');
                $pdf->SetTitle('LISTE-');
                $pdf->SetSubject('RECAPTULATIF');
                $pdf->SetKeywords('--');
                
                $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
                // remove default header/footer
                $pdf->setPrintHeader(true);
                $pdf->setPrintFooter(false);
                
                // set default monospaced font
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
                $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                // set margins
                $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                
                
                // set auto page breaks
                $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
                
                // set image scale factor
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
                
                // set font
                
                
                // add a page
                $pdf->AddPage('L', 'A4', 0);
                
                // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                // GROUPE DE GAUCHE
                $pdf->SetFont('courier', '', 9);
                            
                $titre = '<h1 align="center">EXERCICE MENSUEL TICKET GUICHETIER ESCAL'.$us.' '.$ncomp->nom_compagnie.' DU '. $days .' AU '.$days1.'</h1>';
                $them = '<table border="1" cellpadding="0">
                    <thead> 
                        <tr> 
                          
                          <th width="20%" align="center"><strong>LIGNE</strong></th>
                          <th width="15%" align="center"><strong>NBR_TICKETS</strong></th> 
                          <th width="20%" align="center"><strong>PRIX_UNITAIRE</strong></th>
                          <th width="20%" align="center"><strong>PRIX_TOTAL</strong></th>
                        </tr>
                    </thead>
                    <tbody>';
                    $tglobal = 0;
                    $nb = 0;
                    $p = 0;
                    foreach ($onreport as $departh => $element) {
                      $them .= '<tr>
                          <td width="20%" align="left"><strong>' . $element->nom_ligne . '</strong></td>
                          <td width="15%" align="center"><strong>' . round($element->escalp) . '</strong></td>
                          <td width="20%" align="center"><strong>' . number_format($element->prixescal, 0, '', ' ') . '</strong></td>
                          <td width="20%" align="right"><strong>' . number_format($element->tota, 0, '', ' ') . '</strong></td>
                          </tr>';
                           $tglobal +=$element->tota;
                           $nb +=$element->escalp;
                    }
               
                       $them .= '<tr>
                          <td width="20%" align="left"><strong>TOTAL</strong></td>
                          <td width="15%" align="center"><strong> '.($nb).'</strong></td>
                          <td width="20%" align="center"><strong></strong></td>
                          <td width="20%" align="right"><strong> '.number_format($tglobal, 0, '', ' ').'</strong></td>
                          
                     </tr>';

                $them .= ' </tbody></table>';
                $them.= '<h2>SOMME:'. number_format($tglobal, 0, '', ' ') .' </h2>';
            
                $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
                $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
                ob_end_clean();
                //Close and output PDF document
                $pdf->Output('example_012.pdf' . '', 'I');  
        }

        public function exoreportsvers($ckey, $g)
        {
          $this->entreprise = $this->m_entreprises->get_key($ckey);
              $dt1 = $this->input->post('datedebutvers');
              $dt2 = $this->input->post('datefinvers');
              $cais = $this->input->post('caissiervers');
              $lign = $this->input->post('axelignevers');
              $comp = $this->input->post('_compagvers');

              $ncomp = $this->m_compagnies->getn($comp);

              $gid = $this->input->post('departgarvers');
              $ncgd = $this->m_gare_depart->getn($gid);
                $gar = $ncgd->nom_gaep;

              $uc = $this->m_utilisateur->u($cais);
              if($uc == NULL){
                $us = '';
              }else{
                $us = $uc->first_name.' '.$uc->last_name;
              }
                $dats = explode("-", $dt1);
                  $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                  $dats1 = explode("-", $dt2);
                  $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
            
                if($comp == 5002)
                {
                    $onreport = $this->m_passager->listereportverscptgl($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $cais, $lign);
                  $retourreport = $this->m_non_passager->listereportversretourcptad($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $cais, $lign);
                }
                else
                {
                    $onreport = $this->m_passager->listereportverscpt($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $cais);

                    $retourreport = $this->m_non_passager->listereportversretourcpt($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $cais);
                }
                $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                // set document information
                $pdf->SetCreator(PDF_CREATOR);
                $pdf->SetAuthor('NET SOLUTIONS');
                $pdf->SetTitle('LISTE-');
                $pdf->SetSubject('RECAPTULATIF');
                $pdf->SetKeywords('--');
                
                $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
                // remove default header/footer
                $pdf->setPrintHeader(true);
                $pdf->setPrintFooter(false);
                
                // set default monospaced font
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
                $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                // set margins
                $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                
                
                // set auto page breaks
                $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
                
                // set image scale factor
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
                
                // set font
                
                
                // add a page
                $pdf->AddPage('L', 'A4', 0);
                
                // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                // GROUPE DE GAUCHE
                $pdf->SetFont('courier', '', 9);
                            
                $titre = '<h1 align="center">REPORT MENSUEL DES RECETTES '. $ncomp->nom_compagnie.' '.$gar.' '.$us.' DU '. $days .' AU '.$days1.'</h1>';
                $them = '<table border="1" cellpadding="0">
                    <thead> 
                        <tr> 
                          
                          <th width="20%" align="center"><strong>DATE</strong></th>
                           
                          
                          <th width="20%" align="center"><strong>PRIX_TOTAL</strong></th>
                        </tr>
                    </thead>
                    <tbody>';
                    $tglobal = 0;
                    $tglobalretour = 0;
                    $nb = 0;
                    $nbrt = 0;
                    $p = 0;
                    $pr = 0;
                foreach ($onreport as $departh => $element) {
                    $them .= '<tr>
                        <td width="20%" align="left"><strong>' . $element->datep_create . '</strong></td>
                        
                        <td width="20%" align="right"><strong>' . number_format(($element->total), 0, '', ' ') . '</strong></td>
                        </tr>';
                         
                         //$nb +=round($element->codepassager);
                         $p += $element->total;
                }
               $them .= '<tr>
                        <td width="40%" align="center">RETOUR<strong></strong></td>
                        </tr>';
                foreach ($retourreport as $retours => $retour) {
                 
                  $them .= '<tr>
                      <td width="20%" align="left"><strong>' . $retour->datevente . '</strong></td>
                      
                      <td width="20%" align="right"><strong>' . number_format(($retour->totalr), 0, '', ' ') . '</strong></td>
                      </tr>';
                        
                        //$nbrt +=round($retour->code_non_pass);
                        $pr += $retour->totalr;
                      }

                       $them .= '<tr>
                          <td width="20%" align="left"><strong>TOTAL</strong></td>
                          <td width="20%" align="right"><strong> '.number_format($p + $pr, 0, '', ' ').'</strong></td>
                          
                     </tr>';

                $them .= ' </tbody></table>';
                $them.= '<h2>SOMME:'. number_format($p + $pr, 0, '', ' ') .' </h2>';
            
                $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
                $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
                ob_end_clean();
                //Close and output PDF document
                $pdf->Output('example_012.pdf' . '', 'I');   
        }

        public function recaptglcourrier($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
              $dt1 = $this->input->post('datedebutcrgl');
              $dt2 = $this->input->post('datefincrgl');
              $lign = $this->input->post('axelignecrgl');
              $comp = $this->input->post('_compagcrgl');
              $gid = $this->input->post('departgarcrgl');
              $tyc = $this->input->post('typcoursgl');

              $ncomp = $this->m_compagnies->getn($comp);

              //$ct = $this->m_categ->getps($this->entreprise->id_entreprise, $tyc);

              $ty = 'PLIS ';
                $ty2 = 'COLIS';
                if($tyc === 'Gros_plis'){
                  $ty3 = $ty2;
                }elseif($tyc === 'Petit_plis'){
                  $ty3 = $ty;
                }

                $dats = explode("-", $dt1);
                $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                $dats1 = explode("-", $dt2);
                  $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
            
                  //$recapcourrier = $this->m_courrier_expedier->recaptpligl($this->entreprise->ekey, $dt1, $dt2, $gid, $tyc, $comp, $lign);

                  $recapcourrier = $this->m_courrier_expedier->trecaptpligl($this->entreprise->ekey, $dt1, $dt2, $comp, $gid, $tyc, $lign);
                
              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('CBT_RAKIETA');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('L', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                          
              $titre = '<h1 align="center">RECAP GLOBAL COURRIER '.$ty3.' '.$ncomp->compagnie.' DU '. $days .' AU '.$days1.'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead>                          
                        <tr> 
                          <th width="45%" align="left"><strong>LIGNE</strong></th>
                          <th width="15%" align="center"><strong>NOMBRE</strong></th>
                          <th width="20%" align="center"><strong>PRIX</strong></th>
                          <th width="20%" align="right"><strong>MONTANT</strong></th> 
                          </tr>
                    </thead>
                    <tbody>';
                    $tglobal = 0;
                    $nb = 0;
                foreach ($recapcourrier as $departs => $element) {
                    $them .= '<tr>
                    <td width="45%" align="left"><strong>' . $element->nom_ligne . '</strong></td>
                         
                          <td width="15%" align="center"><strong>' . $element->nombres. '</strong></td>
                          <td width="20%" align="center"><strong>' . number_format($element->prixcolis, 0, '', ' ') . '</strong></td>
                          <td width="20%" align="right"><strong>' . number_format($element->nombres * $element->prixcolis, 0, '', ' ') . '</strong></td>
                          </tr>';
                           $tglobal += ($element->nombres) * ($element->prixcolis);
                          //$tglobal1 = $tglobal;
                           $nb +=$element->nombres;
                        }
          
                $them .= '<tr>
                            <td width="45%" align="center"><strong>TOTAL</strong></td>
                            <td width="15%" align="center"><strong> '.($nb).'</strong></td>
                            <td width="20%" align="center"><strong></strong></td>
                            <td width="20%" align="right"><strong> '.number_format($tglobal, 0, '', ' ').'</strong></td>
                            
                       </tr>';

                $them .= ' </tbody></table>';
                $them.= '<h2>SOMME:'. number_format($tglobal, 0, '', ' ') .' </h2>';

               
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_013.pdf' . '', 'I');
              //============================================================+
              // END OF FILE
              //============================================================+
            
        }

        public function recaptglcourrieresc($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
              $dt1 = $this->input->post('datedebutcrglesc');
              $dt2 = $this->input->post('datefincrglesc');
              $lign = $this->input->post('axelignecrglesc');
              $comp = $this->input->post('_compagcrglesc');
              $gid = $this->input->post('departgarcrglesc');
              $tyc = $this->input->post('typcoursglesc');

              $ncomp = $this->m_compagnies->getn($comp);

              //$ct = $this->m_categ->getps($this->entreprise->id_entreprise, $tyc);

                $ty = 'PLIS';
                $ty2 = 'COLIS';

                if($tyc === 'Gros_plis'){
                  $ty3 = $ty2;
                }elseif($tyc === 'Petit_plis'){
                  $ty3 = $ty;
                }elseif($tyc === ''){
                  $ty3 = $ty.'/'.$ty2;
                }

                $dats = explode("-", $dt1);
                $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                $dats1 = explode("-", $dt2);
                  $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
            
                  //$recapcourrier = $this->m_courrier_expedieresc->recaptpligl($this->entreprise->ekey, $dt1, $dt2, $gid, $tyc, $comp, $lign);

                  $recapcourrier = $this->m_courrier_expedieresc->trecaptpligl($this->entreprise->ekey, $dt1, $dt2, $comp, $gid, $tyc, $lign);
                
              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('CBT_RAKIETA');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('L', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                          
              $titre = '<h1 align="center">RECAP GLOBAL COURRIERESCAL '.$ty3.' '.$ncomp->compagnie.' DU '. $days .' AU '.$days1.'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead>                          
                        <tr> 
                          <th width="45%" align="left"><strong>LIGNE</strong></th>
                          <th width="15%" align="center"><strong>NOMBRE</strong></th>
                          <th width="20%" align="center"><strong>PRIX</strong></th>
                          <th width="20%" align="right"><strong>MONTANT</strong></th> 
                          </tr>
                    </thead>
                    <tbody>';
                    $tglobal = 0;
                    $nb = 0;
                foreach ($recapcourrier as $departs => $element) {
                    $them .= '<tr>
                    <td width="45%" align="left"><strong>' . $element->nom_ligne . '</strong></td>
                         
                          <td width="15%" align="center"><strong>' . $element->nombresesc. '</strong></td>
                          <td width="20%" align="center"><strong>' . number_format($element->prixcolisesc, 0, '', ' ') . '</strong></td>
                          <td width="20%" align="right"><strong>' . number_format($element->nombresesc * $element->prixcolisesc, 0, '', ' ') . '</strong></td>
                          </tr>';
                           $tglobal += ($element->nombresesc) * ($element->prixcolisesc);
                          //$tglobal1 = $tglobal;
                           $nb +=$element->nombresesc;
                        }
          
                $them .= '<tr>
                            <td width="45%" align="center"><strong>TOTAL</strong></td>
                            <td width="15%" align="center"><strong> '.($nb).'</strong></td>
                            <td width="20%" align="center"><strong></strong></td>
                            <td width="20%" align="right"><strong> '.number_format($tglobal, 0, '', ' ').'</strong></td>
                            
                       </tr>';

                $them .= ' </tbody></table>';
                $them.= '<h2>SOMME:'. number_format($tglobal, 0, '', ' ') .' </h2>';

               
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_017.pdf' . '', 'I');
              //============================================================+
              // END OF FILE
              //============================================================+
            
        }
 
        //etat liste courriers

        /*public function exoscourrier($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
              $dt1 = $this->input->post('datedebutcrex');
              $dt2 = $this->input->post('datefincrex');
              $lign = $this->input->post('axelignecrex');
              $comp = $this->input->post('_compagcrex');
              $gid = $this->input->post('departgarcrex');
              $tyc = $this->input->post('typcoursex');

              $ncgd = $this->m_gare_depart->getn($gid);
                $gar = $ncgd->nom_gaep;


              $ty = 'PLIS';
                $ty2 = 'COLIS';

                if($tyc === 'Gros_plis'){
                  $ty3 = $ty2;
                }elseif($tyc === 'Petit_plis'){
                  $ty3 = $ty;
                }elseif($tyc === ''){
                  $ty3 = $ty.'/'.$ty2;
                }


                $dats = explode("-", $dt1);
                $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                $dats1 = explode("-", $dt2);
              $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
            
              $recapcourrierex = $this->m_courrier_expedier->expetatspliexo($this->entreprise->ekey, $dt1, $dt2, $comp, $gid, $tyc, $lign);
                  //var_dump($recapcourrierex, $gid, $tyc, $comp, $lign, $dt1, $dt2);
              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('CBT_RAKIETA');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('P', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                        
              $titre = '<h1 align="center">EXERCICE LISTE '.$ty3.' '.$gar.' DU '. $days .' AU '.$days1.'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead> 
                    <tr>
                      <th width="20%" align="center"><strong>CODE</strong>
                        </th>
                        <th width="30%" align="center"><strong>NOM/PRENOM</strong>
                        </th>
                        <th width="20%" align="center"><strong>CONTACT</strong>
                        </th>
                        
                        <th width="10%" align="center"><strong>PRIX</strong>
                        </th>
                        
                      </tr>
                  </thead>
                  <tbody>';
                  
                  
                  $nb = 0;
              foreach ($recapcourrierex as $courrs => $crr) {
                  $them .= '<tr>
                      <td width="20%" align="left"><strong>'.$crr->num_cour.'</strong></td>
                      <td width="30%" align="left"><strong>'.$crr->nom_client.' '.$crr->prenom_client.'</strong></td>
                      <td width="20%" align="left"><strong>'.$crr->contact_client.'</strong>
                        </td>
                      
                      <td width="10%" align="left"><strong>'.number_format($crr->prixcolis, 0, '', ' ').'</strong></td>
                      </tr>';
                      
              }

              $them .= ' </tbody></table>';

              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();

              //Close and output PDF document
              $pdf->Output('example_013.pdf' . '', 'D');
              //==1==========================================================+
              // END OF FILE
              //============================================================+
        }*/

        public function exoscourrier($ckey, $g)
        {   

           $this->entreprise = $this->m_entreprises->get_key($ckey);
              $dt1 = $this->input->post('datedebutcrex');
              $dt2 = $this->input->post('datefincrex');
              $lign = $this->input->post('axelignecrex');
              $comp = $this->input->post('_compagcrex');
              $gid = $this->input->post('departgarcrex');
              $tyc = $this->input->post('typcoursex');

              $ncgd = $this->m_gare_depart->getn($gid);
                $gar = $ncgd->nom_gaep;

                 $ncomp = $this->m_compagnies->getn($comp);

              $ty = 'PLIS';
                $ty2 = 'COLIS';

                if($tyc === 'Gros_plis'){
                  $ty3 = $ty2;
                }elseif($tyc === 'Petit_plis'){
                  $ty3 = $ty;
                }elseif($tyc === ''){
                  $ty3 = $ty.'/'.$ty2;
                }


                $dats = explode("-", $dt1);
                $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                $dats1 = explode("-", $dt2);
              $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
            
              $recapcourrierex = $this->m_courrier_expedier->expetatspliexo($this->entreprise->ekey, $dt1, $dt2, $comp, $gid, $tyc, $lign);


            // Préparer le nom du fichier CSV
            $filename = 'EXERCICE LISTE '.$ty3.' '.$gar.' DU '. $days .' AU '.$days1.' ' . $ncomp->nom_compagnie . '.csv';

            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM 
            // Avant le foreach, juste après fopen()
              stream_filter_prepend($output, 'convert.iconv.UTF-8/UTF-8');

            // Forcer le séparateur ;
            $delimiter = ';';

            // En-têtes
            $header = ['CODE', 'NOM', 'PRENOM', 'CONTACT', 'PRIX'];
            fputcsv($output, $header, $delimiter);

            // PASSAGERS ALLER
            foreach ($recapcourrierex as $p) {
              $row = [
                $p->num_cour,
                $p->nom_client,
                $p->prenom_client,
                $p->contact_client,
                $p->prixcolis,// garder un nombre propre
              ];
              fputcsv($output, $row, $delimiter);
            }

            fclose($output);
            exit;

        }
        
        public function exoscourrieresc($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
              $dt1 = $this->input->post('datedebutcrexesc');
              $dt2 = $this->input->post('datefincrexesc');
              $lign = $this->input->post('axelignecrexesc');
              $comp = $this->input->post('_compagcrexesc');
              $gid = $this->input->post('departgarcrexesc');
              $tyc = $this->input->post('typcoursexesc');

              $ncgd = $this->m_gare_depart->getn($gid);
                $gar = $ncgd->nom_gaep;


              $ty = 'PLIS';
                $ty2 = 'COLIS';

                if($tyc === 'Gros_plis'){
                  $ty3 = $ty2;
                }elseif($tyc === 'Petit_plis'){
                  $ty3 = $ty;
                }elseif($tyc === ''){
                  $ty3 = $ty.'/'.$ty2;
                }


                $dats = explode("-", $dt1);
                $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                $dats1 = explode("-", $dt2);
                  $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
            
                  $recapcourrierex = $this->m_courrier_expedieresc->expetatspliexo($this->entreprise->ekey, $dt1, $dt2, $comp, $gid, $tyc, $lign);
                  
              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('CBT_RAKIETA');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('P', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                        
              $titre = '<h1 align="center">EXERCICE LISTE ESCAL '.$ty3.' '.$gar.' DU '. $days .' AU '.$days1.'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead> 
                    <tr>
                      <th width="20%" align="center"><strong>CODE</strong>
                        </th>
                        <th width="30%" align="center"><strong>NOM/PRENOM</strong>
                        </th>
                        <th width="20%" align="center"><strong>CONTACT</strong>
                        </th>
                        
                        <th width="10%" align="center"><strong>PRIX</strong>
                        </th>
                        
                      </tr>
                  </thead>
                  <tbody>';
                  
                  
                  $nb = 0;
              foreach ($recapcourrierex as $courrs => $crr) {
                  $them .= '<tr>
                      <td width="20%" align="left"><strong>'.$crr->num_couresc.'</strong></td>
                      <td width="30%" align="left"><strong>'.$crr->nom_client.' '.$crr->prenom_client.'</strong></td>
                      <td width="20%" align="left"><strong>'.$crr->contact_client.'</strong>
                        </td>
                      
                      <td width="10%" align="left"><strong>'.number_format($crr->prixcolisesc, 0, '', ' ').'</strong></td>
                      </tr>';
                      
              }

              $them .= ' </tbody></table>';

              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();

              //Close and output PDF document
              $pdf->Output('example_013.pdf' . '', 'D');
              //==1==========================================================+
              // END OF FILE
              //============================================================+
        }

        /*public function courrierglob($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
              $dt1 = $this->input->post('datedebutcrglb');
              $dt2 = $this->input->post('datefincrglb');
              $lign = $this->input->post('axelignecrglb');
              $comp = $this->input->post('_compagcrglb');
              $gid = $this->input->post('departgarcrglb');
              $tyc = $this->input->post('typcoursglb');

              //$ct = $this->m_categ->getps($this->entreprise->id_entreprise, $tyc);

              $ty = 'PLIS ';
                $ty2 = 'COLIS';
                if($tyc === 'Gros_plis'){
                  $ty3 = $ty2;
                }elseif($tyc === 'Petit_plis'){
                  $ty3 = $ty;
                }

                $dats = explode("-", $dt1);
                $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                $dats1 = explode("-", $dt2);
                $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
            
                $recapcourriergl = $this->m_courrier_expedier->expetatspliglob($this->entreprise->ekey, $dt1, $dt2, $gid, $comp, $tyc, $lign);

              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('CBT_RAKIETA');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('P', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                        
              $titre = '<h1 align="center">LISTE GLOBALE COURRIER' .$ty3 .' DU '. $days .' AU '.$days1.'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead> 
                    <tr>
                      <th width="20%" align="center"><strong>CODE</strong>
                        </th>
                        <th width="30%" align="center"><strong>NOM/PRENOM</strong>
                        </th>
                        <th width="20%" align="center"><strong>CONTACT</strong>
                        </th>
                        
                        <th width="10%" align="center"><strong>PRIX</strong>
                        </th>
                        
                      </tr>
                  </thead>
                  <tbody>';
                  
                  
                  $nb = 0;
              foreach ($recapcourriergl as $courrs => $crgl) {
                  $them .= '<tr>
                      <td width="20%" align="left"><strong>'.$crgl->num_cour.'</strong></td>
                      <td width="30%" align="left"><strong>'.$crgl->nom_client.' '.$crgl->prenom_client.'</strong></td>
                      <td width="20%" align="left"><strong>'.$crgl->contact_client.'</strong>
                        </td>
                      
                      <td width="10%" align="left"><strong>'.number_format($crgl->prixcolis, 0, '', ' ').'</strong></td>
                      </tr>';
                      
              }
              
              $them .= ' </tbody></table>';

              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();

              //Close and output PDF document
              $pdf->Output('example_013.pdf' . '', 'D');
              //==1==========================================================+
              // END OF FILE
              //============================================================+
        }*/

        public function courrierglob($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
              $dt1 = $this->input->post('datedebutcrglb');
              $dt2 = $this->input->post('datefincrglb');
              $lign = $this->input->post('axelignecrglb');
              $comp = $this->input->post('_compagcrglb');
              $gid = $this->input->post('departgarcrglb');
              $tyc = $this->input->post('typcoursglb');

              //$ct = $this->m_categ->getps($this->entreprise->id_entreprise, $tyc);

              $ty = 'PLIS ';
                $ty2 = 'COLIS';
                if($tyc === 'Gros_plis'){
                  $ty3 = $ty2;
                }elseif($tyc === 'Petit_plis'){
                  $ty3 = $ty;
                }elseif($tyc === ''){
                  $ty3 = $ty.'/'.$ty2;
                }

                $dats = explode("-", $dt1);
                $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                $dats1 = explode("-", $dt2);
                $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
            
                $recapcourriergl = $this->m_courrier_expedier->expetatspliglob($this->entreprise->ekey, $dt1, $dt2, $gid, $comp, $tyc, $lign);
              
              $ncgd = $this->m_gare_depart->getn($gid);
                $gar = $ncgd->nom_gaep;

                 $ncomp = $this->m_compagnies->getn($comp);

              

            // Préparer le nom du fichier CSV
            $filename = 'LISTE GLOBALE COURRIER '.$ty3.' '.$gar.' DU '. $days .' AU '.$days1.' ' . $ncomp->nom_compagnie . '.csv';

            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM 
            // Avant le foreach, juste après fopen()
              stream_filter_prepend($output, 'convert.iconv.UTF-8/UTF-8');

            // Forcer le séparateur ;
            $delimiter = ';';

            // En-têtes
            $header = ['CODE', 'NOM', 'PRENOM', 'CONTACT', 'PRIX'];
            fputcsv($output, $header, $delimiter);

            // PASSAGERS ALLER
            foreach ($recapcourriergl as $p) {
              $row = [
                $p->num_cour,
                $p->nom_client,
                $p->prenom_client,
                $p->contact_client,
                $p->prixcolis,// garder un nombre propre
              ];
              fputcsv($output, $row, $delimiter);
            }

            fclose($output);
            exit;

        }

        public function courrierglobesc($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
              $dt1 = $this->input->post('datedebutcrglbesc');
              $dt2 = $this->input->post('datefincrglbesc');
              $lign = $this->input->post('axelignecrglbesc');
              $comp = $this->input->post('_compagcrglbesc');
              $gid = $this->input->post('departgarcrglbesc');
              $tyc = $this->input->post('typcoursglbesc');

              //$ct = $this->m_categ->getps($this->entreprise->id_entreprise, $tyc);
              $ty = 'PLIS';
                $ty2 = 'COLIS';

                if($tyc === 'Gros_plis'){
                  $ty3 = $ty2;
                }elseif($tyc === 'Petit_plis'){
                  $ty3 = $ty;
                }elseif($tyc === ''){
                  $ty3 = $ty.'/'.$ty2;
                }
                $dats = explode("-", $dt1);
                $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                $dats1 = explode("-", $dt2);
                  $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
            
                  $recapcourriergl = $this->m_courrier_expedieresc->expetatspliglob($this->entreprise->ekey, $dt1, $dt2, $gid, $comp, $tyc, $lign);

              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('CBT_RAKIETA');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('P', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                        
              $titre = '<h1 align="center">LISTE GLOBALE COURRIERESCAL '.$ty3 .' DU '. $days .' AU '.$days1.'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead> 
                    <tr>
                      <th width="20%" align="center"><strong>CODE</strong>
                        </th>
                        <th width="30%" align="center"><strong>NOM/PRENOM</strong>
                        </th>
                        <th width="20%" align="center"><strong>CONTACT</strong>
                        </th>
                        
                        <th width="10%" align="center"><strong>PRIX</strong>
                        </th>
                        
                      </tr>
                  </thead>
                  <tbody>';
                  
                  
                  $nb = 0;
              foreach ($recapcourriergl as $courrs => $crgl) {
                  $them .= '<tr>
                      <td width="20%" align="left"><strong>'.$crgl->num_couresc.'</strong></td>
                      <td width="30%" align="left"><strong>'.$crgl->nom_client.' '.$crgl->prenom_client.'</strong></td>
                      <td width="20%" align="left"><strong>'.$crgl->contact_client.'</strong>
                        </td>
                      
                      <td width="10%" align="left"><strong>'.number_format($crgl->prixcolisesc, 0, '', ' ').'</strong></td>
                      </tr>';
                      
              }
              
              $them .= ' </tbody></table>';

              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();

              //Close and output PDF document
              $pdf->Output('example_013.pdf' . '', 'D');
              //==1==========================================================+
              // END OF FILE
              //============================================================+
        }

        public function exoversement($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);

              $dt1 = $this->input->post('datedebut');
              $dt2 = $this->input->post('datefin');
              $cais = $this->input->post('caissier');
              $lign = $this->input->post('axeligne');
              $comp = $this->input->post('_compag');
              $gid = $this->input->post('departgar');
              $uc = $this->m_utilisateur->u($cais);
              if($uc == NULL){
                $us = '';
              }else{
                $us = $uc->first_name.' '.$uc->last_name;
              }
              $dats = explode("-", $dt1);
              $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
              $dats1 = explode("-", $dt2);
              $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
            if($this->session->agent->userole === '1' OR $this->session->agent->userole === '2')
            {

                if($comp == 5002){

                    $onreport = $this->m_passager->listereport($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $cais, $lign);
                    $retourreport = $this->m_non_passager->listereportretour($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $cais, $lign);

                }else
                {
                  $onreport = $this->m_passager->listereportcptadmin($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $cais, $lign);
                  $retourreport = $this->m_non_passager->listereportretourcptadmin($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $cais, $lign);
                }

                $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                // set document information
                $pdf->SetCreator(PDF_CREATOR);
                $pdf->SetAuthor('NET SOLUTIONS');
                $pdf->SetTitle('LISTE-');
                $pdf->SetSubject('RECAPTULATIF');
                $pdf->SetKeywords('--');
                
                $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
                // remove default header/footer
                $pdf->setPrintHeader(true);
                $pdf->setPrintFooter(false);
                
                // set default monospaced font
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
                $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                // set margins
                $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                
                
                // set auto page breaks
                $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
                
                // set image scale factor
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
                
                // set font
                
                
                // add a page
                $pdf->AddPage('L', 'A4', 0);
                
                // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                // GROUPE DE GAUCHE
                $pdf->SetFont('courier', '', 9);
                            
                $titre = '<h1 align="center">REPORT DES TICKETS  '.$us.' DU '. $days .' AU '.$days1.'</h1>';
                $them = '<table border="1" cellpadding="0">
                    <thead> 
                        <tr> 
                          
                          <th width="20%" align="center"><strong>LIGNE</strong></th>
                          <th width="15%" align="center"><strong>NBR_TICKETS</strong></th> 
                          <th width="20%" align="center"><strong>PRIX_UNITAIRE</strong></th>
                          <th width="20%" align="center"><strong>PRIX_TOTAL</strong></th>
                        </tr>
                    </thead>
                    <tbody>';
                    $tglobal = 0;
                    $tglobalretour = 0;
                    $nb = 0;
                    $nbrt = 0;
                    $p = 0;
                    $pr = 0;
                foreach ($onreport as $departh => $element) {
                    $them .= '<tr>
                        <td width="20%" align="left"><strong>' . $element->nom_ligne . '</strong></td>
                        <td width="15%" align="center"><strong>' . round($element->codepassager) . '</strong></td>
                        <td width="20%" align="center"><strong>' . number_format($element->prixvente, 0, '', ' ') . '</strong></td>
                        <td width="20%" align="right"><strong>' . number_format($element->total, 0, '', ' ') . '</strong></td>
                        </tr>';
                         $tglobal +=$element->total;
                         $nb +=round($element->codepassager);
                         //$p += $element->total;
                }
               
                foreach ($retourreport as $retours => $retour) {
                  $aler3 = explode("-", $retour->nom_ligne);
                      $allerretour3 = $aler3[1]. '-' .$aler3[0];
                  $them .= '<tr>
                      <td width="20%" align="left"><strong>' . $allerretour3 . '</strong></td>
                      <td width="15%" align="center"><strong>' . round($retour->code_non_pass) . '</strong></td>
                      <td width="20%" align="center"><strong>' . number_format($retour->prixretour, 0, '', ' ') . '</strong></td>
                      <td width="20%" align="right"><strong>' . number_format($retour->totalr, 0, '', ' ') . '</strong></td>
                      </tr>';
                        $tglobalretour +=$retour->totalr;
                        $nbrt +=round($retour->code_non_pass);
                        //$pr += $retour->totalr;
                      }

                       $them .= '<tr>
                          <td width="20%" align="left"><strong>TOTAL</strong></td>
                          <td width="15%" align="center"><strong> '.($nb+$nbrt).'</strong></td>
                          <td width="20%" align="center"><strong></strong></td>
                          <td width="20%" align="right"><strong> '.number_format($tglobal + $tglobalretour, 0, '', ' ').'</strong></td>
                          
                     </tr>';

                $them .= ' </tbody></table>';
                $them.= '<h2>SOMME:'. number_format($tglobal + $tglobalretour, 0, '', ' ') .' </h2>';
            
                $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
                $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
                ob_end_clean();
                //Close and output PDF document
                $pdf->Output('example_012.pdf' . '', 'I');
 
            }
            else
            {
              if($comp == 5002){

                    $onreport = $this->m_passager->listereport($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $cais, $lign);
                    $retourreport = $this->m_non_passager->listereportretour($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $cais, $lign);

                }
                else
                {
                  $onreport = $this->m_passager->listereportcpt($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $cais, $lign);
                  $retourreport = $this->m_non_passager->listereportretourcpt($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $cais, $lign);
                }  

                $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                // set document information
                $pdf->SetCreator(PDF_CREATOR);
                $pdf->SetAuthor('NET SOLUTIONS');
                $pdf->SetTitle('LISTE-');
                $pdf->SetSubject('RECAPTULATIF');
                $pdf->SetKeywords('--');
                
                $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
                // remove default header/footer
                $pdf->setPrintHeader(true);
                $pdf->setPrintFooter(false);
                
                // set default monospaced font
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
                $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                // set margins
                $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                
                
                // set auto page breaks
                $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
                
                // set image scale factor
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
                
                // set font
                
                
                // add a page
                $pdf->AddPage('L', 'A4', 0);
                
                // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                // GROUPE DE GAUCHE
                $pdf->SetFont('courier', '', 9);
                            
                $titre = '<h1 align="center">REPORT DES TICKETS  '.$us.' DU '. $days .' AU '.$days1.'</h1>';
                $them = '<table border="1" cellpadding="0">
                    <thead> 
                        <tr> 
                          
                          <th width="20%" align="center"><strong>LIGNE</strong></th>
                          <th width="15%" align="center"><strong>NBR_TICKETS</strong></th> 
                          <th width="20%" align="center"><strong>PRIX_UNITAIRE</strong></th>
                          <th width="20%" align="center"><strong>PRIX_TOTAL</strong></th>
                        </tr>
                    </thead>
                    <tbody>';
                    $tglobal = 0;
                    $tglobalretour = 0;
                    $nb = 0;
                    $nbrt = 0;
                    $p = 0;
                    $pr = 0;
                foreach ($onreport as $departh => $element) {
                    $them .= '<tr>
                        <td width="20%" align="left"><strong>' . $element->nom_ligne . '</strong></td>
                        <td width="15%" align="center"><strong>' . round($element->codepassager) . '</strong></td>
                        <td width="20%" align="center"><strong>' . number_format($element->prixvente, 0, '', ' ') . '</strong></td>
                        <td width="20%" align="right"><strong>' . number_format($element->total, 0, '', ' ') . '</strong></td>
                        </tr>';
                         $tglobal +=$element->total;
                         $nb +=round($element->codepassager);
                         //$p += $element->totalr;
                }
               
                foreach ($retourreport as $retours => $retour) {
                  $aler3 = explode("-", $retour->nom_ligne);
                      $allerretour3 = $aler3[1]. '-' .$aler3[0];
                  $them .= '<tr>
                      <td width="20%" align="left"><strong>' . $allerretour3 . '</strong></td>
                      <td width="15%" align="center"><strong>' . round($retour->code_non_pass) . '</strong></td>
                      <td width="20%" align="center"><strong>' . number_format($retour->prixretour, 0, '', ' ') . '</strong></td>
                      <td width="20%" align="right"><strong>' . number_format($retour->totalr, 0, '', ' ') . '</strong></td>
                      </tr>';
                        $tglobalretour +=$retour->totalr;
                        $nbrt +=round($retour->code_non_pass);
                        //$pr += $retour->totalr;
                      }

                       $them .= '<tr>
                          <td width="20%" align="left"><strong>TOTAL</strong></td>
                          <td width="15%" align="center"><strong> '.($nb+$nbrt).'</strong></td>
                          <td width="20%" align="center"><strong></strong></td>
                          <td width="20%" align="right"><strong> '.number_format($tglobal + $tglobalretour, 0, '', ' ').'</strong></td>
                          
                     </tr>';

              $them .= ' </tbody></table>';
              $them.= '<h2>SOMME:'. number_format($tglobal + $tglobalretour, 0, '', ' ') .' </h2>';
            
                $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
                $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
                ob_end_clean();
                //Close and output PDF document
                $pdf->Output('example_012.pdf' . '', 'I');
 
            }
              
        }
        //nombre de passager par heure par date 

        public function trinombre($ckey)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
              $dt1 = $this->input->post('date1');
              $dt2 = $this->input->post('date2');

              $cp = $this->input->post('nomcomp');
              $gid = $this->input->post('nomgare');

              $sb1 = strpos($this->input->post('lignear'), '/');
              $lign = substr($this->input->post('lignear'), 0, $sb1);

              $nomlign = substr($this->input->post('lignear'), $sb1 + 1, strlen($this->input->post('lignear')));

              $sb2 = strpos($this->input->post('heuredepart'), '/');
              $her = substr($this->input->post('heuredepart'), 0, $sb2);

              $heur = substr($this->input->post('heuredepart'), $sb2 + 1, strlen($this->input->post('heuredepart')));
               
              $nbrpas = $this->m_passager->reporpass($this->entreprise->ekey, $cp, $gid, $dt1, $dt2, $lign, $her);
  
              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('CBT_RAKIETA');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('P', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                          
              $titre = '<h1 align="center">ETATS PASSAGERS '. $nomlign.'  '.$heur.'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead> 
                      <tr> 
                        <th width="30%" align="center"><strong>DATE / HEURE</strong>
                        </th>
                        <th width="30%" align="center"><strong>LIGNE</strong>
                        </th>
                        <th width="15%" align="center"><strong>NBR_PASSAGERS</strong>
                        </th>
                        
                      </tr>
                  </thead>
                  <tbody>';
                  
                  
                  $nb = 0;
              foreach ($nbrpas as $passagers => $pass) {
                  $them .= '<tr>
                      
                      <td width="30%" align="center"><strong>'.$pass->date_progr.' '.$pass->heure.'</strong></td>
                      <td width="30%" align="center"><strong>'.$pass->nom_ligne.'</strong>
                        </td>
                      <td width="15%" align="center"><strong>'.$pass->nbr.'</strong></td>
                      </tr>';
                      $nb += round($pass->nbr);
              }
            $them .= '<tr>
                        <td width="60%" align="center"><strong>TOTAL</strong></td>
                        <td width="15%" align="center"><strong> '.($nb).'</strong></td>
                        
                   </tr>';
            $them .= ' </tbody></table>';
            
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_013.pdf' . '', 'I');
              //============================================================+
              // END OF FILE
              //============================================================+
        }

        
        public function trinombrees($ckey)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
              $dt1 = $this->input->post('date1es');
              $dt2 = $this->input->post('date2es');

              $cp = $this->input->post('nomcompes');
              $gid = $this->input->post('nomgarees');

              $sb1 = strpos($this->input->post('ligneares'), '/');
              $lign = substr($this->input->post('ligneares'), 0, $sb1);

              $nomlign = substr($this->input->post('ligneares'), $sb1 + 1, strlen($this->input->post('ligneares')));

              $sb2 = strpos($this->input->post('heuredepartes'), '/');
              $her = substr($this->input->post('heuredepartes'), 0, $sb2);

              $heur = substr($this->input->post('heuredepartes'), $sb2 + 1, strlen($this->input->post('heuredepartes')));
               
              $nbrpas = $this->m_escalclients->reporpass($this->entreprise->ekey, $cp, $gid, $dt1, $dt2, $lign, $her);
  
              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('CBT_RAKIETA');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('P', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                          
              $titre = '<h1 align="center">ETAT PASSAGERS ESCAL'. $nomlign.'  '.$heur.'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead> 
                      <tr> 
                        <th width="30%" align="center"><strong>DATE / HEURE</strong>
                        </th>
                        <th width="30%" align="center"><strong>LIGNE</strong>
                        </th>
                        <th width="30%" align="center"><strong>NOM/PRENOM</strong>
                        </th>
                        
                      </tr>
                  </thead>
                  <tbody>';
                  
                  
                  $nb = 0;
              foreach ($nbrpas as $passagers => $pass) {
                  $them .= '<tr>
                      
                      <td width="30%" align="center"><strong>'.$pass->datedepescal.' '.$pass->heure.'</strong></td>
                      <td width="30%" align="center"><strong>'.$pass->nom_ligne.'</strong>
                        </td>
                      <td width="30%" align="center"><strong>'.$pass->nom_client .' '.$pass->prenom_client .' '.$pass->contact_client .'</strong></td>
                      </tr>';
              }
            $them .= '<tr>
                        <td width="60%" align="center"><strong>TOTAL</strong></td>
                        <td width="15%" align="center"><strong> '.($nb).'</strong></td>
                        
                   </tr>';
            $them .= ' </tbody></table>';
            
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_013.pdf' . '', 'I');
              //============================================================+
              // END OF FILE
              //============================================================+
        }
        //nombre passagers par date

        /*public function trinombrepasspdf($ckey)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
              $dtp1 = $this->input->post('dateps1');
              $dtp2 = $this->input->post('dateps2');

              $dat = explode("-", $dtp1);
              $dat2 = explode("-", $dtp2);
              
              $day = $dat[2]. '-'. $dat[1]. '-' .$dat[0];

              $day2 = $dat2[2]. '-'. $dat2[1]. '-' .$dat2[0];

              $cp = $this->input->post('nomcomps');
              $gid = $this->input->post('nomgares');

              $ncomp = $this->m_compagnies->getn($cp);

              $nbrpas = $this->m_passager->exopass($this->entreprise->ekey, $cp, $dtp1, $dtp2, $gid);

              $nbrpasrt = $this->m_non_passager->exopass($this->entreprise->ekey, $cp, $dtp1, $dtp2, $gid);
                   
              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('CBT_RAKIETA');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('P', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                        
              $titre = '<h1 align="center">EXERCICE LISTE PASSAGERS '. $ncomp->nom_compagnie.' DU '. $day.' AU '.$day2.'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead> 
                    <tr>
                      <th width="20%" align="center"><strong>CODE</strong>
                        </th>
                        <th width="30%" align="center"><strong>NOM/PRENOM</strong>
                        </th>
                        <th width="20%" align="center"><strong>CONTACT</strong>
                        </th>
                        <th width="20%" align="center"><strong>LIGNE</strong>
                        </th>
                        <th width="10%" align="center"><strong>PRIX</strong>
                        </th>
                        
                      </tr>
                  </thead>
                  <tbody>';
                  
                  
                  $nb = 0;
              foreach ($nbrpas as $passagers => $pass) {
                  $them .= '<tr>
                      <td width="20%" align="left"><strong>'.$pass->code_ticket.'</strong></td>
                      <td width="30%" align="left"><strong>'.$pass->nom_client.' '.$pass->prenom_client.'</strong></td>
                      <td width="20%" align="left"><strong>'.$pass->contact_client.'</strong>
                        </td>
                      <td width="20%" align="left"><strong>'.$pass->nom_ligne.'</strong></td>
                      <td width="10%" align="left"><strong>'.number_format($pass->prixvente, 0, '', ' ').'</strong></td>
                      </tr>';
                      
                      //$nb += $pass->cdp;
              }

            $them .= '<tr>
                        <td width="100%" align="center">RETOUR<strong></strong></td>
                        </tr>';

                    foreach ($nbrpasrt as $passagersrt => $passrt) {
                  $them .= '<tr>
                      <td width="20%" align="left"><strong>'.$passrt->codeticket.'</strong></td>
                      <td width="30%" align="left"><strong>'.$passrt->nom_client.' '.$passrt->prenom_client.'</strong></td>
                      <td width="20%" align="left"><strong>'.$passrt->contact_client.'</strong>
                        </td>
                      <td width="20%" align="left"><strong>'.$passrt->nom_ligne.'</strong></td>
                      <td width="10%" align="left"><strong>'.number_format($passrt->prixretour, 0, '', ' ').'</strong></td>
                      </tr>';
                      
              }
            
              $them .= ' </tbody></table>';

              //$them.= '<h2>TOTAL: </h2>';

              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();

              //Close and output PDF document
              $pdf->Output('example_013.pdf' . '', 'D');
              //==1==========================================================+
              // END OF FILE
              //============================================================+
        }*/

        public function trinombrepass($ckey)
        {
            $this->_rapport_limits();

            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dtp1 = $this->input->post('dateps1');
            $dtp2 = $this->input->post('dateps2');

            $dat = explode("-", $dtp1);
            $dat2 = explode("-", $dtp2);
            $day = $dat[2] . '-' . $dat[1] . '-' . $dat[0];
            $day2 = $dat2[2] . '-' . $dat2[1] . '-' . $dat2[0];

            $cp = $this->input->post('nomcomps');
            $gid = $this->input->post('nomgares');

            $ncomp = $this->m_compagnies->getn($cp);

            $nbrpas = $this->m_passager->exopass($this->entreprise->ekey, $cp, $dtp1, $dtp2, $gid);
            $nbrpasrt = $this->m_non_passager->exopass($this->entreprise->ekey, $cp, $dtp1, $dtp2, $gid);

            // --- TRI DES PASSAGERS (ALLER) par nom / prénom
            usort($nbrpas, function($a, $b) {
                // compare nom d’abord
                $cmpNom = strcmp($a->nom_client, $b->nom_client);
                if ($cmpNom !== 0) {
                    return $cmpNom;
                }
                // si même nom, comparer prénom
                return strcmp($a->prenom_client, $b->prenom_client);
            });

            // --- TRI DES PASSAGERS RETOUR par nom / prénom
            usort($nbrpasrt, function($a, $b) {
                $cmpNom = strcmp($a->nom_client, $b->nom_client);
                if ($cmpNom !== 0) {
                    return $cmpNom;
                }
                return strcmp($a->prenom_client, $b->prenom_client);
            });

            // Préparer le nom du fichier CSV
            $filename = 'liste_passagers_exo_' . $ncomp->nom_compagnie . '_' . $day . '_au_' . $day2 . '.csv';

            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM 
            // Avant le foreach, juste après fopen()
              stream_filter_prepend($output, 'convert.iconv.UTF-8/UTF-8');

            // Forcer le séparateur ;
            $delimiter = ';';

            // En-têtes
            $header = ['CODE', 'NOM', 'PRENOM', 'CONTACT', 'LIGNE', 'PRIX'];
            fputcsv($output, $header, $delimiter);

            // PASSAGERS ALLER
            foreach ($nbrpas as $p) {
              $row = [
                $p->code_ticket,
                $p->nom_client,
                $p->prenom_client,
                $p->contact_client,
                $p->nom_ligne,
                $p->prixvente  // garder un nombre propre
              ];
              fputcsv($output, $row, $delimiter);
            }

            // Ligne RETOUR
            fputcsv($output, ['RETOUR', '', '', '', '', ''], $delimiter);

            // PASSAGERS RETOUR
            foreach ($nbrpasrt as $r) {
              $row = [
                $r->codeticket,
                $r->nom_client,
                $r->prenom_client,
                $r->contact_client,
                $r->nom_ligne,
                $r->prixretour
              ];
              fputcsv($output, $row, $delimiter);
            }

            fclose($output);
            exit;
        }

        public function trinombrepassesc($ckey)
        {
          $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dtp1 = $this->input->post('dateps1esc');
            $dtp2 = $this->input->post('dateps2esc');

            $dat = explode("-", $dtp1);
            $dat2 = explode("-", $dtp2);
            
            $day = $dat[2]. '-'. $dat[1]. '-' .$dat[0];

            $day2 = $dat2[2]. '-'. $dat2[1]. '-' .$dat2[0];

            $cp = $this->input->post('nomcompsesc');
            $gid = $this->input->post('nomgaresesc');

            $ncomp = $this->m_compagnies->getn($cp);

            $nbrpas = $this->m_escalclients->exopass($this->entreprise->ekey, $cp, $gid, $dtp1, $dtp2);

                 
            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            // set document information
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('NET SOLUTIONS');
            $pdf->SetTitle('LISTE-');
            $pdf->SetSubject('CBT_RAKIETA');
            $pdf->SetKeywords('--');
            
            $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
            // remove default header/footer
            $pdf->setPrintHeader(true);
            $pdf->setPrintFooter(false);
            
            // set default monospaced font
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            // set margins
            $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
            
            
            // set auto page breaks
            $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
            
            // set image scale factor
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
            
            // set font
            
            
            // add a page
            $pdf->AddPage('P', 'A4', 0);
            
            // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
            // GROUPE DE GAUCHE
            $pdf->SetFont('courier', '', 9);
                      
            $titre = '<h1 align="center">EXERCICE LISTE PASSAGERS ESCAL '. $ncomp->nom_compagnie.' DU '. $day.' AU '.$day2.'</h1>';
            $them = '<table border="1" cellpadding="0">
                <thead> 
                  <tr>
                    <th width="20%" align="center"><strong>CODE</strong>
                      </th>
                      <th width="30%" align="center"><strong>NOM/PRENOM</strong>
                      </th>
                      <th width="20%" align="center"><strong>CONTACT</strong>
                      </th>
                      <th width="20%" align="center"><strong>LIGNE</strong>
                      </th>
                      <th width="10%" align="center"><strong>PRIX</strong>
                      </th>
                      
                    </tr>
                </thead>
                <tbody>';
                
                
                $nb = 0;
            foreach ($nbrpas as $passagers => $pass) {
                $them .= '<tr>
                    <td width="20%" align="left"><strong>'.$pass->idclescal.'</strong></td>
                    <td width="30%" align="left"><strong>'.$pass->nom_client.' '.$pass->prenom_client.'</strong></td>
                    <td width="20%" align="left"><strong>'.$pass->contact_client.'</strong></td>
                    <td width="20%" align="left"><strong>'.$pass->nom_ligne.'</strong></td>
                    <td width="10%" align="left"><strong>'.number_format($pass->prixescal, 0, '', ' ').'</strong></td>
                    </tr>';
            }

                  
            $them .= ' </tbody></table>';

            $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
            $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
            ob_end_clean();

            //Close and output PDF document
            $pdf->Output('example_013.pdf' . '', 'D');
            //==1==========================================================+
            // END OF FILE
            //============================================================+
        }

  

        public function tripassagergr($ckey, $us, $gd, $sgd)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
              $dtp1 = $this->input->post('debutdateg');
              $dtp2 = $this->input->post('findateg');

              $dat = explode("-", $dtp1);
              $dat2 = explode("-", $dtp2);
              
              $day = $dat[2]. '-'. $dat[1]. '-' .$dat[0];

              $day2 = $dat2[2]. '-'. $dat2[1]. '-' .$dat2[0];

              $nbrpas = $this->m_ordres->gettr($this->entreprise->ekey, $gd, $dtp1, $dtp2);
              
              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('CBT_RAKIETA');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('P', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                          
              $titre = '<h1 align="center">ETATS AUTRES PASSAGERS '. $day.'  '.$day2.'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead> 
                      <tr>
                      <th width="30%" align="center"><strong>OPERATEUR ET P/O</strong>
                        </th>
                      <th width="20%" align="center"><strong>CODE</strong>
                        </th>
                        <th width="30%" align="center"><strong>NOM/PRENOM</strong>
                        </th>
                        
                        <th width="20%" align="center"><strong>LIGNES</strong>
                        </th>
                        
                      </tr>
                  </thead>
                  <tbody>';
                  
                  
                  $nb = 0;
              foreach ($nbrpas as $passagers => $passg) {
                  $them .= '<tr>
                  <td width="30%" align="left"><strong>'.$passg->username.' '.$passg->pourordre.'</strong>
                        </td>
                      <td width="20%" align="left"><strong>'.$passg->code_ticket.'</strong></td>
                      <td width="30%" align="left"><strong>'.$passg->nom_client.' '.$passg->prenom_client.'</strong></td>
                      
                      <td width="20%" align="left"><strong>'.$passg->nom_ligne.'</strong></td>
                      </tr>';     
              }
            
            $them .= ' </tbody></table>';
            
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_013.pdf' . '', 'I');
              //============================================================+
              // END OF FILE
              //============================================================+
        }

        /*public function trinombrepassglobpdf($ckey)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
              $dtp1 = $this->input->post('dateps1');
              $dtp2 = $this->input->post('dateps2');

              $cp = $this->input->post('nomcomps');
              $gid = $this->input->post('nomgares');

              $ncomp = $this->m_compagnies->getn($cp);

              $nbrpas = $this->m_passager->exopassglob($this->entreprise->ekey, $cp, $dtp1, $dtp2, $gid);
              
              $dat = explode("-", $dtp1);
              $dat2 = explode("-", $dtp2);
              
              $day1 = $dat[2]. '-'. $dat[1]. '-' .$dat[0];

              $day2 = $dat2[2]. '-'. $dat2[1]. '-' .$dat2[0];

              //var_dump($nbrpas);
              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('CBT_RAKIETA');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('P', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                          
              $titre = '<h1 align="center">LISTE GLOBALE PASSAGERS '. $ncomp->nom_compagnie.' DU '. $day1.' AU '.$day2.'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead> 
                      <tr>
                      <th width="20%" align="center"><strong>CODE</strong>
                        </th>
                        <th width="30%" align="center"><strong>NOM/PRENOM</strong>
                        </th>
                        <th width="30%" align="center"><strong>CONTACT</strong>
                        </th>
                        <th width="20%" align="center"><strong>PRIX</strong>
                        </th>
                        
                      </tr>
                  </thead>
                  <tbody>';
                  
                  
                  $nb = 0;
              foreach ($nbrpas as $passagers => $pass) {
                  $them .= '<tr>
                      <td width="20%" align="left"><strong>'.$pass->code_ticket.'</strong></td>
                      <td width="30%" align="left"><strong>'.$pass->nom_client.' '.$pass->prenom_client.'</strong></td>
                      <td width="30%" align="left"><strong>'.$pass->contact_client.'</strong>
                        </td>
                      <td width="20%" align="left"><strong>'.number_format($pass->prixvente, 0, '', ' ').'</strong></td>

                      </tr>';
                      
              }
            
            $them .= ' </tbody></table>';
            
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_016.pdf' . '', 'D');
              //============================================================+
              // END OF FILE
              //============================================================+
        }*/

        public function trinombrepassglob($ckey)
        {
            $this->_rapport_limits();

            $this->entreprise = $this->m_entreprises->get_key($ckey);
              $dtp1 = $this->input->post('dateps1');
              $dtp2 = $this->input->post('dateps2');

              $cp = $this->input->post('nomcomps');
              $gid = $this->input->post('nomgares');

              $ncomp = $this->m_compagnies->getn($cp);

              $nbrpas = $this->m_passager->exopassglob($this->entreprise->ekey, $cp, $dtp1, $dtp2, $gid);
              
              $dat = explode("-", $dtp1);
              $dat2 = explode("-", $dtp2);
              
              $day1 = $dat[2]. '-'. $dat[1]. '-' .$dat[0];

              $day2 = $dat2[2]. '-'. $dat2[1]. '-' .$dat2[0];


            // --- TRI DES PASSAGERS (ALLER) par nom / prénom
            usort($nbrpas, function($a, $b) {
                // compare nom d’abord
                $cmpNom = strcmp($a->nom_client, $b->nom_client);
                if ($cmpNom !== 0) {
                    return $cmpNom;
                }
                // si même nom, comparer prénom
                return strcmp($a->prenom_client, $b->prenom_client);
            });

            // Préparer le nom du fichier CSV
            $filename = 'liste_passagers_global_' . $ncomp->nom_compagnie . '_' . $day1 . '_au_' . $day2 . '.csv';

            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM 
            // Avant le foreach, juste après fopen()
              stream_filter_prepend($output, 'convert.iconv.UTF-8/UTF-8');

            // Forcer le séparateur ;
            $delimiter = ';';

            // En-têtes
            $header = ['CODE', 'NOM', 'PRENOM', 'CONTACT', 'LIGNE', 'PRIX'];
            fputcsv($output, $header, $delimiter);

            // PASSAGERS ALLER
            foreach ($nbrpas as $p) {
              $row = [
                $p->code_ticket,
                $p->nom_client,
                $p->prenom_client,
                $p->contact_client,
                $p->nom_ligne,
                $p->prixvente  // garder un nombre propre
              ];
              fputcsv($output, $row, $delimiter);
            }

            fclose($output);
            exit;
        }
        
        public function trinombrepassglobesc($ckey)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
              $dtp1 = $this->input->post('dateps1esc');
              $dtp2 = $this->input->post('dateps2esc');

              $cp = $this->input->post('nomcompsesc');
              $gid = $this->input->post('nomgaresesc');

              $ncomp = $this->m_compagnies->getn($cp);

              $nbrpas = $this->m_escalclients->exopassglob($this->entreprise->ekey, $cp, $gid, $dtp1, $dtp2);
              
              $dat = explode("-", $dtp1);
              $dat2 = explode("-", $dtp2);
              
              $day1 = $dat[2]. '-'. $dat[1]. '-' .$dat[0];

              $day2 = $dat2[2]. '-'. $dat2[1]. '-' .$dat2[0];

              //var_dump($nbrpas);
              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('CBT_RAKIETA');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('P', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                          
              $titre = '<h1 align="center">LISTE GLOBALE PASSAGERS ESCAL'. $ncomp->nom_compagnie.' DU '. $day1.' AU '.$day2.'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead> 
                      <tr>
                      <th width="20%" align="center"><strong>CODE</strong>
                        </th>
                        <th width="30%" align="center"><strong>NOM/PRENOM</strong>
                        </th>
                        <th width="30%" align="center"><strong>CONTACT</strong>
                        </th>
                        <th width="20%" align="center"><strong>PRIX</strong>
                        </th>
                        
                      </tr>
                  </thead>
                  <tbody>';
                  
                  
                  $nb = 0;
              foreach ($nbrpas as $passagers => $pass) {
                  $them .= '<tr>
                      <td width="20%" align="left"><strong>'.$pass->idclescal.'</strong></td>
                    <td width="30%" align="left"><strong>'.$pass->nom_client.' '.$pass->prenom_client.'</strong></td>
                    <td width="20%" align="left"><strong>'.$pass->contact_client.'</strong></td>
                    <td width="20%" align="left"><strong>'.$pass->nom_ligne.'</strong></td>
                    <td width="10%" align="left"><strong>'.number_format($pass->prixescal, 0, '', ' ').'</strong></td>
                    </tr>';
                      
              }
            
            $them .= ' </tbody></table>';
            
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_016.pdf' . '', 'D');
              //============================================================+
              // END OF FILE
              //============================================================+
        }
        public function exoreportsversgl($ckey, $g)
        {
          $this->entreprise = $this->m_entreprises->get_key($ckey);

              $dt1 = $this->input->post('datedebutversgl');
              $dt2 = $this->input->post('datefinversgl');
              $cais = $this->input->post('caissierversgl');
              $lign = $this->input->post('axeligneversgl');
              $comp = $this->input->post('_compagversgl');

              $ncomp = $this->m_compagnies->getn($comp);
              
              $gid = $this->input->post('departgarversgl');
              $ncgd = $this->m_gare_depart->getn($gid);
                $gar = $ncgd->nom_gaep;

              $uc = $this->m_utilisateur->u($cais);
              if($uc == NULL){
                $us = '';
              }else{
                $us = $uc->first_name.' '.$uc->last_name;
              }
                $dats = explode("-", $dt1);
                  $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                  $dats1 = explode("-", $dt2);
                  $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
            
              $onreport = $this->m_passager->listereportverscptgl($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $cais, $lign);
                $retourreport = $this->m_non_passager->listereportversretourcptad($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $cais, $lign);
            
            
                $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                // set document information
                $pdf->SetCreator(PDF_CREATOR);
                $pdf->SetAuthor('NET SOLUTIONS');
                $pdf->SetTitle('LISTE-');
                $pdf->SetSubject('RECAPTULATIF');
                $pdf->SetKeywords('--');
                
                $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
                // remove default header/footer
                $pdf->setPrintHeader(true);
                $pdf->setPrintFooter(false);
                
                // set default monospaced font
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
                $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                // set margins
                $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                
                
                // set auto page breaks
                $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
                
                // set image scale factor
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
                
                // set font
                
                
                // add a page
                $pdf->AddPage('L', 'A4', 0);
                
                // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                // GROUPE DE GAUCHE
                $pdf->SetFont('courier', '', 9);
                            
                $titre = '<h1 align="center">RECETTE GLOBALE TICKET '. $ncomp->nom_compagnie.' '.$gar.' '.$us.' DU '. $days .' AU '.$days1.'</h1>';
                $them = '<table border="1" cellpadding="0">
                    <thead> 
                        <tr> 
                          
                          <th width="20%" align="center"><strong>DATE</strong></th>
                           
                          
                          <th width="20%" align="center"><strong>PRIX_TOTAL</strong></th>
                        </tr>
                    </thead>
                    <tbody>';
                    $tglobal = 0;
                    $tglobalretour = 0;
                    $nb = 0;
                    $nbrt = 0;
                    $p = 0;
                    $pr = 0;
                foreach ($onreport as $departh => $element) {
                    $them .= '<tr>
                        <td width="20%" align="left"><strong>' . $element->datep_create . '</strong></td>
                        
                        <td width="20%" align="right"><strong>' . number_format(($element->total), 0, '', ' ') . '</strong></td>
                        </tr>';
                         
                        
                         $p += $element->total;
                }
               $them .= '<tr>
                        <td width="40%" align="center">RETOUR<strong></strong></td>
                        </tr>';
                foreach ($retourreport as $retours => $retour) {
                 
                  $them .= '<tr>
                      <td width="20%" align="left"><strong>' . $retour->datevente . '</strong></td>
                      
                      <td width="20%" align="right"><strong>' . number_format(($retour->totalr), 0, '', ' ') . '</strong></td>
                      </tr>';
                        
                       
                        $pr += $retour->totalr;
                      }

                       $them .= '<tr>
                          <td width="20%" align="left"><strong>TOTAL</strong></td>
                          <td width="20%" align="right"><strong> '.number_format($p + $pr, 0, '', ' ').'</strong></td>
                          
                     </tr>';

                $them .= ' </tbody></table>';
                $them.= '<h2>SOMME:'. number_format($p + $pr, 0, '', ' ') .' </h2>';
            
                $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
                $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
                ob_end_clean();
                //Close and output PDF document
                $pdf->Output('example_012.pdf' . '', 'D'); 
        }

        public function exoreportsventegl($ckey, $g)
        {
          $this->entreprise = $this->m_entreprises->get_key($ckey);

              $dt1 = $this->input->post('datedebutventegl');
              $dt2 = $this->input->post('datefinventegl');
              $cais = $this->input->post('caissierventegl');
              $lign = $this->input->post('axeligneventegl');
              $comp = $this->input->post('_compagventegl');
              $gid = $this->input->post('departgarventegl');

              $ncgd = $this->m_gare_depart->getn($gid);
                $gar = $ncgd->nom_gaep;

              $uc = $this->m_utilisateur->u($cais);
              if($uc == NULL){
                $us = '';
              }else{
                $us = $uc->first_name.' '.$uc->last_name;
              }
                $dats = explode("-", $dt1);
                $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                $dats1 = explode("-", $dt2);
                $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
            
              $onreport = $this->m_passager->histoventeadmin($this->entreprise->ekey, $gid, $dt1, $dt2, $cais, $comp, $lign);
                
                $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                // set document information
                $pdf->SetCreator(PDF_CREATOR);
                $pdf->SetAuthor('NET SOLUTIONS');
                $pdf->SetTitle('LISTE-');
                $pdf->SetSubject('RECAPTULATIF');
                $pdf->SetKeywords('--');
                
                $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
                // remove default header/footer
                $pdf->setPrintHeader(true);
                $pdf->setPrintFooter(false);
                
                // set default monospaced font
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
                $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                // set margins
                $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                
                
                // set auto page breaks
                $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
                
                // set image scale factor
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
                
                // set font
                
                
                // add a page
                $pdf->AddPage('L', 'A4', 0);
                
                // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                // GROUPE DE GAUCHE
                $pdf->SetFont('courier', '', 9);
                            
                $titre = '<h1 align="center">REPORT GLOBAL DES VENTES  '.$us.' DU '. $days .' AU '.$days1.'</h1>';
                $them = '<table border="1" cellpadding="0">
                    <thead> 
                        <tr> 
                          
                          <th align="left"><strong>CODE_TAMPON</strong></th>
                          <th align="left"><strong>CODE_PASSAGER</strong></th>
                          <th align="left"><strong>CODE_TICKET</strong></th>
                          <th align="left"><strong>NOM</strong></th>
                          <th align="left"><strong>PRENOM</strong></th>
                          <th align="left"><strong>CONTACT</strong></th>
                          <th align="left"><strong>LIGNE</strong></th>
                          <th align="left"><strong>DATEVENTE</strong></th>
                          <th align="left"><strong>DEPART</strong></th>
                        </tr>
                    </thead>
                    <tbody>';
                    
                foreach ($onreport as $departh => $element) {
                    $them .= '<tr>
                        <td align="left"><strong>' . $element->tamponcod . '</strong></td>
                        <td align="left"><strong>' . $element->code_passager . '</strong></td>
                        <td align="left"><strong>' . $element->code_ticket . '</strong></td>
                        <td align="left"><strong>' . $element->nom_client . '</strong></td>
                        <td align="left"><strong>' . $element->prenom_client . '</strong></td>
                        <td align="left"><strong>' . $element->contact_client . '</strong></td>
                        <td align="left"><strong>' . $element->nom_ligne . '</strong></td>
                        <td align="left"><strong>' . $element->datep_create . '</strong></td>
                        
                        <td align="left"><strong>' . $element->dateheure_prog . '</strong></td>
                        
                        </tr>';
                        
                }
               
                $them .= ' </tbody></table>';
                
                $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
                $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
                ob_end_clean();
                //Close and output PDF document
                $pdf->Output('example_012.pdf' . '', 'I');
      
        }

        public function exoreportsvente($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);

              $dt1 = $this->input->post('datedebutvente');
              $dt2 = $this->input->post('datefinvente');
              $cais = $this->input->post('caissiervente');
              $lign = $this->input->post('axelignevente');
              $comp = $this->input->post('_compagvente');
              $gid = $this->input->post('departgarvente');
              $uc = $this->m_utilisateur->u($cais);
              if($uc == NULL){
                $us = '';
              }else{
                $us = $uc->first_name.' '.$uc->last_name;
              }
                $dats = explode("-", $dt1);
                $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                $dats1 = explode("-", $dt2);
                $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
            

              if($comp == 5002)
              {
                  $onreport = $this->m_passager->histoventeadmin($this->entreprise->ekey, $gid, $dt1, $dt2, $cais, $comp, $lign);
              }
              else{

                  $onreport = $this->m_passager->histovente($this->entreprise->ekey, $gid, $dt1, $dt2, $cais, $comp, $lign);
              }
              
                
                $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                // set document information
                $pdf->SetCreator(PDF_CREATOR);
                $pdf->SetAuthor('NET SOLUTIONS');
                $pdf->SetTitle('LISTE-');
                $pdf->SetSubject('RECAPTULATIF');
                $pdf->SetKeywords('--');
                
                $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
                // remove default header/footer
                $pdf->setPrintHeader(true);
                $pdf->setPrintFooter(false);
                
                // set default monospaced font
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
                $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                // set margins
                $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                
                
                // set auto page breaks
                $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
                
                // set image scale factor
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
                
                // set font
                
                
                // add a page
                $pdf->AddPage('L', 'A4', 0);
                
                // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                // GROUPE DE GAUCHE
                $pdf->SetFont('courier', '', 9);
                            
                $titre = '<h1 align="center">REPORT DES VENTES  '.$us.' DU '. $days .' AU '.$days1.'</h1>';
                $them = '<table border="1" cellpadding="0">
                    <thead> 
                        <tr> 
                          
                          <th align="left"><strong>CODE_TAMPON</strong></th>
                          <th align="left"><strong>CODE_PASSAGER</strong></th>
                          <th align="left"><strong>CODE_TICKET</strong></th>
                          <th align="left"><strong>NOM</strong></th>
                          <th align="left"><strong>PRENOM</strong></th>
                          <th align="left"><strong>CONTACT</strong></th>
                          <th align="left"><strong>LIGNE</strong></th>
                          <th align="left"><strong>DATEVENTE</strong></th>
                          <th align="left"><strong>DEPART</strong></th>
                        </tr>
                    </thead>
                    <tbody>';
                    
                foreach ($onreport as $departh => $element) {
                    $them .= '<tr>
                        <td align="left"><strong>' . $element->tamponcod . '</strong></td>
                        <td align="left"><strong>' . $element->code_passager . '</strong></td>
                        <td align="left"><strong>' . $element->code_ticket . '</strong></td>
                        <td align="left"><strong>' . $element->nom_client . '</strong></td>
                        <td align="left"><strong>' . $element->prenom_client . '</strong></td>
                        <td align="left"><strong>' . $element->contact_client . '</strong></td>
                        <td align="left"><strong>' . $element->nom_ligne . '</strong></td>
                        <td align="left"><strong>' . $element->datep_create . '</strong></td>
                        
                        <td align="left"><strong>' . $element->dateheure_prog . '</strong></td>
                        
                        </tr>';
                        
                }
               
                $them .= ' </tbody></table>';
                
                $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
                $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
                ob_end_clean();
                //Close and output PDF document
                $pdf->Output('example_012.pdf' . '', 'I');
 
              
        }

        public function etatpassagers($ckey)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
              $dt1 = $this->input->post('debudate');
              $dt2 = $this->input->post('fidate');
              $user = $this->input->post('vendeuseid');
              $gid = $this->input->post('departgar');
              $sta = $this->input->post('statutticket');
              $uc = $this->m_utilisateur->u($user);
              if($uc == NULL){
                $us = '';
              }else{
                $us = $uc->first_name.' '.$uc->last_name;
              }
              $dats = explode("-", $dt1);
                $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                $dats1 = explode("-", $dt2);
                $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
              if($sta == 'confirm'){

                $ticketetats = $this->m_passager->etatsc($this->entreprise->ekey, $dt1, $dt2, $gid, $user, $sta);
              } 

              if($sta == 'repor')
              {

                $ticketetats = $this->m_passager->etats($this->entreprise->ekey, $dt1, $dt2, $gid, $user, $sta);
              }

              if($sta == '')
              {
                $ticketetats = $this->m_passager->etats1($this->entreprise->ekey, $dt1, $dt2, $gid, $user);
              }
              
              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('CBT_RAKIETA');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('L', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                          
              $titre = '<h1 align="center">ETATS DES TICKETS  '.$us.' '. $sta .' DU '. $days .' AU '.$days1.'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead> 
                      <tr> 
                        
                        <th width="15%" align="center"><strong>CODE</strong></th> 
                        <th width="15%" align="center"><strong>LIGNE</strong></th> 
                        <th width="20%" align="center"><strong>NOM ET PRENOM</strong></th>
                        <th width="20%" align="center"><strong>DATE ET HEURE</strong></th>
                        <th width="10%" align="center"><strong>PRIX</strong></th> 
                        
                      </tr>
                  </thead>
                  <tbody>';
                  
              foreach ($ticketetats as $ticketetat => $lement) {
                  $them .= '<tr>
                      <td width="15%" align="left"><strong>' . $lement->code_ticket. '</strong></td>
                      <td width="15%" align="left"><strong>' . $lement->nom_ligne . '</strong></td>
                      <td width="20%" align="left"><strong>' . $lement->nom_client . ' ' . $lement->prenom_client . '</strong></td>
                      <td width="20%" align="left"><strong>' . $lement->date_progr . ' ' . $lement->heure . '</strong></td>
                      <td width="10%" align="right"><strong>' . $lement->prixvente. '</strong></td>
                    </tr>';
                      
              }
        
              $them .= ' </tbody></table>';
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_013.pdf' . '', 'I');
              //============================================================+
              // END OF FILE
              //============================================================+
        }
        
        public function verse($ckey){

            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = $this->input->post('datedebut');
            $dt2 = $this->input->post('datefin');
            $ver = $this->input->post('type');
            $nm = $this->input->post('nom');
            $gid = $this->input->post('departgar');
            $comp = $this->input->post('_compag');

            $ncomp = $this->m_compagnies->getn($comp);
            $uopera = $this->input->post('useropered');

              $cai = $this->m_compte_user->cpuseres($uopera);

                $ngrd = $this->m_gare_depart->getno($gid);

              $dats = explode("-", $dt1);
                $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                $dats1 = explode("-", $dt2);
                $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
              if($this->session->agent->userole === '1' OR $this->session->agent->userole === '2')
                {
                    $trivers = $this->m_versements->valigetadmin($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $ver, $nm);

                    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                    // set document information
                    $pdf->SetCreator(PDF_CREATOR);
                    $pdf->SetAuthor('NET SOLUTIONS');
                    $pdf->SetTitle('LISTE-');
                    $pdf->SetSubject('CBT_RAKIETA');
                    $pdf->SetKeywords('--');
                    
                    $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
                    // remove default header/footer
                    $pdf->setPrintHeader(true);
                    $pdf->setPrintFooter(false);
                    
                    // set default monospaced font
                    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
                    $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                    // set margins
                    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                    
                    
                    // set auto page breaks
                    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
                    
                    // set image scale factor
                    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
                    
                    // set font
                    
                    
                    // add a page
                    $pdf->AddPage('L', 'A4', 0);
                      // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                      // GROUPE DE GAUCHE
                      $pdf->SetFont('courier', '', 9);
                                  
                      $titre = '<h1 align="center">ETATS DES VERSEMENTS '. $ncomp->nom_compagnie.' '.$ngrd->garenom.' DU '. $days .' AU '.$days1.'</h1>';
                      $them = '<table border="1" cellpadding="0">
                          <thead> 
                              <tr> 
                              <th width="20%" align="center"><strong>DATE</strong></th>
                                <th width="20%" align="center"><strong>NOM</strong></th>
                                <th width="20%" align="center"><strong>TYPE</strong></th> 
                                <th width="20%" align="center"><strong>MONTANT</strong></th>
                              </tr>
                          </thead>
                          <tbody>';
                          $etatglobal = 0;
                      foreach ($trivers as $vers => $lement) {
                          $them .= '<tr>
                          <td width="20%" align="left"><strong>' . $lement->date_versement . '</strong></td>
                              <td width="20%" align="center"><strong>' . $lement->nom_beneficiaire . '</strong></td>
                              <td width="20%" align="center"><strong>' . $lement->type_versement . '</strong></td>
                              <td width="20%" align="right"><strong>' . number_format($lement->montant_verser, 0, '', ' ') . '</strong></td>
                              </tr>';
                               
                      }
                      
                      $them .= ' </tbody></table>';
                      
                      $them.= '<h2>CAISSE DE :'. $cai->first_name.' '. $cai->last_name.' </h2>';
                    
                      $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
                      $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
                      ob_end_clean();
                      //Close and output PDF document
                      $pdf->Output('example_013.pdf' . '', 'I');
                }
                else
                {
                  $trivers = $this->m_versements->valiget($this->entreprise->ekey, $gid, $uopera, $dt1, $dt2, $comp, $ver, $nm);

                  $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                  // set document information
                  $pdf->SetCreator(PDF_CREATOR);
                  $pdf->SetAuthor('NET SOLUTIONS');
                  $pdf->SetTitle('LISTE-');
                  $pdf->SetSubject('CBT_RAKIETA');
                  $pdf->SetKeywords('--');
                  
                  $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
                  // remove default header/footer
                  $pdf->setPrintHeader(true);
                  $pdf->setPrintFooter(false);
                  
                  // set default monospaced font
                  $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
                  $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                  $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                  // set margins
                  $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                  
                  
                  // set auto page breaks
                  $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
                  
                  // set image scale factor
                  $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
                  
                  // set font
                  
                  
                  // add a page
                  $pdf->AddPage('L', 'A4', 0);
                    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                    // GROUPE DE GAUCHE
                    $pdf->SetFont('courier', '', 9);
                                
                    $titre = '<h1 align="center">ETATS DES VERSEMENTS '.$ncomp->nom_compagnie.' '.$ngrd->garenom.' DU '. $days .' AU '.$days1.'</h1>';
                    $them = '<table border="1" cellpadding="0">
                        <thead> 
                            <tr> 
                            <th width="20%" align="center"><strong>DATE</strong></th>
                              <th width="20%" align="center"><strong>NOM</strong></th>
                              <th width="20%" align="center"><strong>TYPE</strong></th> 
                              <th width="20%" align="center"><strong>MONTANT</strong></th>
                            </tr>
                        </thead>
                        <tbody>';
                        $etatglobal = 0;
                    foreach ($trivers as $vers => $lement) {
                        $them .= '<tr>
                        <td width="20%" align="left"><strong>' . $lement->date_versement . '</strong></td>
                            <td width="20%" align="center"><strong>' . $lement->nom_beneficiaire . '</strong></td>
                            <td width="20%" align="center"><strong>' . $lement->type_versement . '</strong></td>
                            <td width="20%" align="right"><strong>' . number_format($lement->montant_verser, 0, '', ' ') . '</strong></td>
                            </tr>';
                             
                    }
                    
                    $them .= ' </tbody></table>';
                    
                    $them.= '<h2>CAISSE DE :'. $cai->first_name.' '. $cai->last_name.' </h2>';
                  
                    $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
                    $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
                    ob_end_clean();
                    //Close and output PDF document
                    $pdf->Output('example_013.pdf' . '', 'I');
                }
            
        }

          //tirage des recette
        public function recaptrecette($ckey)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
              $date1 = $this->input->post('datedebut');
              $date2 = $this->input->post('datefin');
              $typ = $this->input->post('type');
              $gen = $this->input->post('genre');
              $nm = $this->input->post('nom');
              $gid = $this->input->post('departgar');
              $comp = $this->input->post('_compag');

              $uopera = roleattribut_guard_post_hint($this->entreprise->ekey);

              $cai = $this->m_compte_user->cpuseres($uopera);

              $ngrd = $this->m_gare_depart->getno($gid);

              $ncomp = $this->m_compagnies->getn($comp);
                
                $dats = explode("-", $date1);
                $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                $dats1 = explode("-", $date2);
                $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
                
              if (recette_role_is_saisie($this->session->agent->userole)) {
                $trirecet = $this->m_recette->trirecette_adjoint($this->entreprise->ekey, $gid, $uopera, $date1, $date2, $comp, $typ, $gen, $nm);
              } elseif (recette_role_is_validateur_adjoint($this->session->agent->userole)) {
                $trirecet = $this->m_recette->valdtrirecettead($this->entreprise->ekey, $comp, $gid, $uopera, $date1, $date2, $typ, $gen, $nm);
              } elseif ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2') {
                $trirecet = $this->m_recette->valdtrirecettead($this->entreprise->ekey, $comp, $gid, $uopera, $date1, $date2, $typ, $gen, $nm);
              } elseif (recette_role_is_validateur_principal($this->session->agent->userole)) {
                $uopera = $this->input->post('useropered');
                $trirecet = $this->m_recette->valdtrirecette($this->entreprise->ekey, $comp, $gid, $uopera, $date1, $date2, $typ, $gen, $nm);
              } else {
                $uopera = $this->input->post('useropered');
                $trirecet = $this->m_recette->valdtrirecette($this->entreprise->ekey, $comp, $gid, $uopera, $date1, $date2, $typ, $gen, $nm);
              }
              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('RESERVATIONS');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('L', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', 'B', 10);
                          
              $titre = '<h1 align="center">ETATS DES RECETTES DE '.$ncomp->nom_compagnie.' '.$ngrd->garenom.' DU '. $days .' AU '.$days1.'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead> 
                      <tr> 
                        <th width="10%" align="center"><strong>DATE</strong></th>
                        <th width="10%" align="center"><strong>TYPE</strong></th>
                        <th width="10%" align="center"><strong>GENRE</strong></th> 
                        <th width="15%" align="center"><strong>NOM</strong></th>
                        <th width="20%" align="center"><strong>COMMENTAIRE</strong></th>

                        <th width="15%" align="center"><strong>TOTAL</strong></th>
                      </tr>
                  </thead>
                  <tbody>';
                  $recetglobal = 0;
                  $pdf->SetFont('courier', '', 9);
              foreach ($trirecet as $tris => $rect) {
                  $them .= '<tr>
                      <td width="10%" align="center"><strong>' . $rect->date_recet . '</strong></td>
                      <td width="10%" align="center"><strong>' . $rect->type_recet . '</strong></td>
                      <td width="10%" align="center"><strong>' . $rect->type_personnel . '</strong></td>
                      <td width="15%" align="center"><strong>' . $rect->nom . '</strong></td>
                      <td width="20%" align="center"><strong>' . $rect->commentaire_recet .'</strong></td>

                      <td width="15%" align="center"><strong>' . $rect->montant_recet . '</strong></td>
                      </tr>';
                       $recetglobal +=$rect->montant_recet;
              }
              $them .= '<tr>
                        <td width="65%" align="center"><strong>TOTAL</strong></td>
                        <td width="15%" align="center"><strong> '.number_format($recetglobal, 0, '', ' ').'</strong></td>
                                    
                   </tr>';
              
              $them .= ' </tbody></table>';
              
              $them.= '<h2>RECETTE TOTAL :'. number_format($recetglobal, 0, '', ' ').' </h2>';
              $them.= '<h2>CAISSE DE :'. $cai->first_name.' '. $cai->last_name.' </h2>';
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_015.pdf' . '', 'I');
              
        }

          //tirage des depenses
        public function recaptdepense($ckey)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
              $date1 = $this->input->post('datedebut');
              $date2 = $this->input->post('datefin');
              $typ = $this->input->post('type');
              $gen = $this->input->post('genre');
              $nm = $this->input->post('nom');
              $comp = $this->input->post('_compag');
              $gid = $this->input->post('departgar');
              $ncomp = $this->m_compagnies->getn($comp);
              $uopera = roleattribut_guard_post_hint($this->entreprise->ekey);
              if ($uopera === NULL || $uopera === '') {
                  $uopera = $this->input->post('useropered');
              }

              $ngrd = $this->m_gare_depart->getno($gid);

              $cai = $this->m_compte_user->cpuseres($uopera);

                $dats = explode("-", $date1);
                  $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                  $dats1 = explode("-", $date2);
                  
                  $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
              if (recette_role_is_saisie($this->session->agent->userole)) {
                $tridepens = $this->m_depense->tridepense_adjoint($this->entreprise->ekey, $gid, $uopera, $date1, $date2, $comp, $typ, $gen, $nm);
              } elseif (recette_role_is_validateur_adjoint($this->session->agent->userole)) {
                $tridepens = $this->m_depense->adtridepense($this->entreprise->ekey, $gid, $uopera, $comp, $date1, $date2, $gen, $nm);
              } elseif ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2') {
                $tridepens = $this->m_depense->valdtridepensead($this->entreprise->ekey, $comp, $gid, $uopera, $date1, $date2, $typ, $gen, $nm);
              } elseif (recette_role_is_validateur_principal($this->session->agent->userole)) {
                $uopera = $this->input->post('useropered');
                $tridepens = $this->m_depense->valdtridepense($this->entreprise->ekey, $comp, $gid, $uopera, $date1, $date2, $typ, $gen, $nm);
              } else {
                $uopera = $this->input->post('useropered');
                $tridepens = $this->m_depense->valdtridepense($this->entreprise->ekey, $comp, $gid, $uopera, $date1, $date2, $typ, $gen, $nm);
              }
                $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                // set document information
                $pdf->SetCreator(PDF_CREATOR);
                $pdf->SetAuthor('NET SOLUTIONS');
                $pdf->SetTitle('LISTE-');
                $pdf->SetSubject('RESERVATIONS');
                $pdf->SetKeywords('--');
                
                $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
                // remove default header/footer
                $pdf->setPrintHeader(true);
                $pdf->setPrintFooter(false);
                
                // set default monospaced font
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
                $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                // set margins
                $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                
                
                // set auto page breaks
                $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
                
                // set image scale factor
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
                
                // set font
                
                
                // add a page
                $pdf->AddPage('L', 'A4', 0);
                
                // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                // GROUPE DE GAUCHE
                $pdf->SetFont('courier', '', 9);
                            
                $titre = '<h1 align="center">ETATS DES DEPENSES DE '.$ncomp->nom_compagnie.' '.$ngrd->garenom.' DU '. $days.' AU '.$days1.'</h1>';
                $them = '<table border="1" cellpadding="0">
                    <thead> 
                        <tr> 
                          <th width="10%" align="center"><strong>DATE</strong></th>
                          <th width="10%" align="center"><strong>TYPE</strong></th>
                          <th width="10%" align="center"><strong>GENRE</strong></th> 
                          <th width="15%" align="center"><strong>NOM</strong></th>
                          <th width="20%" align="center"><strong>COMMENTAIRE</strong></th>
                          <th width="20%" align="center"><strong>MOTIF</strong></th>

                          <th width="15%" align="center"><strong>TOTAL</strong></th>
                        </tr>
                    </thead>
                    <tbody>';
                    $depenseglobal = 0;
                foreach ($tridepens as $trid => $depen) {
                    $them .= '<tr>
                        <td width="10%" align="center"><strong>' . $depen->date_depens . '</strong></td>
                        <td width="10%" align="center"><strong>' . $depen->type_depense . '</strong></td>
                        <td width="10%" align="center"><strong>' . $depen->type_personnel . '</strong></td>
                        <td width="15%" align="center"><strong>' . $depen->nom_perso . '</strong></td>
                        <td width="20%" align="center"><strong>' . $depen->commentaire .'</strong></td>
                        <td width="20%" align="center"><strong>' . $depen->motif .'</strong></td>

                        <td width="15%" align="center"><strong>' . $depen->montant_depens . '</strong></td>
                        </tr>';
                         $depenglobal +=$depen->montant_depens;
                }
                
                    $them .= '<tr>
                        <td width="85%" align="center"><strong>TOTAL</strong></td>
                        <td width="15%" align="center"><strong> '.number_format($depenglobal, 0, '', ' ').'</strong></td>
                                    
                   </tr>';
                $them .= ' </tbody></table>';
                
                $them.= '<h2>DEPENSE TOTAL :'. number_format($depenglobal, 0, '', ' ').' </h2>';

                $them.= '<h2>CAISSE DE :'. $cai->first_name.' '. $cai->last_name.' </h2>';

                $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
                $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
                ob_end_clean();
                //Close and output PDF document
                $pdf->Output('example_016.pdf' . '', 'I');
        }

        public function recaptautredepense($ckey)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
              $date1 = $this->input->post('datedebut');
              $date2 = $this->input->post('datefin');
              $typ = $this->input->post('type');
              $gen = $this->input->post('genre');
              $nm = $this->input->post('nom');
              $gid = $this->input->post('departgar');
              
              $uopera = $this->input->post('useropered');

              $cai = $this->m_compte_user->cpuseres($uopera);

              $ngrd = $this->m_gare_depart->getno($gid);


                  $dats = explode("-", $date1);
                      $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                      $dats1 = explode("-", $date2);         
                  $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
                if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){

                    $tridepens = $this->m_depense->valdautretridepensead($this->entreprise->ekey, $gid, $uopera, $date1, $date2, $typ, $gen, $nm);
                  }
                  else{
                    $tridepens = $this->m_depense->valdautretridepense($this->entreprise->ekey, $gid, $uopera, $date1, $date2, $typ, $gen, $nm);
                  }
                $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                // set document information
                $pdf->SetCreator(PDF_CREATOR);
                $pdf->SetAuthor('NET SOLUTIONS');
                $pdf->SetTitle('LISTE-');
                $pdf->SetSubject('RESERVATIONS');
                $pdf->SetKeywords('--');
                
                $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
                // remove default header/footer
                $pdf->setPrintHeader(true);
                $pdf->setPrintFooter(false);
                
                // set default monospaced font
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
                $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                // set margins
                $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                
                
                // set auto page breaks
                $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
                
                // set image scale factor
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
                
                // set font
                
                
                // add a page
                $pdf->AddPage('L', 'A4', 0);
                
                // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                // GROUPE DE GAUCHE
                $pdf->SetFont('courier', '', 9);
                            
                $titre = '<h1 align="center">ETATS DES DEPENSES '.$ngrd->garenom.' DU '. $days .' AU '.$days1.'</h1>';
                $them = '<table border="1" cellpadding="0">
                    <thead> 
                        <tr> 
                          <th width="10%" align="center"><strong>DATE</strong></th>
                          <th width="10%" align="center"><strong>TYPE</strong></th>
                          <th width="10%" align="center"><strong>GENRE</strong></th> 
                          <th width="15%" align="center"><strong>NOM</strong></th>
                          <th width="15%" align="center"><strong>TOTAL</strong></th>
                          <th width="20%" align="center"><strong>COMMENTAIRE</strong></th>
                          <th width="20%" align="center"><strong>MOTIF</strong></th>
                        </tr>
                    </thead>
                    <tbody>';
                    $depenseglobal = 0;
                foreach ($tridepens as $trid => $depen) {
                    $them .= '<tr>
                        <td width="10%" align="center"><strong>' . $depen->date_depens . '</strong></td>
                        <td width="10%" align="center"><strong>' . $depen->type_depense . '</strong></td>
                        <td width="10%" align="center"><strong>' . $depen->genre_depens . '</strong></td>
                        <td width="15%" align="center"><strong>' . $depen->nom_perso . '</strong></td>
                        <td width="15%" align="center"><strong>' . $depen->montant_depens . '</strong></td>
                        <td width="20%" align="center"><strong>' . $depen->commentaire .'</strong></td>
                        <td width="20%" align="center"><strong>' . $depen->motif .'</strong></td>
                        </tr>';
                        $depenglobal +=$depen->montant_depens;
                }
                    $them .= '<tr>
                        <td width="80%" align="center"><strong>TOTAL</strong></td>
                        <td width="20%" align="center"><strong> '.number_format($depenglobal, 0, '', ' ').'</strong></td>
                                    
                   </tr>';
                $them .= ' </tbody></table>';
                
                $them.= '<h2>DEPENSE TOTAL :'. number_format($depenglobal, 0, '', ' ').' </h2>';
                $them.= '<h2>CAISSE DE :'. $cai->first_name.' '. $cai->last_name.' </h2>';
                $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
                $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
                ob_end_clean();
                //Close and output PDF document
                $pdf->Output('example_016.pdf' . '', 'I');
                //============================================================+
                // END OF FILE
                //============================================================+
              
        }

          //tirage des depots
        public function recaptdepot($ckey)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);

              $date1 = $this->input->post('datedebut');
              $date2 = $this->input->post('datefin');
              $typ = $this->input->post('type');
              $gen = $this->input->post('genre');
              $nm = $this->input->post('nom');
              $comp = $this->input->post('_compag');
              $ncomp = $this->m_compagnies->getn($comp);
              $gid = $this->input->post('departgar');

              $uopera = roleattribut_guard_post_hint($this->entreprise->ekey);
              if ($uopera === NULL || $uopera === '') {
                  $uopera = $this->input->post('useropered');
              }

              $cai = $this->m_compte_user->cpuseres($uopera);

              $ngrd = $this->m_gare_depart->getno($gid);

                $dats = explode("-", $date1);
                    $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                    $dats1 = explode("-", $date2);
                  $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
              if (recette_role_is_saisie($this->session->agent->userole)) {
                $tridepo = $this->m_depot->tridepot_adjoint($this->entreprise->ekey, $gid, $uopera, $comp, $date1, $date2, $typ, $gen, $nm);
              } elseif (recette_role_is_validateur_adjoint($this->session->agent->userole)) {
                $tridepo = $this->m_depot->adtridepot($this->entreprise->ekey, $gid, $uopera, $comp, $date1, $date2, $gen, $nm);
              } elseif ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2') {
                $tridepo = $this->m_depot->tridepotadmin($this->entreprise->ekey, $gid, $comp, $date1, $date2, $typ, $gen, $nm);
              } elseif (recette_role_is_validateur_principal($this->session->agent->userole)) {
                $uopera = $this->input->post('useropered');
                $tridepo = $this->m_depot->valdtridepot($this->entreprise->ekey, $gid, $uopera, $date1, $date2, $typ, $gen, $nm, $comp);
              } else {
                $uopera = $this->input->post('useropered');
                $tridepo = $this->m_depot->valdtridepot($this->entreprise->ekey, $gid, $uopera, $date1, $date2, $typ, $gen, $nm, $comp);
              }

                $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                // set document information
                $pdf->SetCreator(PDF_CREATOR);
                $pdf->SetAuthor('NET SOLUTIONS');
                $pdf->SetTitle('LISTE-');
                $pdf->SetSubject('DEPOTS');
                $pdf->SetKeywords('--');
                
                $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
                // remove default header/footer
                $pdf->setPrintHeader(true);
                $pdf->setPrintFooter(false);
                
                // set default monospaced font
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
                $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                // set margins
                $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                
                
                // set auto page breaks
                $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
                
                // set image scale factor
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
                
                // set font
                
                
                // add a page
                $pdf->AddPage('L', 'A4', 0);
                
                // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                // GROUPE DE GAUCHE
                $pdf->SetFont('courier', '', 9);
                            
                $titre = '<h1 align="center">ETATS DES DEPOTS DE '.$ncomp->nom_compagnie.' '.$ngrd->garenom.' DU '. $days.' AU '.$days1.'</h1>';
                $them = '<table border="1" cellpadding="0">
                    <thead> 
                        <tr> 
                          <th width="10%" align="center"><strong>DATE</strong></th>
                          <th width="10%" align="center"><strong>TYPE</strong></th>
                          <th width="10%" align="center"><strong>GENRE</strong></th> 
                          <th width="20%" align="center"><strong>NOM</strong></th>
                          <th width="20%" align="center"><strong>COMMENTAIRE</strong></th>
                            <th width="20%" align="center"><strong>TOTAL</strong></th>

                        </tr>
                    </thead>
                    <tbody>';
                    $depotglobal = 0;
                foreach ($tridepo as $tridpo => $depot) {
                    $them .= '<tr>
                        <td width="10%" align="center"><strong>' . $depot->datedepot . '</strong></td>
                        <td width="10%" align="center"><strong>' . $depot->type_depot . '</strong></td>
                        <td width="10%" align="center"><strong>' . $depot->type_personnel . '</strong></td>
                        <td width="20%" align="center"><strong>' . $depot->nom_pre . '</strong></td>
                        <td width="20%" align="center"><strong>' . $depot->commentaire_depot . '</strong></td>
                        <td width="20%" align="center"><strong>' . $depot->montant_depot . '</strong></td>
                        
                        </tr>';
                        $depotglobal +=$depot->montant_depot;
                }
                $them .= '<tr>
                        <td width="70%" align="center"><strong>TOTAL</strong></td>
                        <td width="20%" align="center"><strong> '.number_format($depotglobal, 0, '', ' ').'</strong></td>
                                    
                   </tr>';
                $them .= ' </tbody></table>';
                
                $them.= '<h2>DEPOT TOTAL :'. number_format($depotglobal, 0, '', ' ').' </h2>';

                $them.= '<h2>CAISSE DE :'. $cai->first_name.' '. $cai->last_name.' </h2>';

                $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
                $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
                ob_end_clean();
                //Close and output PDF document
                $pdf->Output('example_017.pdf' . '', 'I');
              //============================================================+
              // END OF FILE
              //============================================================+
        }

        public function recaptautredepot($ckey)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);

              $date1 = $this->input->post('datedebut');
              $date2 = $this->input->post('datefin');
              $typ = $this->input->post('type');
              $gen = $this->input->post('genre');
              $nm = $this->input->post('nom');
              $gid = $this->input->post('departgar');
              $uopera = $this->input->post('useropered');

                $ngrd = $this->m_gare_depart->getno($gid);

              $cai = $this->m_compte_user->cpuseres($uopera);

                  $dats = explode("-", $date1);
                      $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                        $dats1 = explode("-", $date2);
                  $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
                $tridepo = $this->m_depot->valdautretridepot($this->entreprise->ekey, $gid, $uopera, $date1, $date2, $typ, $gen, $nm);

                $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                // set document information
                $pdf->SetCreator(PDF_CREATOR);
                $pdf->SetAuthor('NET SOLUTIONS');
                $pdf->SetTitle('LISTE-');
                $pdf->SetSubject('DEPOTS');
                $pdf->SetKeywords('--');
                
                $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
                // remove default header/footer
                $pdf->setPrintHeader(true);
                $pdf->setPrintFooter(false);
                
                // set default monospaced font
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
                $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                // set margins
                $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                
                
                // set auto page breaks
                $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
                
                // set image scale factor
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
                
                // set font
                
                
                // add a page
                $pdf->AddPage('L', 'A4', 0);
                
                // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                // GROUPE DE GAUCHE
                $pdf->SetFont('courier', '', 9);
                            
                $titre = '<h1 align="center">ETATS DES DEPOTS '.$ngrd->garenom.' DU '. $days .' AU '.$days1.'</h1>';
                $them = '<table border="1" cellpadding="0">
                    <thead> 
                        <tr> 
                          <th width="10%" align="center"><strong>DATE</strong></th>
                          <th width="10%" align="center"><strong>TYPE</strong></th>
                          <th width="10%" align="center"><strong>GENRE</strong></th> 
                          <th width="20%" align="center"><strong>NOM</strong></th>
                          <th width="20%" align="center"><strong>TOTAL</strong></th>
                          <th width="20%" align="center"><strong>COMMENTAIRE</strong></th>
                        </tr>
                    </thead>
                    <tbody>';
                    $depotglobal = 0;
                foreach ($tridepo as $tridpo => $depot) {
                    $them .= '<tr>
                        <td width="10%" align="center"><strong>' . $depot->datedepot . '</strong></td>
                        <td width="10%" align="center"><strong>' . $depot->type_depot . '</strong></td>
                        <td width="10%" align="center"><strong>' . $depot->genre_depot . '</strong></td>
                        <td width="20%" align="center"><strong>' . $depot->nom_pre . '</strong></td>
                        <td width="20%" align="center"><strong>' . $depot->montant_depot . '</strong></td>
                        <td width="20%" align="center"><strong>' . $depot->commentaire_depot . '</strong></td>
                        </tr>';
                        $depotglobal +=$depot->montant_depot;
                }
                
                $them .= ' </tbody></table>';
                
                $them.= '<h2>DEPOT TOTAL :'. number_format($depotglobal, 0, '', ' ').' </h2>';
                $them.= '<h2>CAISSE DE :'. $cai->first_name.' '. $cai->last_name.' </h2>';

                $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
                $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
                ob_end_clean();
                //Close and output PDF document
                $pdf->Output('example_017.pdf' . '', 'I');
        }


        public function ficheinventaire($ckey, $gd, $usename, $cpid)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
                $ddbt = $this->input->post('dated');
                $dfin = $this->input->post('datef');
                $comp = $this->input->post('_compag');
                $gid = $this->input->post('departgar');
                $ncomp = $this->m_compagnies->getn($comp);
                $dats = explode("-", $ddbt);
                          $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                            $dats1 = explode("-", $dfin);
                          $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
                $triversements = $this->m_passager->versfiltre($this->entreprise->ekey, $gid, $ddbt, $dfin, $comp, $cpid);
                $triversenonp = $this->m_non_passager->versefiltr($this->entreprise->ekey, $gid, $ddbt, $dfin, $comp, $cpid);
                $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('RECAPT');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('P', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                          
              $titre = '<h1 align="center">FICHES D\'INVENTAIRE '.$ncomp->nom_compagnie.' DU '. $days.' AU '.$days1.' </h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead> 
                      <tr> 
                        <th width="15%" align="left"><strong>DATE VALIDATION</strong></th>
                        <th width="20%" align="left"><strong>LIGNE</strong></th>
                        <th width="20%" align="left"><strong>MONTANT</strong></th>
                        </tr>
                  </thead>
                  <tbody>';
                  $etatversement = 0;
                  $etats = 0;
              foreach ($triversements as $trier => $item) {
                  $them .= '<tr>
                      <td width="15%" align="left"><strong>'.$item->datep_create.'</strong></td>
                      <td width="20%" align="center"><strong>' . $item->nom_ligne . '</strong></td>
                      <td width="20%" align="right"><strong>' . number_format($item->total, 0, '', ' ') . '</strong></td>
                      </tr>';
                            $etatversement += $item->total;
              }

              foreach ($triversenonp as $triversenon => $triversen) {
                $aler = explode("-", $triversen->nom_ligne);
                    $allerretour = $aler[1]. '-' .$aler[0];
                $them .= '<tr>
                    <td width="15%" align="left"><strong>'.$triversen->datevente.'</strong></td>
                    <td width="20%" align="center"><strong>' . $allerretour . '</strong></td>
                    <td width="20%" align="right"><strong>' . number_format($triversen->totalr, 0, '', ' ') . '</strong></td>
                    </tr>';
                    $etats += $triversen->totalr;
            }
            $them .= '<tr>
                        <td width="35%" align="left"><strong>TOTAL</strong></td>
                        <td width="20%" align="right"><strong> '.number_format($etatversement + $etats, 0, '', ' ').'</strong></td>

                   </tr>';
              $them .= ' </tbody></table>';
              $them.= '<h2>SOMME:'. number_format($etatversement + $etats, 0, '', ' ') .' </h2>';

              $them.= '<h2>'.$usename.', '.$gd.' le '. mdate("%d/%m/%Y", now('UTC')) .' </h2>';
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_014.pdf' . '', 'I');
              //============================================================+
              // END OF FILE
              //============================================================+
        }
        
        //inventaire bagages

        public function fiches($ckey, $gd, $usename, $cpid)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
                $ddbt = $this->input->post('dated');
                $dfin = $this->input->post('datef');
                $comp = $this->input->post('_compag');
                $gid = $this->input->post('departgar');
                $ncomp = $this->m_compagnies->getn($comp);
                $dats = explode("-", $ddbt);
                    $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                    $dats1 = explode("-", $dfin);
                    $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
                $triversementsb = $this->m_bagage->filtrebag($this->entreprise->ekey, $gid, $ddbt, $dfin, $comp, $cpid);

                $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('RECAPT');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('P', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                          
              $titre = '<h1 align="center">FICHES D\'INVENTAIRE '.$ncomp->nom_compagnie.' DU '. $days.' AU '.$days1.' </h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead> 
                      <tr> 
                        <th width="15%" align="left"><strong>DATE VALIDATION</strong></th>
                        <th width="20%" align="left"><strong>LIGNE</strong></th>
                        <th width="20%" align="left"><strong>MONTANT</strong></th>
                        </tr>
                  </thead>
                  <tbody>';
                  $etatversement = 0;
                  $etats = 0;
              foreach ($triversementsb as $trier => $itemg) {
                  $them .= '<tr>
                      <td width="15%" align="left"><strong>'.$itemg->date_create.'</strong></td>
                      <td width="20%" align="center"><strong>' . $itemg->nom_ligne . '</strong></td>
                      <td width="20%" align="right"><strong>' . number_format($itemg->bagtotal, 0, '', ' ') . '</strong></td>
                      </tr>';
                            $etatversement += $itemg->bagtotal;
              }
            $them .= '<tr>
                        <td width="35%" align="left"><strong>TOTAL</strong></td>
                        <td width="20%" align="right"><strong> '.number_format($etatversement, 0, '', ' ').'</strong></td>

                   </tr>';
              $them .= ' </tbody></table>';
              $them.= '<h2>SOMME:'. number_format($etatversement, 0, '', ' ') .' </h2>';

              $them.= '<h2>'.$usename.', '.$gd.' le '. mdate("%d/%m/%Y", now('UTC')) .' </h2>';
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_014.pdf' . '', 'I');
              //============================================================+
              // END OF FILE
              //============================================================+
        }

        public function ficheinventaireesc($ckey, $gd, $usename, $cpid)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
                $ddbt = $this->input->post('dated');
                $dfin = $this->input->post('datef');
                $comp = $this->input->post('_compag');
                $gid = $this->input->post('departgar');
                $ncomp = $this->m_compagnies->getn($comp);
                $dats = explode("-", $ddbt);
                          $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                            $dats1 = explode("-", $dfin);
                          $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
                $triversements = $this->m_escalclients->versfiltre($this->entreprise->ekey, $gid, $ddbt, $dfin, $comp, $cpid);
                $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('RECAPT');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('P', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                          
              $titre = '<h1 align="center">FICHES D\'INVENTAIRE '.$ncomp->nom_compagnie.' DU '. $days.' AU '.$days1.' </h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead> 
                      <tr> 
                        <th width="15%" align="left"><strong>DATE VALIDATION</strong></th>
                        <th width="20%" align="left"><strong>LIGNE</strong></th>
                        <th width="20%" align="left"><strong>MONTANT</strong></th>
                        </tr>
                  </thead>
                  <tbody>';
                  $etatversement = 0;
                  $etats = 0;
              foreach ($triversements as $trier => $item) {
                  $them .= '<tr>
                      <td width="15%" align="left"><strong>'.$item->dateescal.'</strong></td>
                      <td width="20%" align="center"><strong>' . $item->nom_ligne . '</strong></td>
                      <td width="20%" align="right"><strong>' . number_format($item->total, 0, '', ' ') . '</strong></td>
                      </tr>';
                            $etatversement += $item->total;
              }

            
                $them .= '<tr>
                        <td width="35%" align="left"><strong>TOTAL</strong></td>
                        <td width="20%" align="right"><strong> '.number_format($etatversement, 0, '', ' ').'</strong></td>

                   </tr>';
                $them .= ' </tbody></table>';
                $them.= '<h2>SOMME:'. number_format($etatversement, 0, '', ' ') .' </h2>';

              $them.= '<h2>'.$usename.', '.$gd.' le '. mdate("%d/%m/%Y", now('UTC')) .' </h2>';
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_014.pdf' . '', 'I');
              //============================================================+
              // END OF FILE
              //============================================================+
        }
          //passager vendu par jour
          //tirage de liste
        public function passagervendu($ckey, $gd, $cpu)
        {
             
             $dd = $this->input->post('debutdate');
             $df = $this->input->post('findate');
            
             $comp = $this->input->post('_compag');
             $ncomp = $this->m_compagnies->getn($comp);

                $dats = explode("-", $dd);
                          $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                            $dats1 = explode("-", $df);
                          $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
             $this->entreprise = $this->m_entreprises->get_key($ckey);
              $onvente = $this->m_passager->ventejour($this->entreprise->ekey, $gd, $cpu, $dd, $df);
              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('RESERVATIONS');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              //$pdf->AddPage();
              $pdf->AddPage('L', 'A4', 0);
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              $pdf->SetFont('courier', '', 9);
             
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
              
              $titre = '<h1 align="center">PASSAGERS VENDU DU '. $days.' AU '.$days1.'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead> 
                      <tr> 
                          <th width="7%" align="center"><strong>SIEGE </strong></th>
                          <th width="10%" align="center"><strong>CODE</strong></th>
                          <th width="15%" align="center"><strong>ITINERAIRE</strong></th>
                          <th align="center"><strong>QUARTIER</strong></th> 
                          <th width="20%" align="center"><strong>NOM PASSAGER</strong></th>
                          <th align="center"><strong>CONTACT</strong></th> 
 
                      </tr>
                  </thead>
                  <tbody>';
              foreach ($onvente as $departh => $element) {
                  $them .= '<tr>
                      <td width="7%" align="center"><strong>' . $element->num_siege_categorie . '</strong></td>
                      <td width="10%" align="left"><strong>' . $element->code_ticket . '</strong></td>
                      <td width="15%"align="left"><strong>' . $element->nom_ligne . '</strong></td>
                      <td align="left"><strong>' . $element->quart . '</strong></td>
                      <td width="20%" align="left"><strong>' . $element->nom_client . '&nbsp;&nbsp;' . $element->prenom_client . '</strong></td>
                      <td align="left"><strong>' . $element->contact_client . '</strong></td>
                       </tr>';
              }
              $them .= ' </tbody></table>';
              
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_011.pdf' . '', 'I');
              //============================================================+
              // END OF FILE
              //============================================================+
        }

        public function passagervenduesc($ckey, $gd, $cpu)
        {
             
             $dd = $this->input->post('debutdate');
             $df = $this->input->post('findate');
            
             $comp = $this->input->post('_compag');
             $ncomp = $this->m_compagnies->getn($comp);

                $dats = explode("-", $dd);
                $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                $dats1 = explode("-", $df);
                $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
             $this->entreprise = $this->m_entreprises->get_key($ckey);
              $onvente = $this->m_escalclients->ventejour($this->entreprise->ekey, $gd, $cpu, $dd, $df);
              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('RESERVATIONS');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              //$pdf->AddPage();
              $pdf->AddPage('L', 'A4', 0);
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              $pdf->SetFont('courier', '', 9);
             
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
              
              $titre = '<h1 align="center">PASSAGERS VENDU DU '. $days.' AU '.$days1.'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead> 
                      <tr> 
                          
                          <th width="17%" align="center"><strong>CODE</strong></th>
                          <th width="15%" align="center"><strong>ITINERAIRE</strong></th>
                          <th align="center"><strong>QUARTIER</strong></th> 
                          <th width="20%" align="center"><strong>NOM PASSAGER</strong></th>
                          <th align="center"><strong>CONTACT</strong></th> 
 
                      </tr>
                  </thead>
                  <tbody>';
              foreach ($onvente as $departh => $element) {
                  $them .= '<tr>
                      <td width="17%" align="left"><strong>' . $element->idclescal . '</strong></td>
                      <td width="15%"align="left"><strong>' . $element->nom_ligne . '</strong></td>
                      <td align="left"><strong>' . $element->quartier_escal. '</strong></td>
                      <td width="20%" align="left"><strong>' . $element->nom_client . '&nbsp;&nbsp;' . $element->prenom_client . '</strong></td>
                      <td align="left"><strong>' . $element->contact_client . '</strong></td>
                       </tr>';
              }
              $them .= ' </tbody></table>';
              
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_011.pdf' . '', 'I');
              //============================================================+
              // END OF FILE
              //============================================================+
        }
         //tirage des recette depense depot par chef de ligne
        public function recettetris($ckey, $g, $cai, $us)
        {
           $this->entreprise = $this->m_entreprises->get_key($ckey);
             $date1 = $this->input->post('debutdate');
             $date2 = $this->input->post('findate');
             $typ = $this->input->post('typerecette');
              $comp = $this->input->post('_compag');
             $ncomp = $this->m_compagnies->getn($comp);
              $dats = explode("-", $date1);
                          $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                            $dats1 = explode("-", $date2);
                          $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
            $trirects = $this->m_recette->trisrecet($this->entreprise->ekey, $g, $cai, $us, $date1, $date2, $comp, $typ);
             $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
             // set document information
             $pdf->SetCreator(PDF_CREATOR);
             $pdf->SetAuthor('NET SOLUTIONS');
             $pdf->SetTitle('LISTE-');
             $pdf->SetSubject('RESERVATIONS');
             $pdf->SetKeywords('--');
             
             $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
             // remove default header/footer
             $pdf->setPrintHeader(true);
             $pdf->setPrintFooter(false);
             
             // set default monospaced font
             $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
             $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
             $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
             // set margins
             $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
             
             
             // set auto page breaks
             $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
             
             // set image scale factor
             $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
             
             // set font
             
             
             // add a page
             $pdf->AddPage('L', 'A4', 0);
             
             // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
             // GROUPE DE GAUCHE
             $pdf->SetFont('courier', 'B', 10);
                         
             $titre = '<h1 align="center">ETATS DES RECETTES DE '.$ncomp->nom_compagnie.' DU '. $days.' AU '.$days1.'</h1>';
             $them = '<table border="1" cellpadding="0">
                 <thead> 
                     <tr> 
                       <th width="10%" align="center"><strong>DATE</strong></th>
                       <th width="10%" align="center"><strong>TYPE</strong></th>
                       <th width="10%" align="center"><strong>OPERATEUR</strong></th> 
                       <th width="15%" align="center"><strong>NOM</strong></th>
                       <th width="15%" align="center"><strong>TOTAL</strong></th>
                       <th width="20%" align="center"><strong>COMMENTAIRE</strong></th>
                     </tr>
                 </thead>
                 <tbody>';
                 $recetglobale = 0;
                 $pdf->SetFont('courier', '', 9);
             foreach ($trirects as $tris => $recte) {
                 $them .= '<tr>
                     <td width="10%" align="center"><strong>' . $recte->date_recet . '</strong></td>
                     <td width="10%" align="center"><strong>' . $recte->type_recet . '</strong></td>
                     <td width="10%" align="center"><strong>' . $recte->username . '</strong></td>
                     <td width="15%" align="center"><strong>' . $recte->nom . '</strong></td>
                     <td width="15%" align="center"><strong>' . $recte->montant_recet . '</strong></td>
                     <td width="20%" align="center"><strong>' . $recte->commentaire_recet .'</strong></td>
                     </tr>';
                      $recetglobale +=$recte->montant_recet;
             }
             
             $them .= ' </tbody></table>';
             
             $them.= '<h2>RECETTE TOTAL :'. number_format($recetglobale, 0, '', ' ').' </h2>';
             $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
             $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
             ob_end_clean();
             //Close and output PDF document
             $pdf->Output('example_015.pdf' . '', 'I');
             
        }

        public function depensetris($ckey, $g, $cai, $us)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
              $date1 = $this->input->post('debutdate');
              $date2 = $this->input->post('findate');
              $typ = $this->input->post('typedepense');
                $comp = $this->input->post('_compag');
              $ncomp = $this->m_compagnies->getn($comp);

              $profil = $this->db->query("SELECT userole FROM attributions_role WHERE roleattribut = '$us' LIMIT 1")->row();
              $userole = $profil ? $profil->userole : '';
                
                $dats = explode("-", $date1);
                          $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                            $dats1 = explode("-", $date2);
                          $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
                $tridepns = $this->m_depense->trisdepens_par_profil($this->entreprise->ekey, $g, $cai, $us, $userole, $date1, $date2, $comp, $typ);
                $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                // set document information
                $pdf->SetCreator(PDF_CREATOR);
                $pdf->SetAuthor('NET SOLUTIONS');
                $pdf->SetTitle('LISTE-');
                $pdf->SetSubject('RESERVATIONS');
                $pdf->SetKeywords('--');
                
                $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
                // remove default header/footer
                $pdf->setPrintHeader(true);
                $pdf->setPrintFooter(false);
                
                // set default monospaced font
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
                $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                // set margins
                $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                
                
                // set auto page breaks
                $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
                
                // set image scale factor
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
                
                // set font
                
                
                // add a page
                $pdf->AddPage('L', 'A4', 0);
                
                // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                // GROUPE DE GAUCHE
                $pdf->SetFont('courier', '', 9);
                            
                $titre = '<h1 align="center">ETATS DES DEPENSES DE '.$ncomp->nom_compagnie.' DU '. $days.' AU '.$days1.'</h1>';
                $them = '<table border="1" cellpadding="0">
                    <thead> 
                        <tr> 
                          <th width="10%" align="center"><strong>DATE</strong></th>
                          <th width="10%" align="center"><strong>TYPE</strong></th>
                          <th width="10%" align="center"><strong>OPERATEUR</strong></th> 
                          <th width="15%" align="center"><strong>NOM</strong></th>
                          <th width="15%" align="center"><strong>TOTAL</strong></th>
                          <th width="20%" align="center"><strong>COMMENTAIRE</strong></th>
                          <th width="20%" align="center"><strong>MOTIF</strong></th>
                        </tr>
                    </thead>
                    <tbody>';
                    $depenseglobal = 0;
                foreach ($tridepns as $trid => $depn) {
                    $them .= '<tr>
                        <td width="10%" align="center"><strong>' . $depn->date_depens . '</strong></td>
                        <td width="10%" align="center"><strong>' . $depn->type_depense . '</strong></td>
                        <td width="10%" align="center"><strong>' . $depn->username . '</strong></td>
                        <td width="15%" align="center"><strong>' . $depn->nom_perso . '</strong></td>
                        <td width="15%" align="center"><strong>' . $depn->montant_depens . '</strong></td>
                        <td width="20%" align="center"><strong>' . $depn->commentaire .'</strong></td>
                        <td width="20%" align="center"><strong>' . $depn->motif .'</strong></td>
                        </tr>';
                         $depenglobal +=$depn->montant_depens;
                }
                
                $them .= ' </tbody></table>';
                
                $them.= '<h2>DEPENSE TOTAL :'. number_format($depenglobal, 0, '', ' ').' </h2>';
                $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
                $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
                ob_end_clean();
                //Close and output PDF document
                $pdf->Output('example_016.pdf' . '', 'I');
              //============================================================+
              // END OF FILE
              //============================================================+
          
        }
        public function depottris($ckey, $g, $cai, $us)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
              $date1 = $this->input->post('debutdate');
              $date2 = $this->input->post('findate');
              $typ = $this->input->post('typedepot');
              $comp = $this->input->post('_compag');
              $ncomp = $this->m_compagnies->getn($comp);

              $profil = $this->db->query("SELECT userole FROM attributions_role WHERE roleattribut = '$us' LIMIT 1")->row();
              $userole = $profil ? $profil->userole : '';

                $dats = explode("-", $date1);
                          $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                            $dats1 = explode("-", $date2);
                          $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
            $tridepo = $this->m_depot->trisdepot_par_profil($this->entreprise->ekey, $g, $cai, $us, $userole, $date1, $date2, $comp, $typ);

              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('DEPOTS');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('L', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                          
              $titre = '<h1 align="center">ETATS DES DEPOTS DE '.$ncomp->nom_compagnie.' DU '. $days.' AU '.$days1.'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead> 
                      <tr> 
                        <th width="10%" align="center"><strong>DATE</strong></th>
                        <th width="10%" align="center"><strong>TYPE</strong></th>
                        <th width="10%" align="center"><strong>OPERATEUR</strong></th> 
                        <th width="20%" align="center"><strong>NOM</strong></th>

                        <th width="20%" align="center"><strong>COMMENTAIRE</strong></th>
                        <th width="20%" align="center"><strong>TOTAL</strong></th>
                      </tr>
                  </thead>
                  <tbody>';
                  $depotglobal = 0;
              foreach ($tridepo as $tridpo => $depot) {
                  $them .= '<tr>
                      <td width="10%" align="center"><strong>' . $depot->datedepot . '</strong></td>
                      <td width="10%" align="center"><strong>' . $depot->type_depot . '</strong></td>
                      <td width="10%" align="center"><strong>' . $depot->username . '</strong></td>
                      <td width="20%" align="center"><strong>' . $depot->nom_pre . '</strong></td>

                      <td width="20%" align="center"><strong>' . $depot->commentaire_depot . '</strong></td>
                      <td width="20%" align="center"><strong>' . $depot->montant_depot . '</strong></td>
                      </tr>';
                      $depotglobal +=$depot->montant_depot;
              }
              
              $them .= ' </tbody></table>';
              
              $them.= '<h2>DEPOT TOTAL :'. number_format($depotglobal, 0, '', ' ').' </h2>';
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_017.pdf' . '', 'I');
            //============================================================+
            // END OF FILE
            //============================================================+
         
        }

        public function recettetries($ckey, $g, $cai)
        {
           $this->entreprise = $this->m_entreprises->get_key($ckey);
             $date1 = $this->input->post('debutdate');
             $date2 = $this->input->post('findate');
             $typ = $this->input->post('typerecette');
             $us = $this->input->post('opera');
              $comp = $this->input->post('_compag');
             $ncomp = $this->m_compagnies->getn($comp);

                $dats = explode("-", $date1);
                    $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                            $dats1 = explode("-", $date2);
                    $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
               $trirects = $this->m_recette->trisrecet($this->entreprise->ekey, $g, $comp, $cai, $us, $date1, $date2, $typ);
             $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
             // set document information
             $pdf->SetCreator(PDF_CREATOR);
             $pdf->SetAuthor('NET SOLUTIONS');
             $pdf->SetTitle('LISTE-');
             $pdf->SetSubject('RESERVATIONS');
             $pdf->SetKeywords('--');
             
             $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
             // remove default header/footer
             $pdf->setPrintHeader(true);
             $pdf->setPrintFooter(false);
             
             // set default monospaced font
             $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
             $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
             $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
             // set margins
             $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
             
             
             // set auto page breaks
             $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
             
             // set image scale factor
             $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
             
             // set font
             
             
             // add a page
             $pdf->AddPage('L', 'A4', 0);
             
             // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
             // GROUPE DE GAUCHE
             $pdf->SetFont('courier', 'B', 10);
                         
             $titre = '<h1 align="center">ETATS DES RECETTES DE '.$ncomp->nom_compagnie.' DU '. $days .' AU '.$days1.'</h1>';
             $them = '<table border="1" cellpadding="0">
                 <thead> 
                     <tr> 
                       <th width="10%" align="center"><strong>DATE</strong></th>
                       <th width="10%" align="center"><strong>TYPE</strong></th>
                       <th width="10%" align="center"><strong>OPERATEUR</strong></th> 
                       <th width="15%" align="center"><strong>NOM</strong></th>

                       <th width="20%" align="center"><strong>COMMENTAIRE</strong></th>
                       <th width="15%" align="center"><strong>TOTAL</strong></th>
                     </tr>
                 </thead>
                 <tbody>';
                 $recetglobale = 0;
                 $pdf->SetFont('courier', '', 9);
             foreach ($trirects as $tris => $recte) {
                 $them .= '<tr>
                     <td width="10%" align="center"><strong>' . $recte->date_recet . '</strong></td>
                     <td width="10%" align="center"><strong>' . $recte->type_recet . '</strong></td>
                     <td width="10%" align="center"><strong>' . $recte->username . '</strong></td>
                     <td width="15%" align="center"><strong>' . $recte->nom . '</strong></td>

                     <td width="20%" align="center"><strong>' . $recte->commentaire_recet .'</strong></td>
                     <td width="15%" align="center"><strong>' . $recte->montant_recet . '</strong></td>
                     </tr>';
                      $recetglobale +=$recte->montant_recet;
             }
             
             $them .= ' </tbody></table>';
             
             $them.= '<h2>RECETTE TOTAL :'. number_format($recetglobale, 0, '', ' ').' </h2>';
             $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
             $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
             ob_end_clean();
             //Close and output PDF document
             $pdf->Output('example_015.pdf' . '', 'I');
             
        }
        

        public function depensetries($ckey, $g, $cai)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
              $date1 = $this->input->post('debutdate');
              $date2 = $this->input->post('findate');
              $typ = $this->input->post('typedepense');
              $us = $this->input->post('opera');
              $comp = $this->input->post('_compag');
            $ncomp = $this->m_compagnies->getn($comp);
                  $dats = explode("-", $date1);
                  $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                  $dats1 = explode("-", $date2);
                  $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
                $tridepns = $this->m_depense->trisdepens($this->entreprise->ekey, $g, $cai, $us, $date1, $date2, $comp, $typ);
                $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                // set document information
                $pdf->SetCreator(PDF_CREATOR);
                $pdf->SetAuthor('NET SOLUTIONS');
                $pdf->SetTitle('LISTE-');
                $pdf->SetSubject('RESERVATIONS');
                $pdf->SetKeywords('--');
                
                $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
                // remove default header/footer
                $pdf->setPrintHeader(true);
                $pdf->setPrintFooter(false);
                
                // set default monospaced font
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
                $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                // set margins
                $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                
                
                // set auto page breaks
                $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
                
                // set image scale factor
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
                
                // set font
                
                
                // add a page
                $pdf->AddPage('L', 'A4', 0);
                
                // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                // GROUPE DE GAUCHE
                $pdf->SetFont('courier', '', 9);
                            
                $titre = '<h1 align="center">ETATS DES DEPENSES DE '.$ncomp->nom_compagnie.' DU '. $days .' AU '.$days1.'</h1>';
                $them = '<table border="1" cellpadding="0">
                    <thead> 
                        <tr> 
                          <th width="10%" align="center"><strong>DATE</strong></th>
                          <th width="10%" align="center"><strong>TYPE</strong></th>
                          <th width="10%" align="center"><strong>OPERATEUR</strong></th> 
                          <th width="15%" align="center"><strong>NOM</strong></th>
                          <th width="20%" align="center"><strong>COMMENTAIRE</strong></th>
                          <th width="20%" align="center"><strong>MOTIF</strong></th>

                          <th width="15%" align="center"><strong>TOTAL</strong></th>
                        </tr>
                    </thead>
                    <tbody>';
                    $depenseglobal = 0;
                foreach ($tridepns as $trid => $depn) {
                    $them .= '<tr>
                        <td width="10%" align="center"><strong>' . $depn->date_depens . '</strong></td>
                        <td width="10%" align="center"><strong>' . $depn->type_depense . '</strong></td>
                        <td width="10%" align="center"><strong>' . $depn->username . '</strong></td>
                        <td width="15%" align="center"><strong>' . $depn->nom_perso . '</strong></td>
                        <td width="20%" align="center"><strong>' . $depn->commentaire .'</strong></td>
                        <td width="20%" align="center"><strong>' . $depn->motif .'</strong></td>

                        <td width="15%" align="center"><strong>' . $depn->montant_depens . '</strong></td>
                        </tr>';
                         $depenglobal +=$depn->montant_depens;
                }
                
                $them .= ' </tbody></table>';
                
                $them.= '<h2>DEPENSE TOTAL :'. number_format($depenglobal, 0, '', ' ').' </h2>';
                $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
                $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
                ob_end_clean();
                //Close and output PDF document
                $pdf->Output('example_016.pdf' . '', 'I');
              //============================================================+
              // END OF FILE
              //============================================================+
          
        }

        public function depottries($ckey, $g, $cai)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
              $date1 = $this->input->post('debutdate');
              $date2 = $this->input->post('findate');
              $typ = $this->input->post('typedepot');
              $us = $this->input->post('opera');

              $comp = $this->input->post('_compag');

              $dats = explode("-", $date1);
                          $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                            $dats1 = explode("-", $date2);
                          $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
              $ncomp = $this->m_compagnies->getn($comp);
              $tridepo = $this->m_depot->trisdepot($this->entreprise->ekey, $g, $comp, $cai, $us, $date1, $date2, $typ);

              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('DEPOTS');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('L', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                          
              $titre = '<h1 align="center">ETATS DES DEPOTS DE '.$ncomp->nom_compagnie.' DU '. $days.' AU '.$days1.'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead> 
                      <tr> 
                        <th width="10%" align="center"><strong>DATE</strong></th>
                        <th width="10%" align="center"><strong>TYPE</strong></th>
                        <th width="10%" align="center"><strong>OPERATEUR</strong></th> 
                        <th width="20%" align="center"><strong>NOM</strong></th>
                        
                        <th width="20%" align="center"><strong>COMMENTAIRE</strong></th>
                        <th width="20%" align="center"><strong>TOTAL</strong></th>
                      </tr>
                  </thead>
                  <tbody>';
                  $depotglobal = 0;
              foreach ($tridepo as $tridpo => $depot) {
                  $them .= '<tr>
                      <td width="10%" align="center"><strong>' . $depot->datedepot . '</strong></td>
                      <td width="10%" align="center"><strong>' . $depot->type_depot . '</strong></td>
                      <td width="10%" align="center"><strong>' . $depot->username . '</strong></td>
                      <td width="20%" align="center"><strong>' . $depot->nom_pre . '</strong></td>

                      <td width="20%" align="center"><strong>' . $depot->commentaire_depot . '</strong></td>
                      <td width="20%" align="center"><strong>' . $depot->montant_depot . '</strong></td>
                      </tr>';
                      $depotglobal +=$depot->montant_depot;
              }
              
              $them .= ' </tbody></table>';
              
              $them.= '<h2>DEPOT TOTAL :'. number_format($depotglobal, 0, '', ' ').' </h2>';
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_017.pdf' . '', 'I');
            //============================================================+
            // END OF FILE
            //============================================================+
         
        }
         //tri versement bancaire

        public function versementbanq($ckey)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);

            $date1 = $this->input->post('datedebut');
            $date2 = $this->input->post('datefin');
            $typ = $this->input->post('type');
            $gen = $this->input->post('genre');
            $nm = $this->input->post('nom');
            $comp = $this->input->post('_compag');
            $gid = $this->input->post('gareconnect');
              $atr = roleattribut_guard_post_hint($this->entreprise->ekey);
              $ncomp = $this->m_compagnies->getn($comp);

              $dats = explode("-", $date1);
              $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
              $dats1 = explode("-", $date2);
              $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
              $triverseme = $this->m_versements->versembanque($this->entreprise->ekey, $comp, $gid, $atr, $date1, $date2, $gen, $nm);

              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('DEPOTS');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('L', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                          
              $titre = '<h1 align="center">ETATS DES DEPOTS DE '.$ncomp->nom_compagnie.' DU '. $days .' AU '.$days1.'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead> 
                      <tr> 
                        <th width="10%" align="left"><strong>DATE</strong></th>
                        <th width="10%" align="left"><strong>TYPE</strong></th>
                        <th width="10%" align="left"><strong>GENRE</strong></th> 
                        <th width="10%" align="left"><strong>NOM</strong></th>
                        <th width="20%" align="left"><strong>BORDEREAU</strong></th>
                        <th width="20%" align="left"><strong>COMMENTAIRE</strong></th>

                        <th width="20%" align="left"><strong>MONTANT</strong></th>

                      </tr>
                  </thead>
                  <tbody>';
                  $depotglobal = 0;
              foreach ($triverseme as $triversem => $triversemen) {
                  $them .= '<tr>
                      <td width="10%" align="left"><strong>' . $triversemen->date_versement . '</strong></td>
                      <td width="10%" align="left"><strong>' . $triversemen->type_versement . '</strong></td>
                      <td width="10%" align="left"><strong>' . $triversemen->genre_depot . '</strong></td>
                      <td width="10%" align="left"><strong>' . $triversemen->nom_beneficiaire . '</strong></td>
                      <td width="20%" align="left"><strong>' . $triversemen->bordereau_verser . '</strong></td>
                      <td width="20%" align="left"><strong>' . $triversemen->commentaire . '</strong></td>

                      <td width="20%" align="left"><strong>' . $triversemen->montant_verser . '</strong></td>
                      </tr>';
                      $depotglobal +=$triversemen->montant_verser;
              }
              
              $them .= ' </tbody></table>';
              
              $them.= '<h2>VERSEMENT TOTAL :'. number_format($depotglobal, 0, '', ' ').' </h2>';
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_017.pdf' . '', 'I');
            
        }

        public function versementfour($ckey)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);

            $date1 = $this->input->post('datedebut');
            $date2 = $this->input->post('datefin');
            $typ = $this->input->post('type');
            $gen = $this->input->post('genre');
            $nm = $this->input->post('nom');
            $comp = $this->input->post('_compag');
            $gid = $this->input->post('departgar');
            $ncomp = $this->m_compagnies->getn($comp);

              $dats = explode("-", $date1);
                $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                $dats1 = explode("-", $date2);
                $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
              
              $triverseme = $this->m_versements->versemfourni($this->entreprise->ekey, $comp, $gid, $atr, $date1, $date2, $gen, $nm);

              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('DEPOTS');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('L', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                          
              $titre = '<h1 align="center">ETATS DES DEPOTS DE '.$ncomp->nom_compagnie.' DU '. $days .' AU '.$days1.'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead> 
                      <tr> 
                        <th width="10%" align="left"><strong>DATE</strong></th>
                        <th width="10%" align="left"><strong>TYPE</strong></th>
                        <th width="10%" align="left"><strong>GENRE</strong></th> 
                        <th width="10%" align="left"><strong>NOM</strong></th>
                        <th width="20%" align="left"><strong>BORDEREAU</strong></th>
                        <th width="20%" align="left"><strong>COMMENTAIRE</strong></th>

                        <th width="20%" align="left"><strong>MONTANT</strong></th>

                      </tr>
                  </thead>
                  <tbody>';
                  $depotglobal = 0;
              foreach ($triverseme as $triversem => $triversemen) {
                  $them .= '<tr>
                      <td width="10%" align="left"><strong>' . $triversemen->date_versement . '</strong></td>
                      <td width="10%" align="left"><strong>' . $triversemen->type_versement . '</strong></td>
                      <td width="10%" align="left"><strong>' . $triversemen->genre_depense . '</strong></td>
                      <td width="10%" align="left"><strong>' . $triversemen->nom_beneficiaire . '</strong></td>
                      <td width="20%" align="left"><strong>' . $triversemen->bordereau_verser . '</strong></td>
                      <td width="20%" align="left"><strong>' . $triversemen->commentaire . '</strong></td>

                      <td width="20%" align="left"><strong>' . $triversemen->montant_verser . '</strong></td>
                      </tr>';
                      $depotglobal +=$triversemen->montant_verser;
              }
              
              $them .= ' </tbody></table>';
              
              $them.= '<h2>VERSEMENT TOTAL :'. number_format($depotglobal, 0, '', ' ').' </h2>';
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_017.pdf' . '', 'I');
            
        }

        public function bon($ckey){
            
            $db = $this->input->post('debutdate');
            
            $df = $this->input->post('findate');
            $gd = $this->input->post('stop');
            $sg = $this->input->post('sousgd');

            $this->entreprise = $this->m_entreprises->get_key($ckey);
             $onbon = $this->m_bon_millitaire->voirliste($this->entreprise->ekey, $db, $df, $gd, $sg);
             
             $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
             

             // set document information
             $pdf->SetCreator(PDF_CREATOR);
             $pdf->SetAuthor('NET SOLUTIONS');
             $pdf->SetTitle('LISTE-');
             $pdf->SetSubject('RESERVATIONS');
             $pdf->SetKeywords('--');
             
             $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
             // remove default header/footer
             $pdf->setPrintHeader(true);
             $pdf->setPrintFooter(false);
             
             // set default monospaced font
             $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
             $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
             $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
             // set margins
             $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
             
             
             // set auto page breaks
             $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
             
             // set image scale factor
             $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
             
             // set font
             
             
             // add a page
             //$pdf->AddPage();
             $pdf->AddPage('L', 'A4', 0);
             // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
             // GROUPE DE GAUCHE
             $pdf->SetFont('courier', '', 9);
             
             $titre = '<h1 align="center"> LISTE DES BONS </h1>';
             $them = '<table border="1" cellpadding="0">
                 <thead> 
                    <tr> 
                        <th width="10%" align="center"><strong>DATE</strong></th>
                         <th width="10%" align="center"><strong>N° BON </strong></th>
                         <th width="7%" align="center"><strong>CODE BON</strong></th>
                         <th width="15%" align="center"><strong>TRAJET</strong></th> 
                         <th width="20%" align="center"><strong>NOM ET PRENOM</strong></th>
                         <th align="center"><strong>CONTACT</strong></th> 
                         <th align="center"><strong> REF CINB</strong></th>
                    </tr>
                 </thead>
                 <tbody>';
             foreach ($onbon as $bon => $element) {
                 $them .= '<tr>
                    <td width="10%" align="left"><strong>'.$element->date_bon.'</strong></td>
                     <td width="10%" align="center"><strong>'.$element->bonsecondid.'</strong></td>
                     <td width="7%" align="left"><strong>'.$element->code_bon.'</strong></td>
                     <td width="15%" align="left"><strong>'.$element->nom_gaep.'-'.$element->nom_gadest.'</strong></td>
                     
                     <td width="20%" align="left"><strong>'.$element->nom_client.'&nbsp;&nbsp;'.$element->prenom_client.'</strong></td>
                     <td align="left"><strong>'.$element->contact_client.'</strong></td>
                     <td ><strong>'.$element->num_CNIB.' '.utf8_encode(strftime("%d/%m/%G", strtotime($element->date_delivre))).'</strong></td>
                     </tr>';
             }
             $them .= ' </tbody></table>';
            
             
             $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
             $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
             ob_end_clean();
             //Close and output PDF document
             $pdf->Output('example_011.pdf' . '', 'I');
             //============================================================+
             // END OF FILE
             //============================================================+

        }

        public function etatsplis1($ckey, $g)
        {
          $this->entreprise = $this->m_entreprises->get_key($ckey);

                $dt1 = $this->input->post('datesdebutspli');
                $dt2 = $this->input->post('datesfinspli');
                $lign = $this->input->post('axelignespli');
                $comp = $this->input->post('_compagnpli');
                $typcr = $this->input->post('types_courspli');
                $gid = $this->input->post('deptgaresidpli');
                
                $ncomp = $this->m_compagnies->getn($comp);
                $ncgd = $this->m_gare_depart->getn($gid);
                $gar = $ncgd->nom_gaep;

                    $dats = explode("-", $dt1);
                    $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                    $dats1 = explode("-", $dt2);
                    $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];

                  $cais = strpos($this->input->post('caissesidpli'), '/');
                  $cais1 = substr($this->input->post('caissesidpli'), 0, $cais);
                  $cais2 = substr($this->input->post('caissesidpli'), $cais + 1, strlen($this->input->post('caissesidpli')));
                
                $expcours = $this->m_courrier_expedier->expetatsplis($this->entreprise->ekey, $dt1, $dt2, $gid, $comp, $cais1, $typcr, $lign);

                $ty = 'PLIS';
                $ty2 = 'COLIS';
                if($typcr === 'Gros_plis'){
                  $ty3 = $ty2;
                }elseif($typcr === 'Petit_plis'){
                  $ty3 = $ty;
                }elseif($typcr === ''){
                  $ty3 = $ty.'/'.$ty2;
                }

                $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                // set document information
                $pdf->SetCreator(PDF_CREATOR);
                $pdf->SetAuthor('NET SOLUTIONS');
                $pdf->SetTitle('LISTE-');
                $pdf->SetSubject('RECAPTULATIF');
                $pdf->SetKeywords('--');
                
                $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
                // remove default header/footer
                $pdf->setPrintHeader(true);
                $pdf->setPrintFooter(false);
                
                // set default monospaced font
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
                $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                // set margins
                $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                
                
                // set auto page breaks
                $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
                
                // set image scale factor
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
                
                // set font
                
                
                // add a page
                $pdf->AddPage('L', 'A4', 0);
                
                // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                // GROUPE DE GAUCHE
                $pdf->SetFont('courier', '', 9);
                            
                $titre = '<h1 align="center">EXERCICE MENSUEL GUICHETIER '.$cais2.' '.$ty3.'  '.$ncomp->nom_compagnie.' '.$gar.' DU '. $days.' AU '.$days1.'</h1>';
                $them = '<table border="1" cellpadding="0">
                    <thead> 
                        <tr> 
                          <th width="25%" align="left"><strong>LIGNE</strong></th>
                          <th width="15%" align="center"><strong>NBR COLIS</strong></th>
                          <th width="20%" align="center"><strong>PRIX UNITAIRE</strong></th>
                          <th width="20%" align="right"><strong>PRIX TOTAL</strong></th> 
                          </tr>
                    </thead>
                    <tbody>';
                     $tglobal = 0;
                    $nb = 0;
                foreach ($expcours as $departs => $element) {
                    $them .= '<tr>
                      <td width="25%" align="left"><strong>' . $element->nom_ligne . '</strong></td>
                          <td width="15%" align="center"><strong>'.$element->nombres . '</strong></td>
                          <td width="20%" align="center"><strong>' . number_format($element->prixcolis, 0, '', ' ') . '</strong></td>
                          <td width="20%" align="right"><strong>' . number_format(($element->nombres) * $element->prixcolis, 0, '', ' ') . '</strong></td>
                          </tr>';
                           $tglobal += $element->nombres * $element->prixcolis;
                          $tglobal1 = $tglobal;
                           $nb += $element->nombres;
                        }
          
                $them .= '<tr>
                            <td width="25%" align="center"><strong>TOTAL</strong></td>
                            <td width="15%" align="center"><strong> '.($nb).'</strong></td>
                            <td width="20%" align="center"><strong></strong></td>
                            <td width="20%" align="right"><strong> '.number_format($tglobal1, 0, '', ' ').'</strong></td>
                            
                       </tr>';
                $them .= ' </tbody></table>';
                $them.= '<h2>SOMME:'. number_format($tglobal1, 0, '', ' ') .' </h2>';

                $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
                $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
                ob_end_clean();
                //Close and output PDF document
                $pdf->Output('example_012.pdf' . '', 'I');
                //============================================================+
                // END OF FILE
                //============================================================+
        }

        public function etatsplis1esc($ckey, $g)
        {
          $this->entreprise = $this->m_entreprises->get_key($ckey);

                $dt1 = $this->input->post('datesdebutspliesc');
                $dt2 = $this->input->post('datesfinspliesc');
                $lign = $this->input->post('axelignespliesc');
                $comp = $this->input->post('_compagnpliesc');
                $typcr = $this->input->post('types_courspliesc');
                $gid = $this->input->post('deptgaresidpliesc');
                
                $ncomp = $this->m_compagnies->getn($comp);
                $ncgd = $this->m_gare_depart->getn($gid);
                $gar = $ncgd->nom_gaep;

                    $dats = explode("-", $dt1);
                    $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                    $dats1 = explode("-", $dt2);
                    $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];

                  $cais = strpos($this->input->post('caissesidpliesc'), '/');
                  $cais1 = substr($this->input->post('caissesidpliesc'), 0, $cais);
                  $cais2 = substr($this->input->post('caissesidpliesc'), $cais + 1, strlen($this->input->post('caissesidpliesc')));
                
                $expcours = $this->m_courrier_expedieresc->expetatsplis($this->entreprise->ekey, $dt1, $dt2, $gid, $comp, $cais1, $typcr, $lign);

                $ty = 'PLIS';
                $ty2 = 'COLIS';
                if($typcr === 'Gros_plis'){
                  $ty3 = $ty2;
                }elseif($typcr === 'Petit_plis'){
                  $ty3 = $ty;
                }elseif($typcr === ''){
                  $ty3 = $ty.'/'.$ty2;
                }

                $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                // set document information
                $pdf->SetCreator(PDF_CREATOR);
                $pdf->SetAuthor('NET SOLUTIONS');
                $pdf->SetTitle('LISTE-');
                $pdf->SetSubject('RECAPTULATIF');
                $pdf->SetKeywords('--');
                
                $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
                // remove default header/footer
                $pdf->setPrintHeader(true);
                $pdf->setPrintFooter(false);
                
                // set default monospaced font
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
                $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                // set margins
                $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                
                
                // set auto page breaks
                $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
                
                // set image scale factor
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
                
                // set font
                
                
                // add a page
                $pdf->AddPage('L', 'A4', 0);
                
                // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                // GROUPE DE GAUCHE
                $pdf->SetFont('courier', '', 9);
                            
                $titre = '<h1 align="center">EXERCICE MENSUEL ESCAL GUICHETIER '.$cais2.' '.$ty3.'  '.$ncomp->nom_compagnie.' '.$gar.' DU '. $days.' AU '.$days1.'</h1>';
                $them = '<table border="1" cellpadding="0">
                    <thead>
                        <tr> 
                          <th width="25%" align="left"><strong>LIGNE</strong></th>
                          <th width="15%" align="center"><strong>NBR COLIS</strong></th>
                          <th width="20%" align="center"><strong>PRIX UNITAIRE</strong></th>
                          <th width="20%" align="right"><strong>PRIX TOTAL</strong></th> 
                          </tr>
                    </thead>
                    <tbody>';
                     $tglobal = 0;
                    $nb = 0;
                foreach ($expcours as $departs => $element) {
                    $them .= '<tr>
                      <td width="25%" align="left"><strong>' . $element->nom_ligne . '</strong></td>
                          <td width="15%" align="center"><strong>'.$element->nombresesc . '</strong></td>
                          <td width="20%" align="center"><strong>' . number_format($element->prixcolisesc, 0, '', ' ') . '</strong></td>
                          <td width="20%" align="right"><strong>' . number_format(($element->nombresesc) * $element->prixcolisesc, 0, '', ' ') . '</strong></td>
                          </tr>';
                           $tglobal += $element->nombresesc * $element->prixcolisesc;
                          $tglobal1 = $tglobal;
                           $nb += $element->nombresesc;
                        }
          
                $them .= '<tr>
                            <td width="25%" align="center"><strong>TOTAL</strong></td>
                            <td width="15%" align="center"><strong> '.($nb).'</strong></td>
                            <td width="20%" align="center"><strong></strong></td>
                            <td width="20%" align="right"><strong> '.number_format($tglobal1, 0, '', ' ').'</strong></td>
                            
                       </tr>';
                $them .= ' </tbody></table>';
                $them.= '<h2>SOMME:'. number_format($tglobal1, 0, '', ' ') .' </h2>';

                $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
                $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
                ob_end_clean();
                //Close and output PDF document
                $pdf->Output('example_012.pdf' . '', 'I');
                //============================================================+
                // END OF FILE
                //============================================================+
        }

        public function etatsverseplis($ckey, $g)
        {
          $this->entreprise = $this->m_entreprises->get_key($ckey);

                $dt1 = $this->input->post('datesdebutsplivers');
                $dt2 = $this->input->post('datesfinsplivers');
                $lign = $this->input->post('axelignesplivers');
                $comp = $this->input->post('_compagnplivers');
                $typcr = $this->input->post('types_coursplivers');
                $gid = $this->input->post('deptgaresidplivers');
                $ncomp = $this->m_compagnies->getn($comp);
                $ncgd = $this->m_gare_depart->getn($gid);
                $gar = $ncgd->nom_gaep;

                $dats = explode("-", $dt1);
                $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                $dats1 = explode("-", $dt2);
                $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];

                  $cais = strpos($this->input->post('caissesidplivers'), '/');
                  $cais1 = substr($this->input->post('caissesidplivers'), 0, $cais);
                  $cais2 = substr($this->input->post('caissesidplivers'), $cais + 1, strlen($this->input->post('caissesidplivers')));
                
               //$expcoures = $this->m_courrier_expedier->expetatspli($this->entreprise->ekey, $comp, $dt1, $dt2, $gid, $cais1, $typcr);
                
                $expcoures = $this->m_courrier_expedier->expverspli($this->entreprise->ekey, $dt1, $dt2, $comp, $gid, $cais1, $typcr);
                  
                //var_dump($expcoures, $cais2, $dt1, $dt2, $gid, $cais1, $typcr, $comp);
                  $ty = 'PLIS';
                  $ty2 = 'COLIS';
                  if($typcr === 'Gros_plis'){
                    $ty3 = $ty2;
                  }elseif($typcr === 'Petit_plis'){
                    $ty3 = $ty;
                  }
                  elseif($typcr === ''){
                    $ty3 = $ty.'/'.$ty2;
                  }

                $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                // set document information
                $pdf->SetCreator(PDF_CREATOR);
                $pdf->SetAuthor('NET SOLUTIONS');
                $pdf->SetTitle('LISTE-');
                $pdf->SetSubject('RECAPTULATIF');
                $pdf->SetKeywords('--');
                
                $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
                // remove default header/footer
                $pdf->setPrintHeader(true);
                $pdf->setPrintFooter(false);
                
                // set default monospaced font
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
                $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                // set margins
                $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                
                
                // set auto page breaks
                $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
                
                // set image scale factor
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
                
                // set font
                
                
                // add a page
                $pdf->AddPage('L', 'A4', 0);
                
                // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                // GROUPE DE GAUCHE
                $pdf->SetFont('courier', '', 9);
                            
                $titre = '<h1 align="center">BROUILLARD(EXERCICE) COURRIER '.$cais2.' '.$ty3.'  '.$ncomp->nom_compagnie.' '.$gar.' DU '. $days.' AU '.$days1.'</h1>';
                $them = '<table border="1" cellpadding="0">
                    <thead> 
                        <tr> 
                          <th width="25%" align="left"><strong>DATE ENVOI</strong></th>
                          
                          <th width="20%" align="right"><strong>MONTANT</strong></th> 
                          </tr>
                    </thead>
                    <tbody>';
                     $tglobal = 0;
                    $nb = 0;
                foreach ($expcoures as $departs => $element) {
                  $datse = explode("-", $element->dateenvoi);
                  $dayse = $datse[2]. '-'. $datse[1]. '-' .$datse[0];
                    $them .= '<tr>
                    <td width="25%" align="left"><strong>' . $dayse . '</strong></td>
                         
                          <td width="20%" align="right"><strong>' . number_format($element->montant, 0, '', ' ') . '</strong></td>
                          </tr>';
                           $tglobal +=$element->montant;
                }
          
                $them .= '<tr>
                            <td width="25%" align="center"><strong>TOTAL</strong></td>
                            <td width="20%" align="right"><strong> '.number_format($tglobal, 0, '', ' ').'</strong></td>
                            
                       </tr>';

                $them .= ' </tbody></table>';
                $them.= '<h2>SOMME:'. number_format($tglobal, 0, '', ' ') .' </h2>';

                $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
                $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
                ob_end_clean();
                //Close and output PDF document
                $pdf->Output('example_012.pdf' . '', 'I');
                //============================================================+
                // END OF FILE
                //============================================================+
        }

        public function etatsverseplisesc($ckey, $g)
        {
          $this->entreprise = $this->m_entreprises->get_key($ckey);

                $dt1 = $this->input->post('datesdebutspliversesc');
                $dt2 = $this->input->post('datesfinspliversesc');
                $lign = $this->input->post('axelignespliversesc');
                $comp = $this->input->post('_compagnpliversesc');
                $typcr = $this->input->post('types_courspliversesc');
                $gid = $this->input->post('deptgaresidpliversesc');
                $ncomp = $this->m_compagnies->getn($comp);
                $ncgd = $this->m_gare_depart->getn($gid);
                $gar = $ncgd->nom_gaep;

                $dats = explode("-", $dt1);
                $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                $dats1 = explode("-", $dt2);
                $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];

                  $cais = strpos($this->input->post('caissesidpliversesc'), '/');
                  $cais1 = substr($this->input->post('caissesidpliversesc'), 0, $cais);
                  $cais2 = substr($this->input->post('caissesidpliversesc'), $cais + 1, strlen($this->input->post('caissesidpliversesc')));
                
              
                $expcoures = $this->m_courrier_expedieresc->expverspli($this->entreprise->ekey, $dt1, $dt2, $comp, $gid, $cais1, $typcr);
                 
                  $ty = 'PLIS';
                  $ty2 = 'COLIS';
                  if($typcr === 'Gros_plis'){
                    $ty3 = $ty2;
                  }elseif($typcr === 'Petit_plis'){
                    $ty3 = $ty;
                  }
                  elseif($typcr === ''){
                    $ty3 = $ty.'/'.$ty2;
                  }

                $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                // set document information
                $pdf->SetCreator(PDF_CREATOR);
                $pdf->SetAuthor('NET SOLUTIONS');
                $pdf->SetTitle('LISTE-');
                $pdf->SetSubject('RECAPTULATIF');
                $pdf->SetKeywords('--');
                
                $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
                // remove default header/footer
                $pdf->setPrintHeader(true);
                $pdf->setPrintFooter(false);
                
                // set default monospaced font
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
                $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                // set margins
                $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                
                
                // set auto page breaks
                $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
                
                // set image scale factor
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
                
                // set font
                
                
                // add a page
                $pdf->AddPage('L', 'A4', 0);
                
                // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                // GROUPE DE GAUCHE
                $pdf->SetFont('courier', '', 9);
                            
                $titre = '<h1 align="center">BROUILLARD(EXERCICE) COURRIERESCAL '.$cais2.' '.$ty3.'  '.$ncomp->nom_compagnie.' '.$gar.' DU '. $days.' AU '.$days1.'</h1>';
                $them = '<table border="1" cellpadding="0">
                    <thead> 
                        <tr> 
                          <th width="25%" align="left"><strong>DATE ENVOI</strong></th>
                          
                          <th width="20%" align="right"><strong>MONTANT</strong></th> 
                          </tr>
                    </thead>
                    <tbody>';
                     $tglobal = 0;
                    $nb = 0;
                foreach ($expcoures as $departs => $element) {
                  $datse = explode("-", $element->dateenvoiesc);
                  $dayse = $datse[2]. '-'. $datse[1]. '-' .$datse[0];
                    $them .= '<tr>
                    <td width="25%" align="left"><strong>' . $dayse . '</strong></td>
                         
                          <td width="20%" align="right"><strong>' . number_format($element->montantesc, 0, '', ' ') . '</strong></td>
                          </tr>';
                           $tglobal +=$element->montantesc;
                }
          
                $them .= '<tr>
                            <td width="25%" align="center"><strong>TOTAL</strong></td>
                            <td width="20%" align="right"><strong> '.number_format($tglobal, 0, '', ' ').'</strong></td>
                            
                       </tr>';

                $them .= ' </tbody></table>';
                $them.= '<h2>SOMME:'. number_format($tglobal, 0, '', ' ') .' </h2>';

                $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
                $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
                ob_end_clean();
                //Close and output PDF document
                $pdf->Output('example_012.pdf' . '', 'I');
                //============================================================+
                // END OF FILE
                //============================================================+
        }

        public function etatsglcourrier($ckey, $g)
        {
          $this->entreprise = $this->m_entreprises->get_key($ckey);

                $dt1 = $this->input->post('datesdebutsplig');
                $dt2 = $this->input->post('datesfinsplig');
                $lign = $this->input->post('axelignesplig');
                $comp = $this->input->post('_compagnplig');
                $typcr = $this->input->post('types_coursplig');
                $gid = $this->input->post('deptgaresidplig');
                
                $ncomp = $this->m_compagnies->getn($comp);
                $ncgd = $this->m_gare_depart->getn($gid);
                $gar = $ncgd->nom_gaep;

                //$ct = $this->m_categ->getps($this->entreprise->id_entreprise, $typcr);

                    $dats = explode("-", $dt1);
                    $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                    $dats1 = explode("-", $dt2);
                    $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];

                  $cais = strpos($this->input->post('caissesidplig'), '/');
                  $cais1 = substr($this->input->post('caissesidplig'), 0, $cais);
                  $cais2 = substr($this->input->post('caissesidplig'), $cais + 1, strlen($this->input->post('caissesidplig')));
                
                //$expcours = $this->m_courrier_expedier->expetatspligl($this->entreprise->ekey, $dt1, $dt2, $gid, $cais1, $typcr, $comp, $lign);

                  $expcours = $this->m_courrier_expedier->texpetatspligl($this->entreprise->ekey, $dt1, $dt2, $gid, $comp, $cais1, $typcr, $lign);

                $ty = 'PLIS ';
                  $ty2 = 'COLIS';
                  if($typcr === 'Gros_plis'){
                    $ty3 = $ty2;
                  }elseif($typcr === 'Petit_plis'){
                    $ty3 = $ty;
                  }


                $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                // set document information
                $pdf->SetCreator(PDF_CREATOR);
                $pdf->SetAuthor('NET SOLUTIONS');
                $pdf->SetTitle('LISTE-');
                $pdf->SetSubject('RECAPTULATIF');
                $pdf->SetKeywords('--');
                
                $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
                // remove default header/footer
                $pdf->setPrintHeader(true);
                $pdf->setPrintFooter(false);
                
                // set default monospaced font
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
                $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                // set margins
                $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                
                
                // set auto page breaks
                $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
                
                // set image scale factor
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
                
                // set font
                
                
                // add a page
                $pdf->AddPage('L', 'A4', 0);
                
                // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                // GROUPE DE GAUCHE
                $pdf->SetFont('courier', '', 9);
                            
                $titre = '<h1 align="center">ETAT GLOBAL COURRIER'.$ty3.'  '.$ncomp->nom_compagnie.' '.$gar.' DU '. $days.' AU '.$days1.' '.$cais2.'</h1>';
                $them = '<table border="1" cellpadding="0">
                    <thead> 
                        <tr> 
                          <th width="25%" align="left"><strong>LIGNE</strong></th>
                          
                          <th width="15%" align="center"><strong>NBR COLIS</strong></th>
                          <th width="20%" align="center"><strong>PRIX UNITAIRE</strong></th>
                          <th width="20%" align="right"><strong>PRIX TOTAL</strong></th> 
                          </tr>
                    </thead>
                    <tbody>';
                     $tglobal = 0;
                    $nb = 0;
                foreach ($expcours as $departs => $element) {
                    $them .= '<tr>
                    <td width="25%" align="left"><strong>' . $element->nom_ligne . '</strong></td>
                        
                          <td width="15%" align="center"><strong>' . round($element->nombres) . '</strong></td>
                          <td width="20%" align="center"><strong>' . number_format($element->prixcolis, 0, '', ' ') . '</strong></td>
                          <td width="20%" align="right"><strong>' . number_format($element->montant, 0, '', ' ') . '</strong></td>
                          </tr>';
                           $tglobal +=$element->montant;
                           $nb +=round($element->nombres);
                        }
          
                $them .= '<tr>
                            <td width="25%" align="center"><strong>TOTAL</strong></td>
                            <td width="15%" align="center"><strong> '.($nb).'</strong></td>
                            <td width="20%" align="center"><strong></strong></td>
                            <td width="20%" align="right"><strong> '.number_format($tglobal, 0, '', ' ').'</strong></td>
                            
                       </tr>';
                $them .= ' </tbody></table>';
                $them.= '<h2>SOMME:'. number_format($tglobal, 0, '', ' ') .' </h2>';

                $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
                $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
                ob_end_clean();
                //Close and output PDF document
                $pdf->Output('example_012.pdf' . '', 'I');
                //============================================================+
                // END OF FILE
                //============================================================+
        }



        public function etatsglcourrieresc($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);

                $dt1 = $this->input->post('datesdebutspligesc');
                $dt2 = $this->input->post('datesfinspligesc');
                $lign = $this->input->post('axelignespligesc');
                $comp = $this->input->post('_compagnpligesc');
                $typcr = $this->input->post('types_courspligesc');
                $gid = $this->input->post('deptgaresidpligesc');
                
                $ncomp = $this->m_compagnies->getn($comp);
                $ncgd = $this->m_gare_depart->getn($gid);
                $gar = $ncgd->nom_gaep;

                //$ct = $this->m_categ->getps($this->entreprise->id_entreprise, $typcr);

                $ty = 'PLIS';
                $ty2 = 'COLIS';

                if($typcr === 'Gros_plis'){
                  $ty3 = $ty2;
                }elseif($typcr === 'Petit_plis'){
                  $ty3 = $ty;
                }elseif($typcr === ''){
                  $ty3 = $ty.'/'.$ty2;
                }

                    $dats = explode("-", $dt1);
                    $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                    $dats1 = explode("-", $dt2);
                    $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];

                  $cais = strpos($this->input->post('caissesidpligesc'), '/');
                  $cais1 = substr($this->input->post('caissesidpligesc'), 0, $cais);
                  $cais2 = substr($this->input->post('caissesidpligesc'), $cais + 1, strlen($this->input->post('caissesidpligesc')));
                
                //$expcours = $this->m_courrier_expedieresc->expetatspligl($this->entreprise->ekey, $dt1, $dt2, $gid, $cais1, $typcr, $comp, $lign);

                  $expcours = $this->m_courrier_expedieresc->texpetatspligl($this->entreprise->ekey, $dt1, $dt2, $gid, $comp, $cais1, $typcr, $lign);


                $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                // set document information
                $pdf->SetCreator(PDF_CREATOR);
                $pdf->SetAuthor('NET SOLUTIONS');
                $pdf->SetTitle('LISTE-');
                $pdf->SetSubject('RECAPTULATIF');
                $pdf->SetKeywords('--');
                
                $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
                // remove default header/footer
                $pdf->setPrintHeader(true);
                $pdf->setPrintFooter(false);
                
                // set default monospaced font
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
                $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                // set margins
                $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                
                
                // set auto page breaks
                $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
                
                // set image scale factor
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
                
                // set font
                
                
                // add a page
                $pdf->AddPage('L', 'A4', 0);
                
                // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                // GROUPE DE GAUCHE
                $pdf->SetFont('courier', '', 9);
                            
                $titre = '<h1 align="center">ETAT GLOBAL COURRIERESCAL'.$ty3.'  '.$ncomp->nom_compagnie.' '.$gar.' DU '. $days.' AU '.$days1.' '.$cais2.'</h1>';
                $them = '<table border="1" cellpadding="0">
                    <thead> 
                        <tr> 
                          <th width="25%" align="left"><strong>LIGNE</strong></th>
                          
                          <th width="15%" align="center"><strong>NBR COLIS</strong></th>
                          <th width="20%" align="center"><strong>PRIX UNITAIRE</strong></th>
                          <th width="20%" align="right"><strong>PRIX TOTAL</strong></th> 
                          </tr>
                    </thead>
                    <tbody>';
                     $tglobal = 0;
                    $nb = 0;
                foreach ($expcours as $departs => $element) {
                    $them .= '<tr>
                    <td width="25%" align="left"><strong>' . $element->nom_ligne . '</strong></td>
                        
                          <td width="15%" align="center"><strong>' . round($element->nombresesc) . '</strong></td>
                          <td width="20%" align="center"><strong>' . number_format($element->prixcolisesc, 0, '', ' ') . '</strong></td>
                          <td width="20%" align="right"><strong>' . number_format($element->montantesc, 0, '', ' ') . '</strong></td>
                          </tr>';
                           $tglobal +=$element->montantesc;
                           $nb +=round($element->nombresesc);
                        }
          
                $them .= '<tr>
                            <td width="25%" align="center"><strong>TOTAL</strong></td>
                            <td width="15%" align="center"><strong> '.($nb).'</strong></td>
                            <td width="20%" align="center"><strong></strong></td>
                            <td width="20%" align="right"><strong> '.number_format($tglobal, 0, '', ' ').'</strong></td>
                            
                       </tr>';
                $them .= ' </tbody></table>';
                $them.= '<h2>SOMME:'. number_format($tglobal, 0, '', ' ') .' </h2>';

                $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
                $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
                ob_end_clean();
                //Close and output PDF document
                $pdf->Output('example_012.pdf' . '', 'I');
                //============================================================+
                // END OF FILE
                //============================================================+
        }
        //bordereau bagage

        public function listesbagages($ckey)
        {
              $cdbord = $this->input->post('courschauffeurbg');
              $cprgbord = $this->input->post('courdeptprograbg');
              $cvbord = $this->input->post('courconvoibg');
              $dabord = $this->input->post('courborddeptdateenbg');
              $lignebord = $this->input->post('deptscourlignebg');
              $usenam = $this->input->post('usernames');
              $nam = $this->m_compte_user->for($usenam);
              $lignequart = $this->input->post('courdeptquartierbg');
              $gd = $this->input->post('gareattribuer');
              $sgd = $this->input->post('sousgareconnect');
              $iduser = $this->input->post('usernameconect');
              
              $itinerairesg = $this->db->query("SELECT sg.nomsousgare, sg.idsousgare FROM sousgare sg WHERE sg.idsousgare = '$sgd'")->row();

              //identifiant l'heure dans la table ligne heure

              $ligne_lhbord = strpos($this->input->post('deptscourlignebg'), '/');
              
              $lignehbord = substr($this->input->post('deptscourlignebg'), 0, $ligne_lhbord);
              $lignelhrebord = substr($this->input->post('deptscourlignebg'), $ligne_lhbord + 1, strlen($this->input->post('deptscourlignebg')));
              
              $ligne_lhbord1 = strpos($lignehbord, '-');
              
              $lignehbord1 = substr($lignehbord, 0, $ligne_lhbord1);
                $lignelhrebord1 = substr($lignehbord, $ligne_lhbord1 + 1, strlen($lignehbord));
              
              $post_heurebord = strpos($this->input->post('courdeptprograbg'), '/');

              $sub_heurebord = substr($this->input->post('courdeptprograbg'), 0, $post_heurebord);

              $dprogbord = substr($this->input->post('courdeptprograbg'), $post_heurebord + 1, strlen($this->input->post('courdeptprograbg')));

              $post_heurebord1 = strpos($dprogbord, '/');

              $sub_heurebord1 = substr($dprogbord, 0, $post_heurebord1);

              $dprogbord1 = substr($dprogbord, $post_heurebord1 + 1, strlen($dprogbord));
              
              $post_heurebord2 = strpos($dprogbord1, '/');

              $sub_heurebord2 = substr($dprogbord1, 0, $post_heurebord2);

              $dprogbord2 = substr($dprogbord1, $post_heurebord2 + 1, strlen($dprogbord1));

              
              //$nombord = $cdpbord;
              //$ngebord = substr($nombord, 6, 6);

                if($cdbord != '' AND $cprgbord !='')
                {
                  $this->entreprise = $this->m_entreprises->get_key($ckey);
               
                    if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){
                        
                        $onbord = $this->m_envoibagages->listad1($this->entreprise->ekey, $sub_heurebord, $sub_heurebord2, $dabord, $lignequart);
               
                    }
                    else
                    {
                        $onbord = $this->m_envoibagages->list1($this->entreprise->ekey, $gd, $sgd, $sub_heurebord, $sub_heurebord2, $dabord, $lignequart);
                      
                    }

                
                      $onprogrambordbg = $this->m_bordereaubagage->get($this->entreprise->ekey, $gd, $sgd, $sub_heurebord, $dabord, $lignequart);

                     $addtiragebordbg = array(
                        'idoperbordbag' => $iduser,
                        'idsousgdbordbag' => $sgd,
                        'programmebordbag' => $sub_heurebord,
                         'lignebordbag' => $lignehbord,
                         'quartierbordbag' => $this->input->post('courdeptquartierbg'),
                         'datebordbag' => $this->input->post('courborddeptdateenbg'),
                         'buschauffbordbag' => $this->input->post('courschauffeurbg'),
                         'busconvoybordbag' => $this->input->post('courconvoibg'),
                     );

                      if($onprogrambordbg === NULL){

                         $numb = $this->m_bordereaubagage->create($addtiragebordbg);

                        $ln = $this->m_bordereaubagage->getnu($this->entreprise->ekey, $numb);
                      }
                      else
                      {

                        $this->m_bordereaubagage->update($onprogrambordbg->identbordbag, $addtiragebordbg);

                        $numb = $onprogrambordbg->identbordbag;

                        $ln = $this->m_bordereaubagage->getnu($this->entreprise->ekey, $numb);
                      }

                        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                  
     
                      // set document information
                      $pdf->SetCreator(PDF_CREATOR);
                      $pdf->SetAuthor('MANDISS KARARMA');
                      $pdf->SetTitle('LISTE-');
                      $pdf->SetSubject('BAGAGGES');
                      $pdf->SetKeywords('--');
                      
                      $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise, '   ' . utf8_encode(strftime("%d-%m-%G", strtotime($dabord))) . '    ' . $sub_heurebord1.'  N° BORDEREAU : '.$numb);
                      // remove default header/footer
                      $pdf->setPrintHeader(true);
                      $pdf->setPrintFooter(false);
                      
                      // set default monospaced font
                      $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
                      $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                      $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                      // set margins
                      $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                      
                      
                      // set auto page breaks
                      $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
                      
                      // set image scale factor
                      $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
                      
                      // set font
                      
                      
                      // add a page
                      //$pdf->AddPage();
                      $pdf->AddPage('P', 'A4', 0);
                      // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                      // GROUPE DE GAUCHE
                      $pdf->SetFont('courier', '', 10);
                      $htmlhead = '<h3>CODE DE LA GARE: ' . $lignehbord1 .' &nbsp;&nbsp;&nbsp;CHAUFFEUR: ' . urldecode($cdbord).' &nbsp;&nbsp;&nbsp;CODE_DEPART: ' . urldecode($dprogbord2) . '</h3>
                         
                         <h3></h3>';
                      $pdf->writeHTML($htmlhead, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
                      
                      $titre = '<h1 align="center"> SUIVI BAGAGES  '.$itinerairesg->nomsousgare.' '.$ln->nom_ligne.' '.$lignequart.'</h1>';
                      $them = '<table border="1" cellpadding="0">
                          <thead> 
                            <tr> <th width="10%" align="center"><strong>NUM BAGAGE</strong></th>
                                <th width="15%" align="center"><strong>CODE</strong></th>
                                <th width="25%" align="left"><strong>OUANTITE/DESIGNATION</strong></th>
                                <th width="30%" align="center"><strong>NOM ET PRENOM/CONTACT</strong></th>
                                <th width="10%" align="center"><strong>MONTANT</strong></th>
                                <th width="10%" align="center"><strong>DEST_FINALE</strong></th>
                            </tr>
                          </thead>
                          <tbody>';
                      foreach ($onbord as $departhbord => $elementbord) {
                          $them .= '<tr>
                              <td width="10%" align="left"><strong>' . str_pad($elementbord->identbagas, 3, "0", STR_PAD_LEFT). '</strong></td>
                              <td width="15%" align="left"><strong>' . $elementbord->codebag . '</strong></td>
                              <td width="25%" align="left"><strong>' . $elementbord->nombrebagageenv .'/'.$elementbord->nombrebagage. ' '.$elementbord->contenubagageenv.'</strong></td>
                              <td width="30%" align="left"><strong>' . $elementbord->nom_client . '&nbsp;&nbsp;' . $elementbord->prenom_client . ' ' . $elementbord->contact_client . '</strong></td>
                              <td width="10%" align="left"><strong>' . number_format($elementbord->prix_bagage, 0, '', ' ') . ' F</strong> </td>
                              <td width="10%" align="left"><strong>' .$this->m_gare_arrivee->g($elementbord->gidarrbag)->nom_gaep. ' ' .$elementbord->quartarr_bg. '</strong> </td>
                            </tr>';
                      }
                      $them .= '<tr>
                        <td width="100%" align="center"></td>
                        
                        </tr>';
                      $them .= '<tr>
                        <td width="30%" align="center"><strong>Agent ecrivain bagage <br><br><br> '. $nam->first_name.' '.$nam->last_name.'</strong></td>
                        <td width="30%" align="center"><strong>Convoyeur <br> <br><br>'. urldecode($cvbord).'</strong></td>
                          <td width="40%" align="center"><strong>Agent recepteur</strong></td>
                        
                        </tr>';
                      $them .= ' </tbody></table>';
                      
                      
                      
                        $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
                        $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
                        ob_end_clean();
                        //Close and output PDF document
                        $pdf->Output('example_011.pdf' . '', 'I');
                      
                }
               //============================================================+
               // END OF FILE
               //============================================================+
        }

        public function reimpressionlistebag($ckey, $gd, $sgd, $sub_heurebord, $sub_heurebord2, $dabord, $lignequart)
        {
             $this->entreprise = $this->m_entreprises->get_key($ckey);
              $itinerairesg = $this->db->query("SELECT sg.nomsousgare, sg.idsousgare FROM sousgare sg WHERE sg.idsousgare = '$sgd'")->row();

                     
                      $onbord = $this->m_envoibagages->list1($this->entreprise->ekey, $gd, $sgd, $sub_heurebord, $sub_heurebord2, $dabord, $lignequart);
                        
                      $onprogrambordaxe = $this->m_bordereaubagage->get($this->entreprise->ekey, $gd, $sgd, $sub_heurebord, $dabord, $lignequart);

                      $nam = $this->m_compte_user->cpuseres($onprogrambordaxe->idoperbordbag);

                      $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                  
     
                      // set document information
                      $pdf->SetCreator(PDF_CREATOR);
                      $pdf->SetAuthor('MANDISS KARARMA');
                      $pdf->SetTitle('LISTE-');
                      $pdf->SetSubject('BAGAGES');
                      $pdf->SetKeywords('--');
                      
                      $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise, '   ' . $onprogrambordaxe->dateheure_prog . '    N° BORDEREAU : '.$onprogrambordaxe->identbordbag);

                      // remove default header/footer
                      $pdf->setPrintHeader(true);
                      $pdf->setPrintFooter(false);
                      
                      // set default monospaced font
                      $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
                      $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                      $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                      // set margins
                      $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                      
                      
                      // set auto page breaks
                      $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
                      
                      // set image scale factor
                      $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
                      
                      // set font
                      
                      
                      // add a page
                      //$pdf->AddPage();
                      $pdf->AddPage('P', 'A4', 0);
                      // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                      // GROUPE DE GAUCHE
                      $pdf->SetFont('courier', '', 10);
                      $htmlhead = '<h3>CODE DE LA GARE: ' . $onprogrambordaxe->garesid .' &nbsp;&nbsp;&nbsp;CHAUFFEUR: ' . urldecode($onprogrambordaxe->buschauffbordbag).' &nbsp;&nbsp;&nbsp;CODE_DEPART: ' . urldecode($onprogrambordaxe->depart_code) . '</h3>
                         
                         <h3></h3>';
                      $pdf->writeHTML($htmlhead, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
                      
                      $titre = '<h1 align="center"> SUIVI BAGAGES  '.$itinerairesg->nomsousgare.' '.$onprogrambordaxe->nom_ligne.' '.$lignequart.'</h1>';
                      $them = '<table border="1" cellpadding="0">
                          <thead> 
                            <tr> 
                                <th width="10%" align="center"><strong>NUM BAGAGE</strong></th>
                                <th width="15%" align="center"><strong>CODE</strong></th>
                                <th width="25%" align="left"><strong>QUANTITE/DESIGNATION</strong></th>
                                <th width="30%" align="center"><strong>NOM ET PRENOM DESTINATAIRE/CONTACT</strong></th>
                                <th width="10%" align="center"><strong>MONTANT</strong></th>
                                <th width="10%" align="center"><strong>DEST_FINALE</strong></th>
                            </tr>
                          </thead>
                          <tbody>';
                      foreach ($onbord as $departhbord => $lementbord) {
                          $them .= '<tr>
                              <td width="10%" align="left"><strong>' . $lementbord->identbagas . '</strong></td>
                              <td width="15%" align="left"><strong>' . $lementbord->codebag . '</strong></td>
                              <td width="25%" align="left"><strong>' . $lementbord->nombrebagageenv .'/'.$lementbord->nombrebagage. ' '.$lementbord->contenubagageenv.'</strong></td>
                              <td width="30%" align="left"><strong>' . $lementbord->nom_client . '&nbsp;&nbsp;' . $lementbord->prenom_client . ' ' . $lementbord->contact_client . '</strong></td>
                              <td width="10%" align="left"><strong>' . number_format($lementbord->prix_bagage, 0, '', ' ') . ' F</strong> </td>
                              <td width="10%" align="left"><strong>' .$this->m_gare_arrivee->g($lementbord->gidarrbag)->nom_gaep. ' ' .$lementbord->quartarr_bg. '</strong> </td>
                            </tr>';
                      }
                      $them .= '<tr>
                        <td width="100%" align="center"></td>
                        
                        </tr>';
                      $them .= '<tr>
                        <td width="30%" align="center"><strong>Agent ecrivain bagage <br><br><br> '. $nam->first_name.' '.$nam->last_name.'</strong></td>
                        <td width="30%" align="center"><strong>Convoyeur <br> <br><br>'. urldecode($onprogrambordaxe->busconvoybordbag).'</strong></td>
                          <td width="40%" align="center"><strong>Agent recepteur</strong></td>
                        
                        </tr>';
                      $them .= ' </tbody></table>';
                      
                      
                      
                        $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
                        $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
                        ob_end_clean();
                        //Close and output PDF document
                        $pdf->Output('example_011.pdf' . '', 'I');
                        
                      
                
               //============================================================+
               // END OF FILE
               //============================================================+
        }

        public function listescourriersesc($ckey)
        {
              $cdbord = $this->input->post('courschauffeuresc');
              $cprgbord = $this->input->post('courdeptprograesc');
              $cvbord = $this->input->post('courconvoiesc');
              $dabord = $this->input->post('courborddeptdateenesc');
              $lignebord = $this->input->post('deptscourligneesc');
              $usenam = $this->input->post('usernames');
              $nam = $this->m_compte_user->cpusers($usenam);
              $lignequart = $this->input->post('courdeptquartieresc');
              $gd = $this->input->post('gareattribuer');
              $sgd = $this->input->post('sousgareconnect');
              $iduser = $this->input->post('usernameconect');
              
              $itinerairesg = $this->db->query("SELECT sg.nomsousgare, sg.idsousgare FROM sousgare sg WHERE sg.idsousgare = '$sgd'")->row();

              //identifiant l'heure dans la table ligne heure

              $ligne_lhbord = strpos($this->input->post('deptscourligneesc'), '/');
              
              $lignehbord = substr($this->input->post('deptscourligneesc'), 0, $ligne_lhbord);
              $lignelhrebord = substr($this->input->post('deptscourligneesc'), $ligne_lhbord + 1, strlen($this->input->post('deptscourligneesc')));
              
              $ligne_lhbord1 = strpos($lignehbord, '-');
              
              $lignehbord1 = substr($lignehbord, 0, $ligne_lhbord1);
                $lignelhrebord1 = substr($lignehbord, $ligne_lhbord1 + 1, strlen($lignehbord));
              
              $post_heurebord = strpos($this->input->post('courdeptprograesc'), '/');

              $sub_heurebord = substr($this->input->post('courdeptprograesc'), 0, $post_heurebord);

              $dprogbord = substr($this->input->post('courdeptprograesc'), $post_heurebord + 1, strlen($this->input->post('courdeptprograesc')));

              $post_heurebord1 = strpos($dprogbord, '/');

              $sub_heurebord1 = substr($dprogbord, 0, $post_heurebord1);

              $dprogbord1 = substr($dprogbord, $post_heurebord1 + 1, strlen($dprogbord));
              
              $post_heurebord2 = strpos($dprogbord1, '/');

              $sub_heurebord2 = substr($dprogbord1, 0, $post_heurebord2);

              $dprogbord2 = substr($dprogbord1, $post_heurebord2 + 1, strlen($dprogbord1));

                
                  $this->entreprise = $this->m_entreprises->get_key($ckey);
               
                    if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){
                        
                        $onbord = $this->m_courrier_expedieresc->listad1($this->entreprise->ekey, $sub_heurebord, $sub_heurebord2, $dabord, $lignequart);
                    }
                    else
                    {
                        $onbord = $this->m_courrier_expedier->list1($this->entreprise->ekey, $gd, $sgd, $sub_heurebord, $sub_heurebord2, $dabord, $lignequart);
                    }
                      
                        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                  
     
                      // set document information
                      $pdf->SetCreator(PDF_CREATOR);
                      $pdf->SetAuthor('NET SOLUTIONS');
                      $pdf->SetTitle('LISTE-');
                      $pdf->SetSubject('COURRIERS');
                      $pdf->SetKeywords('--');
                      
                      $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise, '   ' . utf8_encode(strftime("%d-%m-%G", strtotime($dabord))) . '    ' . $sub_heurebord1);
                      // remove default header/footer
                      $pdf->setPrintHeader(true);
                      $pdf->setPrintFooter(false);
                      
                      // set default monospaced font
                      $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
                      $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                      $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                      // set margins
                      $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                      
                      
                      // set auto page breaks
                      $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
                      
                      // set image scale factor
                      $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
                      
                      // set font
                      
                      
                      // add a page
                      //$pdf->AddPage();
                      $pdf->AddPage('P', 'A4', 0);
                      // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                      // GROUPE DE GAUCHE
                      $pdf->SetFont('courier', '', 13);
                      $htmlhead = '<h3>CODE DE LA GARE: ' . $lignehbord1 .' &nbsp;&nbsp;&nbsp;CHAUFFEUR: ' . urldecode($cdbord).'</h3>
                         
                         <h3></h3>';
                      $pdf->writeHTML($htmlhead, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
                      
                      $titre = '<h1 align="center"> LISTE DES COURRIERS  '.$itinerairesg->nomsousgare.' '.$ln->nom_ligne.' '.$lignequart.'</h1>';
                      $them = '<table border="1" cellpadding="0">
                          <thead> 
                            <tr> 
                                <th width="20%" align="center"><strong>CODE</strong></th>
                                <th width="20%" align="center"><strong>DESIGNATION</strong></th>

                            </tr>
                          </thead>
                          <tbody>';
                      foreach ($onbord as $departhbord => $elementbord) {
                          $them .= '<tr>
                              <td width="20%" align="left"><strong>' . $elementbord->num_couresc . '</strong></td>
                              <td width="20%" align="left"><strong>' . $elementbord->nombrecolis . '' . $elementbord->naturecoli . ' '.$elementbord->naturecourrieresc.'</strong></td>
                              
                            </tr>';
                      }
                      $them .= '<tr>
                        <td width="100%" align="center"></td>
                        
                        </tr>';
                      $them .= '<tr>
                        <td width="15%" align="center"><strong>Agent<br><br><br> '. $nam->first_name.' '.$nam->last_name.'</strong></td>
                        <td width="10%" align="center"><strong>Convoyeur <br> <br><br>'. urldecode($cvbord).'</strong></td>
                        <td width="15%" align="center"><strong>Recepteur</strong></td>
                        </tr>';
                      $them .= ' </tbody></table>';
                      
                        $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
                        $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
                        ob_end_clean();
                        //Close and output PDF document
                        $pdf->Output('example_011.pdf' . '', 'I');
                      
                
               //============================================================+
               // END OF FILE
               //============================================================+
        }
    }