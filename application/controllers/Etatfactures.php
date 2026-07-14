<?php defined('BASEPATH') OR exit('No direct script access allowed');

// include the main labraries TCPDF
require_once(APPPATH . 'libraries/tcpdf/tcpdf.php');
    require_once('nel.php');

    
    class PT extends TCPDF
    {
        
        //Page header
        public function Header()
        {
           
            // Logo
            $image_rakieta = 'assets/img/gallery/logo.jpg';
            $this->Image($image_rakieta, 5, 5, 85, '', 'JPG', '', 'TL', false, 400, '', false, false, 0, false, false, false);
            // Set font
            $this->SetFont('helvetica', 'I', 18);
            
            // Title
            $this->writeHTMLCell(160, 40, 40, 15,
                '<h4>Compagnie Burkinabé de Transport</h4>', '',
                0, false, 0, 'R', false);
            
        }
                
        // Page footer
        public function Footer()
        {

            // Position at 30 mm from bottom
            $this->SetY(-30);
            // Set font
            $this->SetFont('helvetica', 'B', 6);

            // Page number/
            $this->writeHTMLCell(200, 5, 5, $this->GetY(),
                '<br>Société Unipersonnelle A Responsabilité Limitée<br>'
                . 'Siège Social Banfora  Parcelle - Lot 35  Section ZC Secteur 1 Rue 1.10 Porte 310<br>'
                . 'BP 105 Banfora(BURKINA FASO) <br>'
                . 'Tel : (226)20910700 75252929<br>'
                . 'RCC : N° BF-BFR-2005 BO108 &nbsp;&nbsp;IFU : 00000983X &nbsp;&nbsp;Régime Fiscal Réel Normal<br>'
                . 'Division Fiscale des Grandes Entreprises<br>'
                . 'Compte BOA : 02117360008 SGBB : 11813300401 17<br>'
                . 'E-mail : cbt_rakieta@yahoo.fr &nbsp;&nbsp;http//www.transport-rakieta.com<br>',
                '',
                1, false, 0, 'C', false);
        }
    }
   
    class Etatfactures extends MY_Controller
    {
        
        public $property = array('title' => 'FACTURES');
        public $entreprise = stdClass::class;
        
        public function __construct()
        {
            parent::__construct();
            $this->property['update_success'] = FALSE;
            $this->property['INSERT'] = FALSE;
            $this->property['pagetitle'] = utf8_encode(strftime("%d %b %G", now()));
        }
     
        
        public function factcontrat($ckey, $g, $dt1, $dt2, $typart, $gid, $punitaire, $cdft, $nt, $objet = FALSE, $montmensuel = FALSE)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);

           
                $facpa = $this->db->query("SELECT * FROM facturations f WHERE f.idfacture = '$cdft'")->row();

                $pa = $facpa->barfact;
                $peri = $facpa->periodicite;
                $nf = $facpa->nom_gaep;

            $ug = $this->m_gare_depart->get($this->entreprise->id_entreprise, $g);
              $dats = explode("-", $dt1);
            $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
            $dats1 = explode("-", $dt2);
            $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
              
              $part = $this->db->query("SELECT * FROM client cl 
                JOIN partenaires p ON p.idclientpatern = cl.id_client
                WHERE cl.id_client = '$typart'")->row();
              
              $oncours = $this->m_courrier_expedier->facts($this->entreprise->ekey, $dt1, $dt2, $typart, $gid, $nt);


               // create new PDF document
                $pdf = new PT(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                            
                // set document information
                $pdf->SetCreator(PDF_CREATOR);
                $pdf->SetAuthor('NET SOLUTIONS');
                $pdf->SetTitle('FACT-');
                $pdf->SetSubject('');
                $pdf->SetKeywords('-FACTURE-');
                // set default header data
                $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);
                // set header and footer fonts
                $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
                $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
                            
                // set margins
                $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                            
                // set auto page breaks
                $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
                            
                // set image scale factor
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
                            
                // ---------------------------------------------------------
                            
                // set font
                $pdf->SetFont('helvetica', '', 9);
                            
                // add a page
                $pdf->AddPage();
                            
                            
                $pdf->writeHTMLCell(80, 0, 110, 40,
                    $ug->nom_ville.', le ' . mdate("%d/%m/%Y", now()),
                    '',
                    0, false, 0, 'R', false);
                            
                // facture N°
                $pdf->SetFont('helvetica', 'B', 12);
                $pdf->writeHTMLCell(140, 0, 40, 55,
                    '<h2>FACTURE N° '.$cdft.' '.$pa.'</h2>', '',
                    0, false, 1, 'L', false);
                
                 $pdf->SetFont('helvetica', 'U', 9);
                // DOIT
                $pdf->writeHTMLCell(18, 0, 15, 75,
                    '<h2>DOIT </h2>', '',
                    0, false, 1, 'L', false);
                $pdf->SetFont('helvetica', 'B', 9);
                
                $pdf->writeHTMLCell(200, 0, 26, 75,
                    '<h2> :  '.$part->nom_client.' '.$part->prenom_client.'/'.$nf.'
                    </h2>', '',
                    0, false, 1, 'L', false);

                $pdf->SetFont('courier', '', 8);

                $pdf->writeHTMLCell(200, 0, 28, 82,
                    '<h2>Siege:'.$part->siegepatern.'
                    <br>'.$part->adressepatern.'
                    <br>Tel:'.$part->telpartern.'
                    <br>RCCM:'.$part->rccmpaterne.'
                    <br>IFU:'.$part->numifupatern.'
                    <br>'.$part->divfiscal.'
                    <br>'.$part->regifiscal.'
                    </h2>', '',
                    0, false, 1, 'L', false);
                            
                //$pdf->SetFont('courier', '', 9);
                //$pdf->writeHTMLCell(250, 0, 20, 65,
                    //'<h2>OBJET:'.urldecode($objet).' DU '.$days.' AU '.$days1.'</h2>', '',
                    //0, false, 1, 'L', false);
                // --------------------------------------------------------
                $pdf->SetFont('helvetica', 'I', 10);
                $table = '<table border="1" cellpadding="3" align="left">
                  <tbody>
                    <tr>
                        <th width="30%" height="50px" align="center"><strong>Désignation</strong></th>
                        <th width="19%" align="center"><strong>Période</strong></th>
                        <th width="11%" align="center"><strong>Quantité</strong></th>
                        <th width="15%" align="center"><strong>Prix unitaire</strong></th>
                        <th width="15%" align="center"><strong>Prix Total</strong></th>
                    </tr>';
                            
                     $pdf->SetFont('helvetica', '', 13);
                    $nb = 0;
                    $m = 0;
                    foreach ($oncours as $departcour => $elemt) {
                        $table .= '<tr>
                      
                          <td width="30%" align="left" height="60px"><strong>' . urldecode($objet) . ' ' . $elemt->naturecoli.'</strong></td>
                          <td width="19%" align="left"><strong>' . $peri . '</strong></td>
                          <td width="11%" align="right"><strong>' . $elemt->nbcol . '</strong></td>
                          <td width="15%" align="right"><strong>' . number_format($punitaire, 0, '', ' ') . '</strong></td>
                          <td width="15%" align="right"><strong>' . number_format(round($elemt->nbcol)*$punitaire, 0, '', ' ') . '</strong></td>
                          </tr>';
                            $nb +=round($elemt->nbcol);
                            $m += ($elemt->nbcol*$punitaire)+$montmensuel;

                    }
        
                  
                    $table .= '<tr>
                        <td width="75%" height="40px" align="center"><strong>TOTAL HT</strong></td>
                        <td width="15%" align="right"><strong>'.number_format($m, 0, '', ' ').'</strong></td>
                        
                        </tr>';
                    $table .= '</tbody></table>';
                    //$pdf->SetFont('helvetica', 'I', 11);
                $pdf->writeHTMLCell(200, 0, 15, 120, $table, '', 0, false, 0, 'L', false);
                //var_dump($oncours);
                $pdf->SetFont('helvetica', 'U', 15);
            
                $pdf->writeHTMLCell(110, 0, 15, 185,
                    'Arrêtée la présente facture à la somme de '
                    , '',
                    0, false, 1, 'L', false);
                $pdf->SetFont('helvetica', '', 15);

                $pdf->writeHTMLCell(80, 0, 110, 185,
                    '<h4>:  '.enlettres($m, NEL_RECTIF_1990).' ('.$m.') F CFA</h4> '
                    , '',
                    0, false, 1, 'R', true);
                            
                
                
                // RESPONSABLE CHARGE
                $pdf->SetFont('helvetica', 'U', 17);
                $pdf->writeHTMLCell(50, 0, 130, 210,
                    'Le Transporteur<br>' ,
                    '',
                    0, false, 0, 'R', false);
                            
                // NOM PRENOM & CACHET 

                    /*$array = array(
                        'montfact' => $m,
                    );
                $this->m_facturation->update($cdft, $array);*/
                // Close and output PDF document
                // This method has several options, check the source code documentation for more information.
                $pdf->Output('FACT' .$g. '.' . mdate("%m.%d.%Y", now()) . '', 'I');
           
            
        }

        //fact colis

        public function factcolis($ckey, $g, $dt1, $dt2, $typart, $gid, $punitaire, $cdft, $nt)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);

            
                $facpa = $this->db->query("SELECT * FROM facturations f WHERE f.idfacture = '$cdft'")->row();

                $pa = $facpa->barfact;
                $peri = $facpa->periodicite;
                $nf = $facpa->nom_gaep;

            $ug = $this->m_gare_depart->get($this->entreprise->id_entreprise, $g);
              $dats = explode("-", $dt1);
            $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
            $dats1 = explode("-", $dt2);
             $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
              
              $part = $this->db->query("SELECT * FROM client cl 
                JOIN partenaires p ON p.idclientpatern = cl.id_client
                WHERE cl.id_client = '$typart'")->row();
              
              $oncours = $this->m_courrier_expedier->factcolis($this->entreprise->ekey, $dt1, $dt2, $typart, $gid, $nt);


               // create new PDF document
                $pdf = new PT(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                            
                // set document information
                $pdf->SetCreator(PDF_CREATOR);
                $pdf->SetAuthor('NET SOLUTIONS');
                $pdf->SetTitle('FACT-');
                $pdf->SetSubject('');
                $pdf->SetKeywords('-FACTURE-');
                // set default header data
                $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);
                // set header and footer fonts
                $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
                $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
                            
                // set margins
                $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                            
                // set auto page breaks
                $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
                            
                // set image scale factor
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
                            
                // ---------------------------------------------------------
                            
                // set font
                $pdf->SetFont('helvetica', '', 9);
                            
                // add a page
                $pdf->AddPage();
                            
                            
                $pdf->writeHTMLCell(80, 0, 110, 40,
                    $ug->nom_ville.', le ' . mdate("%d/%m/%Y", now()),
                    '',
                    0, false, 0, 'R', false);
                            
                // facture N°
                $pdf->SetFont('helvetica', 'B', 12);
                $pdf->writeHTMLCell(140, 0, 40, 55,
                    '<h2>FACTURE N° '.$cdft.' '.$pa.'</h2>', '',
                    0, false, 1, 'L', false);
                
                $pdf->SetFont('helvetica', 'U', 9);
                // DOIT
                $pdf->writeHTMLCell(18, 0, 15, 75,
                    '<h2>DOIT </h2>', '',
                    0, false, 1, 'L', false);
                $pdf->SetFont('helvetica', 'B', 9);
                
                $pdf->writeHTMLCell(200, 0, 26, 75,
                    '<h2> :  '.$part->nom_client.' '.$part->prenom_client.'/'.$nf.'
                    </h2>', '',
                    0, false, 1, 'L', false);

                $pdf->SetFont('courier', '', 8);

                $pdf->writeHTMLCell(200, 0, 28, 82,
                    '<h2>Siege:'.$part->siegepatern.'
                    <br>'.$part->adressepatern.'
                    <br>Tel:'.$part->telpartern.'
                    <br>RCCM:'.$part->rccmpaterne.'
                    <br>IFU:'.$part->numifupatern.'
                    <br>'.$part->divfiscal.'
                    <br>'.$part->regifiscal.'
                    </h2>', '',
                    0, false, 1, 'L', false);
                // --------------------------------------------------------
                $pdf->SetFont('helvetica', 'I', 10);
                $table = '<table border="1" cellpadding="3" align="left">
                  <tbody>
                    <tr>
                        <th width="30%" height="50px" align="center"><strong>Date</strong></th>
                        <th width="19%" align="center"><strong>N° Quittance</strong></th>
                        <th width="11%" align="center"><strong>Nbre colis</strong></th>
                        <th width="15%" align="center"><strong>Prix unitaire</strong></th>
                        <th width="15%" align="center"><strong>Prix Total</strong></th>
                    </tr>';
                            
                     $pdf->SetFont('helvetica', '', 13);
                    $nb = 0;
                    $m = 0;
                    foreach ($oncours as $departcour => $elemt) {
                        $table .= '<tr>
                      
                          <td width="30%" align="left" height="100px"><strong>' . urldecode($elemt->dateenvoi) . '</strong></td>
                          <td width="19%" align="left"><strong>' . $elemt->naturecourrier . '</strong></td>
                          <td width="11%" align="right"><strong>' . $elemt->nbcol . '</strong></td>
                          <td width="15%" align="right"><strong>' . number_format($punitaire, 0, '', ' ') . '</strong></td>
                          <td width="15%" align="right"><strong>' . number_format(round($elemt->nbcol)*$punitaire, 0, '', ' ') . '</strong></td>
                          </tr>';
                            $nb +=round($elemt->nbcol);
                            $m += ($elemt->nbcol*$punitaire);

                    }
        
                  
                    $table .= '<tr>
                        <td width="75%" height="40px" align="center"><strong>TOTAL HT</strong></td>
                        <td width="15%" align="right"><strong>'.number_format($m, 0, '', ' ').'</strong></td>
                        
                        </tr>';
                    $table .= '</tbody></table>';
                    //$pdf->SetFont('helvetica', 'I', 11);
                $pdf->writeHTMLCell(200, 0, 15, 120, $table, '', 0, false, 0, 'L', false);
                //var_dump($oncours);
                $pdf->SetFont('helvetica', 'U', 15);
            
                $pdf->writeHTMLCell(110, 0, 15, 182,
                    'Arrêtée la présente facture à la somme de '
                    , '',
                    0, false, 1, 'L', false);
                $pdf->SetFont('helvetica', '', 15);

                $pdf->writeHTMLCell(80, 0, 110, 182,
                    '<h4>:  '.enlettres($m, NEL_RECTIF_1990).' ('.$m.') F CFA</h4> '
                    , '',
                    0, false, 1, 'R', true);
                            
                
                
                // RESPONSABLE CHARGE
                $pdf->SetFont('helvetica', 'U', 17);
                $pdf->writeHTMLCell(50, 0, 130, 203,
                    'Le Transporteur<br>' ,
                    '',
                    0, false, 0, 'R', false);
                    
                // Close and output PDF document
                // This method has several options, check the source code documentation for more information.
                $pdf->Output('FACT' .$g. '.' . mdate("%m.%d.%Y", now()) . '', 'I');
           
            
        }

    }
    /** End of file: Etatfactures.php **/
    /** File location: application/controllers/Etatfactures.php **/