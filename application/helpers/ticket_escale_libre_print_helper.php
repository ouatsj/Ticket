<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Impression ticket escale libre — PDF 57×40 mm pour TPE POSPrinter.
 * Texte ASCII-safe (tiret au lieu de →), police DejaVu, zone utile centrée ~46 mm.
 */
if (!function_exists('ticket_escale_libre_resolve_logo')) {
    /**
     * @param string $logo
     * @return string|null
     */
    function ticket_escale_libre_resolve_logo($logo)
    {
        $logo = trim(str_replace('\\', '/', (string) $logo));
        if ($logo === '') {
            return null;
        }
        if (preg_match('#^https?://#i', $logo)) {
            return null;
        }
        $logo = ltrim($logo, '/');
        $candidates = array(
            FCPATH . $logo,
            FCPATH . 'public_html/' . $logo,
        );
        foreach ($candidates as $path) {
            if (is_file($path) && is_readable($path)) {
                return $path;
            }
        }
        return null;
    }
}

if (!function_exists('ticket_escale_libre_pos_text')) {
    /**
     * Normalise le texte pour POSPrinter (pas de → / · / tirets Unicode → sinon "?") .
     *
     * @param string $text
     * @param bool $upper
     * @return string
     */
    function ticket_escale_libre_pos_text($text, $upper = true)
    {
        $text = (string) $text;
        if ($text === '') {
            return '';
        }

        // Flèches / puces / tirets typographiques → ASCII
        $map = array(
            "\xE2\x86\x92" => ' - ', // →
            "\xE2\x86\x90" => ' - ', // ←
            "\xE2\x86\x94" => ' - ', // ↔
            '→' => ' - ',
            '←' => ' - ',
            '↔' => ' - ',
            '·' => ' ',
            '•' => ' ',
            '–' => '-',
            '—' => '-',
            '…' => '...',
            '’' => "'",
            '‘' => "'",
            '“' => '"',
            '”' => '"',
            "\xC2\xA0" => ' ', // NBSP
        );
        $text = strtr($text, $map);

        // Autres symboles hors Latin courant → espace / tiret
        $text = preg_replace('/[\x{2190}-\x{21FF}]/u', ' - ', $text);
        $text = preg_replace('/\s*-\s*/', ' - ', $text);
        $text = preg_replace('/\s+/u', ' ', $text);
        $text = trim($text);

        if ($upper) {
            if (function_exists('mb_strtoupper')) {
                $text = mb_strtoupper($text, 'UTF-8');
            } else {
                $text = strtoupper($text);
            }
        }

        return $text;
    }
}

if (!function_exists('ticket_escale_libre_build_pdf')) {
    /**
     * @param object $item
     * @return TCPDF
     */
    function ticket_escale_libre_build_pdf($item)
    {
        if (!class_exists('TCPDF', false)) {
            require_once APPPATH . 'libraries/tcpdf/tcpdf.php';
        }

        $od = trim(preg_replace('/^\[LIBRE\]\s*/', '', (string) (isset($item->quartier_escal) ? $item->quartier_escal : '')));
        $od = ticket_escale_libre_pos_text($od, true);

        $prix = number_format((float) $item->prixescal, 0, '', ' ');
        $passager = ticket_escale_libre_pos_text(
            trim((string) $item->nom_client . ' ' . (string) $item->prenom_client),
            true
        );
        $compagnie = !empty($item->nom_compagnie)
            ? ticket_escale_libre_pos_text((string) $item->nom_compagnie, true)
            : '';
        $tel = !empty($item->contact_client)
            ? ticket_escale_libre_pos_text((string) $item->contact_client, false)
            : '';
        $code = ticket_escale_libre_pos_text((string) $item->idclescal, false);

        if (!empty($item->dateheureescal) && $item->dateheureescal !== '0000-00-00 00:00:00') {
            $emis = (string) $item->dateheureescal;
        } else {
            $emis = mdate('%Y-%m-%d %H:%i:%s', now('UTC'));
        }
        $emis = ticket_escale_libre_pos_text($emis, false);

        // Page papier 57×40 — zone imprimable POSPrinter souvent ~48 mm → contenu 46 mm centré
        $page_w = 57.0;
        $page_h = 40.0;
        $content_w = 46.0;
        $margin_x = ($page_w - $content_w) / 2.0; // 5.5 mm chaque côté

        $pdf = new TCPDF('P', 'mm', array($page_w, $page_h), true, 'UTF-8', false);
        $pdf->SetCreator('Rakieta Bus');
        $pdf->SetAuthor('Rakieta Bus');
        $pdf->SetTitle('TICKET-' . $code);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins($margin_x, 1.0, $margin_x);
        $pdf->SetAutoPageBreak(false, 0);
        if (defined('PDF_IMAGE_SCALE_RATIO')) {
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        }
        $pdf->AddPage('P', array($page_w, $page_h));
        $pdf->SetTextColor(0, 0, 0);

        // DejaVu : accents OK (extrême, Ouagadougou…) — Helvetica core → "?"
        $font = 'dejavusans';
        $fontb = 'dejavusans';

        $y = 1.0;

        // Logo centré dans la zone utile
        $logo_zone_h = 0.0;
        $logo_path = !empty($item->logo) ? ticket_escale_libre_resolve_logo($item->logo) : null;
        if ($logo_path) {
            $logo_max_w = 20.0;
            $logo_max_h = 5.5;
            $logo_x = ($page_w - $logo_max_w) / 2.0;
            try {
                $pdf->Image(
                    $logo_path,
                    $logo_x,
                    $y,
                    $logo_max_w,
                    $logo_max_h,
                    '',
                    '',
                    '',
                    false,
                    150,
                    '',
                    false,
                    false,
                    0,
                    'CM',
                    false,
                    false
                );
                $logo_zone_h = $logo_max_h + 0.5;
            } catch (Exception $e) {
                $logo_zone_h = 0.0;
            }
        }
        $y += $logo_zone_h;

        if ($compagnie !== '') {
            $pdf->SetFont($fontb, 'B', 6);
            $pdf->SetXY($margin_x, $y);
            $pdf->Cell($content_w, 2.4, $compagnie, 0, 2, 'C', false, '', 0);
            $y = $pdf->GetY() + 0.3;
        }

        // Trajet : "DISSIN - DANO (ESCALE)" — tiret ASCII, centré
        $pdf->SetFont($fontb, 'B', 7.5);
        $pdf->SetXY($margin_x, $y);
        $pdf->MultiCell(
            $content_w,
            2.6,
            $od,
            0,
            'C',
            false,
            1,
            $margin_x,
            $y,
            true,
            0,
            false,
            true,
            5.6,
            'M',
            true
        );
        $y = $pdf->GetY() + 0.3;

        $pdf->SetFont($fontb, 'B', 6.5);
        $pdf->SetXY($margin_x, $y);
        $pdf->MultiCell(
            $content_w,
            2.4,
            $passager,
            0,
            'C',
            false,
            1,
            $margin_x,
            $y,
            true,
            0,
            false,
            true,
            5.0,
            'M',
            true
        );
        $y = $pdf->GetY() + 0.15;

        if ($tel !== '') {
            $pdf->SetFont($font, '', 6);
            $pdf->SetXY($margin_x, $y);
            $pdf->Cell($content_w, 2.2, $tel, 0, 2, 'C', false, '', 0);
            $y = $pdf->GetY() + 0.2;
        }

        $pdf->SetFont($fontb, 'B', 8.5);
        $pdf->SetXY($margin_x, $y);
        $pdf->Cell($content_w, 2.8, $prix . ' FCFA', 0, 2, 'C', false, '', 0);
        $y = $pdf->GetY() + 0.2;

        $pdf->SetFont($fontb, 'B', 6);
        $pdf->SetXY($margin_x, $y);
        $pdf->Cell($content_w, 2.2, $code, 0, 2, 'C', false, '', 0);
        $y = $pdf->GetY() + 0.2;

        $bar_h = 4.8;
        $bar_w = min(42.0, $content_w);
        $bar_x = ($page_w - $bar_w) / 2.0;
        if ($y + $bar_h + 2.6 > $page_h - 0.6) {
            $y = max(1.0, $page_h - $bar_h - 3.0);
        }
        $style = array(
            'position' => '',
            'align' => 'C',
            'stretch' => false,
            'fitwidth' => true,
            'cellfitalign' => 'C',
            'border' => false,
            'hpadding' => 0,
            'vpadding' => 0,
            'fgcolor' => array(0, 0, 0),
            'bgcolor' => false,
            'text' => false,
        );
        $pdf->write1DBarcode($code, 'C128', $bar_x, $y, $bar_w, $bar_h, 0.35, $style, 'N');
        $y += $bar_h + 0.3;

        $pdf->SetFont($font, '', 5);
        if ($y > $page_h - 2.2) {
            $y = $page_h - 2.2;
        }
        $pdf->SetXY($margin_x, $y);
        $pdf->Cell($content_w, 1.8, $emis, 0, 0, 'C', false, '', 0);

        return $pdf;
    }
}

if (!function_exists('ticket_escale_libre_output_pdf')) {
    /**
     * @param object $item
     * @param string $filename
     * @return void
     */
    function ticket_escale_libre_output_pdf($item, $filename = 'ticket-escale.pdf')
    {
        $pdf = ticket_escale_libre_build_pdf($item);
        while (ob_get_level() > 0) {
            @ob_end_clean();
        }
        $pdf->Output($filename, 'I');
    }
}
