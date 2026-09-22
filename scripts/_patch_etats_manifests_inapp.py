#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""Lot F: manifests hebdos → in-app."""
from pathlib import Path
import re

path = Path(__file__).resolve().parents[1] / 'application/controllers/Rapport.php'
src = path.read_text(encoding='utf-8', errors='replace')


def replace_method(src, method_name, new_code):
    pat = re.compile(r'(?m)^        public function ' + re.escape(method_name) + r'\(')
    m = pat.search(src)
    if not m:
        raise SystemExit('Missing method: ' + method_name)
    brace_open = src.find('{', m.end())
    if brace_open < 0:
        raise SystemExit('No { for ' + method_name)
    depth = 0
    i = brace_open
    while i < len(src):
        c = src[i]
        if c == '{':
            depth += 1
        elif c == '}':
            depth -= 1
            if depth == 0:
                end = i + 1
                while end < len(src) and src[end] in '\r\n':
                    end += 1
                return src[:m.start()] + new_code.rstrip() + '\n\n' + src[end:]
        i += 1
    raise SystemExit('Unbalanced braces for ' + method_name)


QS_CONNECT = """'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),"""

RETOUR = """'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),"""

COLS = """'columns' => array(
                    array('key' => 'date', 'label' => 'Date', 'align' => 'left'),
                    array('key' => 'ligne', 'label' => 'Ligne', 'align' => 'left'),
                    array('key' => 'nbr', 'label' => 'Nombre', 'align' => 'center'),
                    array('key' => 'pu', 'label' => 'Prix unitaire', 'align' => 'right', 'money' => true),
                    array('key' => 'montant', 'label' => 'Prix total', 'align' => 'right', 'money' => true),
                ),"""

DATE_FMT = """$parts = explode('-', (string) $rawDate);
                $dateAff = (count($parts) === 3) ? ($parts[2] . '-' . $parts[1] . '-' . $parts[0]) : (string) $rawDate;"""

code_manifesthebdo = f'''
        protected function _manifesthebdo_payload($ckey, $g)
        {{
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebut'));
            $dt2 = trim((string) $this->input->get_post('datefin'));
            $lign = trim((string) $this->input->get_post('axeligne'));
            $comp = trim((string) $this->input->get_post('_compag'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgar'));
            if ($gid === '') {{
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }}
            $this->_assert_recap_global_filters($this->entreprise->ekey, $dt1, $dt2, $comp);
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            $isAdmin = ($this->session->agent->userole === '1' || $this->session->agent->userole === '2');
            if ($isAdmin) {{
                if ($comp == 5002) {{
                    $reportick = $this->m_passager->nifesthebad($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
                    $reportickretors = $this->m_non_passager->reporticketretour($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
                }} else {{
                    $reportick = $this->m_passager->nifesthebcptadmin($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
                    $reportickretors = $this->m_non_passager->reporticketretourcptadmin($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
                }}
            }} elseif ($comp == 5002) {{
                $reportick = $this->m_passager->nifesthebad($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
                $reportickretors = $this->m_non_passager->reporticketretour($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
            }} else {{
                $reportick = $this->m_passager->nifestheb($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
                $reportickretors = $this->m_non_passager->reporticketretourcpt($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
            }}
            if (!is_array($reportick)) {{
                $reportick = array();
            }}
            if (!is_array($reportickretors)) {{
                $reportickretors = array();
            }}
            $lignes = array();
            $total = 0.0;
            foreach ($reportick as $lement) {{
                $rawDate = isset($lement->datep_create) ? $lement->datep_create : '';
                {DATE_FMT}
                $nbr = isset($lement->codepassager) ? (int) round((float) $lement->codepassager) : 0;
                $pu = isset($lement->prixvente) ? (float) $lement->prixvente : 0.0;
                $mt = $nbr * $pu;
                $lignes[] = array(
                    'date' => $dateAff,
                    'ligne' => isset($lement->nom_ligne) ? (string) $lement->nom_ligne : '',
                    'nbr' => $nbr,
                    'pu' => $pu,
                    'montant' => $mt,
                );
                $total += $mt;
            }}
            foreach ($reportickretors as $etatretou) {{
                $nbr = isset($etatretou->code_non_pass) ? (int) round((float) $etatretou->code_non_pass) : 0;
                $pu = isset($etatretou->prixretour) ? (float) $etatretou->prixretour : 0.0;
                $mt = $nbr * $pu;
                $lignes[] = array(
                    'date' => '',
                    'ligne' => $this->_recap_invert_ligne_nom(isset($etatretou->nom_ligne) ? $etatretou->nom_ligne : ''),
                    'nbr' => $nbr,
                    'pu' => $pu,
                    'montant' => $mt,
                );
                $total += $mt;
            }}
            $qs = http_build_query(array_filter(array(
                'datedebut' => $dt1,
                'datefin' => $dt2,
                'axeligne' => $lign,
                '_compag' => $comp,
                'departgar' => $gid,
                {QS_CONNECT}
            )));
            return array(
                'titre' => 'MANIFEST TICKET ' . $cieNom . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                {RETOUR}
                {COLS}
                'export_base' => site_url('Rapport/manifesthebdo_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }}

        public function manifesthebdo($ckey, $g)
        {{
            return $this->_etat_render_view('Manifest ticket', $this->_manifesthebdo_payload($ckey, $g));
        }}

        public function manifesthebdo_export($ckey, $g)
        {{
            $this->_etat_export_dispatch($this->_manifesthebdo_payload($ckey, $g));
        }}
'''

code_manifesthebdoesc = f'''
        protected function _manifesthebdoesc_payload($ckey, $g)
        {{
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebutesc'));
            $dt2 = trim((string) $this->input->get_post('datefinesc'));
            $lign = trim((string) $this->input->get_post('axeligneesc'));
            $comp = trim((string) $this->input->get_post('_compagesc'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgaresc'));
            if ($gid === '') {{
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }}
            $this->_assert_recap_global_filters($this->entreprise->ekey, $dt1, $dt2, $comp);
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            $reportick = $this->m_escalclients->nifestheb($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $lign);
            if (!is_array($reportick)) {{
                $reportick = array();
            }}
            $lignes = array();
            $total = 0.0;
            foreach ($reportick as $lement) {{
                $rawDate = isset($lement->datedepescal) ? $lement->datedepescal : '';
                {DATE_FMT}
                $nbr = isset($lement->escalp) ? (int) round((float) $lement->escalp) : 0;
                $pu = isset($lement->prixescal) ? (float) $lement->prixescal : 0.0;
                $mt = $nbr * $pu;
                $lignes[] = array(
                    'date' => $dateAff,
                    'ligne' => isset($lement->nom_ligne) ? (string) $lement->nom_ligne : '',
                    'nbr' => $nbr,
                    'pu' => $pu,
                    'montant' => $mt,
                );
                $total += $mt;
            }}
            $qs = http_build_query(array_filter(array(
                'datedebutesc' => $dt1,
                'datefinesc' => $dt2,
                'axeligneesc' => $lign,
                '_compagesc' => $comp,
                'departgaresc' => $gid,
                {QS_CONNECT}
            )));
            return array(
                'titre' => 'MANIFEST TICKET ESCAL ' . $cieNom . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                {RETOUR}
                {COLS}
                'export_base' => site_url('Rapport/manifesthebdoesc_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }}

        public function manifesthebdoesc($ckey, $g)
        {{
            return $this->_etat_render_view('Manifest ticket escal', $this->_manifesthebdoesc_payload($ckey, $g));
        }}

        public function manifesthebdoesc_export($ckey, $g)
        {{
            $this->_etat_export_dispatch($this->_manifesthebdoesc_payload($ckey, $g));
        }}
'''

code_courriermanifestheb = f'''
        protected function _courriermanifestheb_payload($ckey, $g)
        {{
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebutheb'));
            $dt2 = trim((string) $this->input->get_post('datefinheb'));
            $lign = trim((string) $this->input->get_post('axeligneheb'));
            $comp = trim((string) $this->input->get_post('_compagheb'));
            $tyc = trim((string) $this->input->get_post('typcoursheb'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgarheb'));
            if ($gid === '') {{
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }}
            $this->_assert_recap_global_filters($this->entreprise->ekey, $dt1, $dt2, $comp);
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            if ($tyc === 'Gros_plis') {{
                $ty3 = 'COLIS';
            }} elseif ($tyc === 'Petit_plis') {{
                $ty3 = 'PLIS';
            }} elseif ($tyc === '') {{
                $ty3 = 'PLIS/COLIS';
            }} else {{
                $ty3 = $tyc;
            }}
            $rows = $this->m_courrier_expedier->recaptexopliheb($this->entreprise->ekey, $dt1, $dt2, $gid, $comp, $tyc, $lign);
            if (!is_array($rows)) {{
                $rows = array();
            }}
            $lignes = array();
            $total = 0.0;
            foreach ($rows as $element) {{
                $rawDate = isset($element->dateenvoi) ? $element->dateenvoi : '';
                {DATE_FMT}
                $nbr = isset($element->nombres) ? (int) round((float) $element->nombres) : 0;
                $pu = isset($element->prixcolis) ? (float) $element->prixcolis : 0.0;
                $mt = $nbr * $pu;
                $lignes[] = array(
                    'date' => $dateAff,
                    'ligne' => isset($element->nom_ligne) ? (string) $element->nom_ligne : '',
                    'nbr' => $nbr,
                    'pu' => $pu,
                    'montant' => $mt,
                );
                $total += $mt;
            }}
            $qs = http_build_query(array_filter(array(
                'datedebutheb' => $dt1,
                'datefinheb' => $dt2,
                'axeligneheb' => $lign,
                '_compagheb' => $comp,
                'departgarheb' => $gid,
                'typcoursheb' => $tyc,
                {QS_CONNECT}
            )));
            return array(
                'titre' => 'MANIFEST ' . $cieNom . ' ' . $ty3 . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                {RETOUR}
                {COLS}
                'export_base' => site_url('Rapport/courriermanifestheb_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }}

        public function courriermanifestheb($ckey, $g)
        {{
            return $this->_etat_render_view('Manifest courrier', $this->_courriermanifestheb_payload($ckey, $g));
        }}

        public function courriermanifestheb_export($ckey, $g)
        {{
            $this->_etat_export_dispatch($this->_courriermanifestheb_payload($ckey, $g));
        }}
'''

code_courriermanifesthebesc = f'''
        protected function _courriermanifesthebesc_payload($ckey, $g)
        {{
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebuthebesc'));
            $dt2 = trim((string) $this->input->get_post('datefinhebesc'));
            $lign = trim((string) $this->input->get_post('axelignehebesc'));
            $comp = trim((string) $this->input->get_post('_compaghebesc'));
            $tyc = trim((string) $this->input->get_post('typcourshebesc'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgarhebesc'));
            if ($gid === '') {{
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }}
            $this->_assert_recap_global_filters($this->entreprise->ekey, $dt1, $dt2, $comp);
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            if ($tyc === 'Gros_plis') {{
                $ty3 = 'COLIS';
            }} elseif ($tyc === 'Petit_plis') {{
                $ty3 = 'PLIS';
            }} elseif ($tyc === '') {{
                $ty3 = 'PLIS/COLIS';
            }} else {{
                $ty3 = $tyc;
            }}
            $rows = $this->m_courrier_expedieresc->recaptexopliheb($this->entreprise->ekey, $dt1, $dt2, $gid, $comp, $tyc, $lign);
            if (!is_array($rows)) {{
                $rows = array();
            }}
            $lignes = array();
            $total = 0.0;
            foreach ($rows as $element) {{
                $rawDate = isset($element->dateenvoiesc) ? $element->dateenvoiesc : '';
                {DATE_FMT}
                $nbr = isset($element->nombresesc) ? (int) round((float) $element->nombresesc) : 0;
                $pu = isset($element->prixcolisesc) ? (float) $element->prixcolisesc : 0.0;
                $mt = $nbr * $pu;
                $lignes[] = array(
                    'date' => $dateAff,
                    'ligne' => isset($element->nom_ligne) ? (string) $element->nom_ligne : '',
                    'nbr' => $nbr,
                    'pu' => $pu,
                    'montant' => $mt,
                );
                $total += $mt;
            }}
            $qs = http_build_query(array_filter(array(
                'datedebuthebesc' => $dt1,
                'datefinhebesc' => $dt2,
                'axelignehebesc' => $lign,
                '_compaghebesc' => $comp,
                'departgarhebesc' => $gid,
                'typcourshebesc' => $tyc,
                {QS_CONNECT}
            )));
            return array(
                'titre' => 'MANIFEST ESCAL  ' . $cieNom . ' ' . $ty3 . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                {RETOUR}
                {COLS}
                'export_base' => site_url('Rapport/courriermanifesthebesc_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }}

        public function courriermanifesthebesc($ckey, $g)
        {{
            return $this->_etat_render_view('Manifest courrier escal', $this->_courriermanifesthebesc_payload($ckey, $g));
        }}

        public function courriermanifesthebesc_export($ckey, $g)
        {{
            $this->_etat_export_dispatch($this->_courriermanifesthebesc_payload($ckey, $g));
        }}
'''

code_bagagemanifestheb = f'''
        protected function _bagagemanifestheb_payload($ckey, $g)
        {{
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebuthebbg'));
            $dt2 = trim((string) $this->input->get_post('datefinhebbg'));
            $lign = trim((string) $this->input->get_post('axelignehebbg'));
            $comp = trim((string) $this->input->get_post('_compaghebbg'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgarhebbg'));
            if ($gid === '') {{
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }}
            $this->_assert_recap_global_filters($this->entreprise->ekey, $dt1, $dt2, $comp);
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            $rows = $this->m_bagage->recaptexobgheb($this->entreprise->ekey, $dt1, $dt2, $gid, $comp, $lign);
            if (!is_array($rows)) {{
                $rows = array();
            }}
            $lignes = array();
            $total = 0.0;
            foreach ($rows as $element) {{
                $rawDate = isset($element->date_create) ? $element->date_create : '';
                {DATE_FMT}
                $nbr = isset($element->codid_bagage) ? (int) round((float) $element->codid_bagage) : 0;
                $pu = isset($element->prix_bagage) ? (float) $element->prix_bagage : 0.0;
                $mt = $nbr * $pu;
                $lignes[] = array(
                    'date' => $dateAff,
                    'ligne' => isset($element->nom_ligne) ? (string) $element->nom_ligne : '',
                    'nbr' => $nbr,
                    'pu' => $pu,
                    'montant' => $mt,
                );
                $total += $mt;
            }}
            $qs = http_build_query(array_filter(array(
                'datedebuthebbg' => $dt1,
                'datefinhebbg' => $dt2,
                'axelignehebbg' => $lign,
                '_compaghebbg' => $comp,
                'departgarhebbg' => $gid,
                {QS_CONNECT}
            )));
            return array(
                'titre' => 'MANIFEST BAGAGES ' . $cieNom . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                {RETOUR}
                {COLS}
                'export_base' => site_url('Rapport/bagagemanifestheb_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }}

        public function bagagemanifestheb($ckey, $g)
        {{
            return $this->_etat_render_view('Manifest bagages', $this->_bagagemanifestheb_payload($ckey, $g));
        }}

        public function bagagemanifestheb_export($ckey, $g)
        {{
            $this->_etat_export_dispatch($this->_bagagemanifestheb_payload($ckey, $g));
        }}
'''

code_bagageescmanifestheb = f'''
        protected function _bagageescmanifestheb_payload($ckey, $g)
        {{
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebuthebbge'));
            $dt2 = trim((string) $this->input->get_post('datefinhebbge'));
            $lign = trim((string) $this->input->get_post('axelignehebbge'));
            $comp = trim((string) $this->input->get_post('_compaghebbge'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgarhebbge'));
            if ($gid === '') {{
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }}
            $this->_assert_recap_global_filters($this->entreprise->ekey, $dt1, $dt2, $comp);
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            $rows = $this->m_bagageesc->recaptexobgescheb($this->entreprise->ekey, $dt1, $dt2, $gid, $comp, $lign);
            if (!is_array($rows)) {{
                $rows = array();
            }}
            $lignes = array();
            $total = 0.0;
            foreach ($rows as $element) {{
                $rawDate = isset($element->date_createesc) ? $element->date_createesc : '';
                {DATE_FMT}
                $nbr = isset($element->codid_bagageesc) ? (int) round((float) $element->codid_bagageesc) : 0;
                $pu = isset($element->prix_bagageesc) ? (float) $element->prix_bagageesc : 0.0;
                $mt = $nbr * $pu;
                $lignes[] = array(
                    'date' => $dateAff,
                    'ligne' => isset($element->nom_ligne) ? (string) $element->nom_ligne : '',
                    'nbr' => $nbr,
                    'pu' => $pu,
                    'montant' => $mt,
                );
                $total += $mt;
            }}
            $qs = http_build_query(array_filter(array(
                'datedebuthebbge' => $dt1,
                'datefinhebbge' => $dt2,
                'axelignehebbge' => $lign,
                '_compaghebbge' => $comp,
                'departgarhebbge' => $gid,
                {QS_CONNECT}
            )));
            return array(
                'titre' => 'MANIFEST BAGAGESESCAL ' . $cieNom . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                {RETOUR}
                {COLS}
                'export_base' => site_url('Rapport/bagageescmanifestheb_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }}

        public function bagageescmanifestheb($ckey, $g)
        {{
            return $this->_etat_render_view('Manifest bagages escal', $this->_bagageescmanifestheb_payload($ckey, $g));
        }}

        public function bagageescmanifestheb_export($ckey, $g)
        {{
            $this->_etat_export_dispatch($this->_bagageescmanifestheb_payload($ckey, $g));
        }}
'''

replacements = [
    ('manifesthebdo', code_manifesthebdo),
    ('manifesthebdoesc', code_manifesthebdoesc),
    ('courriermanifestheb', code_courriermanifestheb),
    ('courriermanifesthebesc', code_courriermanifesthebesc),
    ('bagagemanifestheb', code_bagagemanifestheb),
    ('bagageescmanifestheb', code_bagageescmanifestheb),
]

for name, code in replacements:
    src = replace_method(src, name, code)
    print('OK', name)

path.write_text(src, encoding='utf-8')
print('Wrote', path)
