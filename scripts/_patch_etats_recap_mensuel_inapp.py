#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""Lot D: RECAP EX MENSUEL compagnie (ticket/bagage/courrier + escal) → in-app."""
from pathlib import Path
import re

path = Path(__file__).resolve().parents[1] / 'application/controllers/Rapport.php'
src = path.read_text(encoding='utf-8', errors='replace')


def replace_method(src, method_name, new_code):
    pat = re.compile(r'(?m)^        public function ' + re.escape(method_name) + r'\(')
    m = pat.search(src)
    if not m:
        raise SystemExit('Missing method: ' + method_name)
    nxt = re.search(r'(?m)^        (?:public|protected|private) function ', src[m.end():])
    if not nxt:
        raise SystemExit('No next method after: ' + method_name)
    end = m.end() + nxt.start()
    return src[:m.start()] + new_code.rstrip() + '\n\n' + src[end:]


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
                    array('key' => 'ligne', 'label' => 'Ligne', 'align' => 'left'),
                    array('key' => 'nbr', 'label' => 'Nombre', 'align' => 'center'),
                    array('key' => 'pu', 'label' => 'Prix unitaire', 'align' => 'right', 'money' => true),
                    array('key' => 'montant', 'label' => 'Prix total', 'align' => 'right', 'money' => true),
                ),"""

code_exercices = f'''
        protected function _exercices_payload($ckey, $g)
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
                    $reportick = $this->m_passager->reporticket($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
                    $reportickretors = $this->m_non_passager->reporticketretour($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
                }} else {{
                    $reportick = $this->m_passager->reporticketcptadmin($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
                    $reportickretors = $this->m_non_passager->reporticketretourcptadmin($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
                }}
            }} elseif ($comp == 5002) {{
                $reportick = $this->m_passager->reporticket($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
                $reportickretors = $this->m_non_passager->reporticketretour($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
            }} else {{
                $reportick = $this->m_passager->reporticketcpt($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
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
                $nbr = isset($lement->codepassager) ? (int) round((float) $lement->codepassager) : 0;
                $pu = isset($lement->prixvente) ? (float) $lement->prixvente : 0.0;
                $mt = $nbr * $pu;
                $lignes[] = array(
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
                'titre' => 'RECAP EX MENSUEL TICKET ' . $cieNom . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                {RETOUR}
                {COLS}
                'export_base' => site_url('Rapport/exercices_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }}

        public function exercices($ckey, $g)
        {{
            return $this->_etat_render_view('Récap ex mensuel ticket', $this->_exercices_payload($ckey, $g));
        }}

        public function exercices_export($ckey, $g)
        {{
            $this->_etat_export_dispatch($this->_exercices_payload($ckey, $g));
        }}
'''

code_exerciceses = f'''
        protected function _exerciceses_payload($ckey, $g)
        {{
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebutes'));
            $dt2 = trim((string) $this->input->get_post('datefines'));
            $lign = trim((string) $this->input->get_post('axelignees'));
            $comp = trim((string) $this->input->get_post('_compages'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgares'));
            if ($gid === '') {{
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }}
            $this->_assert_recap_global_filters($this->entreprise->ekey, $dt1, $dt2, $comp);
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            $reportick = $this->m_escalclients->reporticketcpt($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
            if (!is_array($reportick)) {{
                $reportick = array();
            }}
            $lignes = array();
            $total = 0.0;
            foreach ($reportick as $lement) {{
                $nbr = isset($lement->escalp) ? (int) round((float) $lement->escalp) : 0;
                $pu = isset($lement->prixescal) ? (float) $lement->prixescal : 0.0;
                $mt = $nbr * $pu;
                $lignes[] = array(
                    'ligne' => isset($lement->nom_ligne) ? (string) $lement->nom_ligne : '',
                    'nbr' => $nbr,
                    'pu' => $pu,
                    'montant' => $mt,
                );
                $total += $mt;
            }}
            $qs = http_build_query(array_filter(array(
                'datedebutes' => $dt1,
                'datefines' => $dt2,
                'axelignees' => $lign,
                '_compages' => $comp,
                'departgares' => $gid,
                {QS_CONNECT}
            )));
            return array(
                'titre' => 'RECAP EX MENSUEL TICKET ESCAL ' . $cieNom . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                {RETOUR}
                {COLS}
                'export_base' => site_url('Rapport/exerciceses_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }}

        public function exerciceses($ckey, $g)
        {{
            return $this->_etat_render_view('Récap ex mensuel ticket escal', $this->_exerciceses_payload($ckey, $g));
        }}

        public function exerciceses_export($ckey, $g)
        {{
            $this->_etat_export_dispatch($this->_exerciceses_payload($ckey, $g));
        }}
'''

code_exercicesbag = f'''
        protected function _exercicesbag_payload($ckey, $g)
        {{
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebutbag'));
            $dt2 = trim((string) $this->input->get_post('datefinbag'));
            $lign = trim((string) $this->input->get_post('axelignebag'));
            $comp = trim((string) $this->input->get_post('_compagbag'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgarbag'));
            if ($gid === '') {{
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }}
            $this->_assert_recap_global_filters($this->entreprise->ekey, $dt1, $dt2, $comp);
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            $reportick = $this->m_bagage->reportbgcpt($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
            if (!is_array($reportick)) {{
                $reportick = array();
            }}
            $lignes = array();
            $total = 0.0;
            foreach ($reportick as $lement) {{
                $nbr = isset($lement->codid_bagage) ? (int) round((float) $lement->codid_bagage) : 0;
                $pu = isset($lement->prix_bagage) ? (float) $lement->prix_bagage : 0.0;
                $mt = $nbr * $pu;
                $lignes[] = array(
                    'ligne' => isset($lement->nom_ligne) ? (string) $lement->nom_ligne : '',
                    'nbr' => $nbr,
                    'pu' => $pu,
                    'montant' => $mt,
                );
                $total += $mt;
            }}
            $qs = http_build_query(array_filter(array(
                'datedebutbag' => $dt1,
                'datefinbag' => $dt2,
                'axelignebag' => $lign,
                '_compagbag' => $comp,
                'departgarbag' => $gid,
                {QS_CONNECT}
            )));
            return array(
                'titre' => 'RECAP EX MENSUEL BABAGE ' . $cieNom . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                {RETOUR}
                {COLS}
                'export_base' => site_url('Rapport/exercicesbag_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }}

        public function exercicesbag($ckey, $g)
        {{
            return $this->_etat_render_view('Récap ex mensuel bagage', $this->_exercicesbag_payload($ckey, $g));
        }}

        public function exercicesbag_export($ckey, $g)
        {{
            $this->_etat_export_dispatch($this->_exercicesbag_payload($ckey, $g));
        }}
'''

code_exercicesbagesc = f'''
        protected function _exercicesbagesc_payload($ckey, $g)
        {{
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebutbagesc'));
            $dt2 = trim((string) $this->input->get_post('datefinbagesc'));
            $lign = trim((string) $this->input->get_post('axelignebagesc'));
            $comp = trim((string) $this->input->get_post('_compagbagesc'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgarbagesc'));
            if ($gid === '') {{
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }}
            $this->_assert_recap_global_filters($this->entreprise->ekey, $dt1, $dt2, $comp);
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            $reportick = $this->m_bagageesc->reportbgcpt($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
            if (!is_array($reportick)) {{
                $reportick = array();
            }}
            $lignes = array();
            $total = 0.0;
            foreach ($reportick as $lement) {{
                $nbr = isset($lement->codid_bagageesc) ? (int) round((float) $lement->codid_bagageesc) : 0;
                $pu = isset($lement->prix_bagageesc) ? (float) $lement->prix_bagageesc : 0.0;
                $mt = $nbr * $pu;
                $lignes[] = array(
                    'ligne' => isset($lement->nom_ligne) ? (string) $lement->nom_ligne : '',
                    'nbr' => $nbr,
                    'pu' => $pu,
                    'montant' => $mt,
                );
                $total += $mt;
            }}
            $qs = http_build_query(array_filter(array(
                'datedebutbagesc' => $dt1,
                'datefinbagesc' => $dt2,
                'axelignebagesc' => $lign,
                '_compagbagesc' => $comp,
                'departgarbagesc' => $gid,
                {QS_CONNECT}
            )));
            return array(
                'titre' => 'RECAP EX MENSUEL BABAGEESCAL ' . $cieNom . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                {RETOUR}
                {COLS}
                'export_base' => site_url('Rapport/exercicesbagesc_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }}

        public function exercicesbagesc($ckey, $g)
        {{
            return $this->_etat_render_view('Récap ex mensuel bagage escal', $this->_exercicesbagesc_payload($ckey, $g));
        }}

        public function exercicesbagesc_export($ckey, $g)
        {{
            $this->_etat_export_dispatch($this->_exercicesbagesc_payload($ckey, $g));
        }}
'''

code_exocourrier = f'''
        protected function _exocourrier_payload($ckey, $g)
        {{
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebutcr'));
            $dt2 = trim((string) $this->input->get_post('datefincr'));
            $lign = trim((string) $this->input->get_post('axelignecr'));
            $comp = trim((string) $this->input->get_post('_compagcr'));
            $tyc = trim((string) $this->input->get_post('typcours'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgarcr'));
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
            $recapcourrier = $this->m_courrier_expedier->recaptexopli($this->entreprise->ekey, $dt1, $dt2, $gid, $comp, $tyc, $lign);
            if (!is_array($recapcourrier)) {{
                $recapcourrier = array();
            }}
            $lignes = array();
            $total = 0.0;
            foreach ($recapcourrier as $element) {{
                $nbr = isset($element->nombres) ? (int) round((float) $element->nombres) : 0;
                $pu = isset($element->prixcolis) ? (float) $element->prixcolis : 0.0;
                $mt = $nbr * $pu;
                $lignes[] = array(
                    'ligne' => isset($element->nom_ligne) ? (string) $element->nom_ligne : '',
                    'nbr' => $nbr,
                    'pu' => $pu,
                    'montant' => $mt,
                );
                $total += $mt;
            }}
            $qs = http_build_query(array_filter(array(
                'datedebutcr' => $dt1,
                'datefincr' => $dt2,
                'axelignecr' => $lign,
                '_compagcr' => $comp,
                'departgarcr' => $gid,
                'typcours' => $tyc,
                {QS_CONNECT}
            )));
            return array(
                'titre' => 'RECAP EX MENSUEL  ' . $cieNom . ' ' . $ty3 . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                {RETOUR}
                {COLS}
                'export_base' => site_url('Rapport/exocourrier_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }}

        public function exocourrier($ckey, $g)
        {{
            return $this->_etat_render_view('Récap ex mensuel courrier', $this->_exocourrier_payload($ckey, $g));
        }}

        public function exocourrier_export($ckey, $g)
        {{
            $this->_etat_export_dispatch($this->_exocourrier_payload($ckey, $g));
        }}
'''

code_exocourrieresc = f'''
        protected function _exocourrieresc_payload($ckey, $g)
        {{
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebutcresc'));
            $dt2 = trim((string) $this->input->get_post('datefincresc'));
            $lign = trim((string) $this->input->get_post('axelignecresc'));
            $comp = trim((string) $this->input->get_post('_compagcresc'));
            $tyc = trim((string) $this->input->get_post('typcoursesc'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgarcresc'));
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
            $recapcourrier = $this->m_courrier_expedieresc->recaptexopli($this->entreprise->ekey, $dt1, $dt2, $gid, $comp, $tyc, $lign);
            if (!is_array($recapcourrier)) {{
                $recapcourrier = array();
            }}
            $lignes = array();
            $total = 0.0;
            foreach ($recapcourrier as $element) {{
                $nbr = isset($element->nombresesc) ? (int) round((float) $element->nombresesc) : 0;
                $pu = isset($element->prixcolisesc) ? (float) $element->prixcolisesc : 0.0;
                $mt = $nbr * $pu;
                $lignes[] = array(
                    'ligne' => isset($element->nom_ligne) ? (string) $element->nom_ligne : '',
                    'nbr' => $nbr,
                    'pu' => $pu,
                    'montant' => $mt,
                );
                $total += $mt;
            }}
            $qs = http_build_query(array_filter(array(
                'datedebutcresc' => $dt1,
                'datefincresc' => $dt2,
                'axelignecresc' => $lign,
                '_compagcresc' => $comp,
                'departgarcresc' => $gid,
                'typcoursesc' => $tyc,
                {QS_CONNECT}
            )));
            return array(
                'titre' => 'RECAP EX MENSUEL ESCAL ' . $cieNom . ' ' . $ty3 . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                {RETOUR}
                {COLS}
                'export_base' => site_url('Rapport/exocourrieresc_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }}

        public function exocourrieresc($ckey, $g)
        {{
            return $this->_etat_render_view('Récap ex mensuel courrier escal', $this->_exocourrieresc_payload($ckey, $g));
        }}

        public function exocourrieresc_export($ckey, $g)
        {{
            $this->_etat_export_dispatch($this->_exocourrieresc_payload($ckey, $g));
        }}
'''

replacements = [
    ('exercices', code_exercices),
    ('exerciceses', code_exerciceses),
    ('exercicesbag', code_exercicesbag),
    ('exercicesbagesc', code_exercicesbagesc),
    ('exocourrier', code_exocourrier),
    ('exocourrieresc', code_exocourrieresc),
]

for name, code in replacements:
    src = replace_method(src, name, code)
    print('OK', name)

path.write_text(src, encoding='utf-8')
print('Wrote', path)
