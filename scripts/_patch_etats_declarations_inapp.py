#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""Lot E: déclarations (clarer + états déclarés) → in-app."""
from pathlib import Path
import re

path = Path(__file__).resolve().parents[1] / 'application/controllers/Rapport.php'
src = path.read_text(encoding='utf-8', errors='replace')


def replace_method(src, method_name, new_code):
    pat = re.compile(r'(?m)^        public function ' + re.escape(method_name) + r'\(')
    m = pat.search(src)
    if not m:
        raise SystemExit('Missing method: ' + method_name)
    # Find opening brace of method body
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
                # Skip trailing newlines only (preserve indentation of next method)
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
                    array('key' => 'ligne', 'label' => 'Ligne', 'align' => 'left'),
                    array('key' => 'nbr', 'label' => 'Nombre', 'align' => 'center'),
                    array('key' => 'pu', 'label' => 'Prix unitaire', 'align' => 'right', 'money' => true),
                    array('key' => 'montant', 'label' => 'Prix total', 'align' => 'right', 'money' => true),
                ),"""

COLS_STATUT = """'columns' => array(
                    array('key' => 'statut', 'label' => 'Statut', 'align' => 'left'),
                    array('key' => 'nbr', 'label' => 'Éléments traités', 'align' => 'center'),
                ),"""


def gare_label():
    return """$ngrd = $this->m_gare_depart->getno($gid);
            $garNom = ($ngrd && isset($ngrd->garenom)) ? $ngrd->garenom : $gid;"""


def ty3_block(tyc_var='tyc'):
    return f"""if (${tyc_var} === 'Gros_plis') {{
                $ty3 = 'COLIS';
            }} elseif (${tyc_var} === 'Petit_plis') {{
                $ty3 = 'PLIS';
            }} elseif (${tyc_var} === '') {{
                $ty3 = 'PLIS/COLIS';
            }} else {{
                $ty3 = ${tyc_var};
            }}"""


# ---- CLARER (mutations) ----

code_exerclarer = f'''
        protected function _exerclarer_payload($ckey, $g, $doUpdate = true)
        {{
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebutdc'));
            $dt2 = trim((string) $this->input->get_post('datefindc'));
            $lign = trim((string) $this->input->get_post('axelignedc'));
            $comp = trim((string) $this->input->get_post('_compagdc'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgardc'));
            if ($gid === '') {{
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }}
            $this->_assert_recap_global_filters($this->entreprise->ekey, $dt1, $dt2, $comp);
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            {gare_label()}
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            $saved = trim((string) $this->input->get_post('decl_statut'));
            $savedNbr = (int) $this->input->get_post('decl_nbr');
            $nbr = 0;
            $ok = false;
            if ($doUpdate && $saved === '') {{
                if ($comp == 5002) {{
                    $reportick = $this->m_passager->reporticketgr($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
                    $reportickretors = $this->m_non_passager->reporticketretourgr($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
                }} else {{
                    $reportick = $this->m_passager->reporticketcptgr($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
                    $reportickretors = $this->m_non_passager->reporticketretourcptgr($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
                }}
                if (!is_array($reportick)) {{
                    $reportick = array();
                }}
                if (!is_array($reportickretors)) {{
                    $reportickretors = array();
                }}
                $pa = false;
                foreach ($reportick as $lement) {{
                    $exopassager = array('exop' => 1);
                    $pa = $this->m_passager->update($lement->code_passager, $lement->code_ticket, $exopassager);
                    $nbr++;
                }}
                foreach ($reportickretors as $rlement) {{
                    $exonpassager = array('exonp' => 1);
                    $this->m_non_passager->update($rlement->code_non_pass, $rlement->codeticket, $exonpassager);
                    $nbr++;
                }}
                $ok = ($pa !== false && $nbr > 0);
            }} else {{
                $ok = ($saved === 'REUSSIE');
                $nbr = $savedNbr;
            }}
            $re = $ok ? 'REUSSIE' : 'NON REUSSIE';
            $qs = http_build_query(array_filter(array(
                'datedebutdc' => $dt1,
                'datefindc' => $dt2,
                'axelignedc' => $lign,
                '_compagdc' => $comp,
                'departgardc' => $gid,
                'decl_statut' => $re,
                'decl_nbr' => (string) $nbr,
                {QS_CONNECT}
            )));
            return array(
                'titre' => 'DECLARATION DES TICKETS ' . $cieNom . ' ' . $garNom . ' DU ' . $days . ' AU ' . $days1 . ' ' . $re,
                'lignes' => array(array('statut' => $re, 'nbr' => $nbr)),
                'total' => 0.0,
                'filters_qs' => $qs,
                {RETOUR}
                {COLS_STATUT}
                'export_base' => site_url('Rapport/exerclarer_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }}

        public function exerclarer($ckey, $g)
        {{
            return $this->_etat_render_view('Déclaration tickets', $this->_exerclarer_payload($ckey, $g, true));
        }}

        public function exerclarer_export($ckey, $g)
        {{
            $this->_etat_export_dispatch($this->_exerclarer_payload($ckey, $g, false));
        }}
'''

code_exerclareres = f'''
        protected function _exerclareres_payload($ckey, $g, $doUpdate = true)
        {{
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebutdces'));
            $dt2 = trim((string) $this->input->get_post('datefindces'));
            $lign = trim((string) $this->input->get_post('axelignedces'));
            $comp = trim((string) $this->input->get_post('_compagdces'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgardces'));
            if ($gid === '') {{
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }}
            $this->_assert_recap_global_filters($this->entreprise->ekey, $dt1, $dt2, $comp);
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            {gare_label()}
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            $saved = trim((string) $this->input->get_post('decl_statut'));
            $savedNbr = (int) $this->input->get_post('decl_nbr');
            $nbr = 0;
            $ok = false;
            if ($doUpdate && $saved === '') {{
                $reportick = $this->m_escalclients->reporticketcptgr($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
                if (!is_array($reportick)) {{
                    $reportick = array();
                }}
                $paes = false;
                foreach ($reportick as $lement) {{
                    $exopassageres = array('exopes' => 1);
                    $paes = $this->m_escalclients->update($lement->idclescal, $exopassageres);
                    $nbr++;
                }}
                $ok = ($paes !== false && $nbr > 0);
            }} else {{
                $ok = ($saved === 'REUSSIE');
                $nbr = $savedNbr;
            }}
            $re = $ok ? 'REUSSIE' : 'NON REUSSIE';
            $qs = http_build_query(array_filter(array(
                'datedebutdces' => $dt1,
                'datefindces' => $dt2,
                'axelignedces' => $lign,
                '_compagdces' => $comp,
                'departgardces' => $gid,
                'decl_statut' => $re,
                'decl_nbr' => (string) $nbr,
                {QS_CONNECT}
            )));
            return array(
                'titre' => 'DECLARATION DES TICKETS ESCAL ' . $cieNom . ' ' . $garNom . ' DU ' . $days . ' AU ' . $days1 . ' ' . $re,
                'lignes' => array(array('statut' => $re, 'nbr' => $nbr)),
                'total' => 0.0,
                'filters_qs' => $qs,
                {RETOUR}
                {COLS_STATUT}
                'export_base' => site_url('Rapport/exerclareres_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }}

        public function exerclareres($ckey, $g)
        {{
            return $this->_etat_render_view('Déclaration tickets escal', $this->_exerclareres_payload($ckey, $g, true));
        }}

        public function exerclareres_export($ckey, $g)
        {{
            $this->_etat_export_dispatch($this->_exerclareres_payload($ckey, $g, false));
        }}
'''

code_exerclarerbg = f'''
        protected function _exerclarerbg_payload($ckey, $g, $doUpdate = true)
        {{
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebutdcbg'));
            $dt2 = trim((string) $this->input->get_post('datefindcbg'));
            $lign = trim((string) $this->input->get_post('axelignedcbg'));
            $comp = trim((string) $this->input->get_post('_compagdcbg'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgardcbg'));
            if ($gid === '') {{
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }}
            $this->_assert_recap_global_filters($this->entreprise->ekey, $dt1, $dt2, $comp);
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            {gare_label()}
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            $saved = trim((string) $this->input->get_post('decl_statut'));
            $savedNbr = (int) $this->input->get_post('decl_nbr');
            $nbr = 0;
            $ok = false;
            if ($doUpdate && $saved === '') {{
                $reportbag = $this->m_bagage->reportbagcptgr($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
                if (!is_array($reportbag)) {{
                    $reportbag = array();
                }}
                $pab = false;
                foreach ($reportbag as $lement) {{
                    $exobagas = array('exobg' => 1);
                    $pab = $this->m_bagage->update($lement->id_bagage, $exobagas);
                    $nbr++;
                }}
                $ok = ($pab !== false && $nbr > 0);
            }} else {{
                $ok = ($saved === 'REUSSIE');
                $nbr = $savedNbr;
            }}
            $re = $ok ? 'REUSSIE' : 'NON REUSSIE';
            $qs = http_build_query(array_filter(array(
                'datedebutdcbg' => $dt1,
                'datefindcbg' => $dt2,
                'axelignedcbg' => $lign,
                '_compagdcbg' => $comp,
                'departgardcbg' => $gid,
                'decl_statut' => $re,
                'decl_nbr' => (string) $nbr,
                {QS_CONNECT}
            )));
            return array(
                'titre' => 'DECLARATION DES BAGAGES ' . $cieNom . ' ' . $garNom . ' DU ' . $days . ' AU ' . $days1 . ' ' . $re,
                'lignes' => array(array('statut' => $re, 'nbr' => $nbr)),
                'total' => 0.0,
                'filters_qs' => $qs,
                {RETOUR}
                {COLS_STATUT}
                'export_base' => site_url('Rapport/exerclarerbg_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }}

        public function exerclarerbg($ckey, $g)
        {{
            return $this->_etat_render_view('Déclaration bagages', $this->_exerclarerbg_payload($ckey, $g, true));
        }}

        public function exerclarerbg_export($ckey, $g)
        {{
            $this->_etat_export_dispatch($this->_exerclarerbg_payload($ckey, $g, false));
        }}
'''

code_exerclarerbgesc = f'''
        protected function _exerclarerbgesc_payload($ckey, $g, $doUpdate = true)
        {{
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebutdcbgesc'));
            $dt2 = trim((string) $this->input->get_post('datefindcbgesc'));
            $lign = trim((string) $this->input->get_post('axelignedcbgesc'));
            $comp = trim((string) $this->input->get_post('_compagdcbgesc'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgardcbgesc'));
            if ($gid === '') {{
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }}
            $this->_assert_recap_global_filters($this->entreprise->ekey, $dt1, $dt2, $comp);
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            {gare_label()}
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            $saved = trim((string) $this->input->get_post('decl_statut'));
            $savedNbr = (int) $this->input->get_post('decl_nbr');
            $nbr = 0;
            $ok = false;
            if ($doUpdate && $saved === '') {{
                $reportbag = $this->m_bagageesc->reportbagcptgr($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $lign);
                if (!is_array($reportbag)) {{
                    $reportbag = array();
                }}
                $pab = false;
                foreach ($reportbag as $lement) {{
                    $exobagas = array('exobagesc' => 1);
                    $pab = $this->m_bagageesc->update($lement->id_bagageesc, $exobagas);
                    $nbr++;
                }}
                $ok = ($pab !== false && $nbr > 0);
            }} else {{
                $ok = ($saved === 'REUSSIE');
                $nbr = $savedNbr;
            }}
            $re = $ok ? 'REUSSIE' : 'NON REUSSIE';
            $qs = http_build_query(array_filter(array(
                'datedebutdcbgesc' => $dt1,
                'datefindcbgesc' => $dt2,
                'axelignedcbgesc' => $lign,
                '_compagdcbgesc' => $comp,
                'departgardcbgesc' => $gid,
                'decl_statut' => $re,
                'decl_nbr' => (string) $nbr,
                {QS_CONNECT}
            )));
            return array(
                'titre' => 'DECLARATION DES BAGAGES ESCAL ' . $cieNom . ' ' . $garNom . ' DU ' . $days . ' AU ' . $days1 . ' ' . $re,
                'lignes' => array(array('statut' => $re, 'nbr' => $nbr)),
                'total' => 0.0,
                'filters_qs' => $qs,
                {RETOUR}
                {COLS_STATUT}
                'export_base' => site_url('Rapport/exerclarerbgesc_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }}

        public function exerclarerbgesc($ckey, $g)
        {{
            return $this->_etat_render_view('Déclaration bagages escal', $this->_exerclarerbgesc_payload($ckey, $g, true));
        }}

        public function exerclarerbgesc_export($ckey, $g)
        {{
            $this->_etat_export_dispatch($this->_exerclarerbgesc_payload($ckey, $g, false));
        }}
'''

code_exoclarercourrier = f'''
        protected function _exoclarercourrier_payload($ckey, $g, $doUpdate = true)
        {{
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebutcrcl'));
            $dt2 = trim((string) $this->input->get_post('datefincrcl'));
            $lign = trim((string) $this->input->get_post('axelignecrcl'));
            $comp = trim((string) $this->input->get_post('_compagcrcl'));
            $tyc = trim((string) $this->input->get_post('typcourscl'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgarcrcl'));
            if ($gid === '') {{
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }}
            $this->_assert_recap_global_filters($this->entreprise->ekey, $dt1, $dt2, $comp);
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            {gare_label()}
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            {ty3_block('tyc')}
            $saved = trim((string) $this->input->get_post('decl_statut'));
            $savedNbr = (int) $this->input->get_post('decl_nbr');
            $nbr = 0;
            $ok = false;
            if ($doUpdate && $saved === '') {{
                $recapcourrier = $this->m_courrier_expedier->recaptexopligr($this->entreprise->ekey, $dt1, $dt2, $gid, $comp, $tyc, $lign);
                if (!is_array($recapcourrier)) {{
                    $recapcourrier = array();
                }}
                $pacr = false;
                foreach ($recapcourrier as $lement) {{
                    $exocours = array('exocr' => 1);
                    $pacr = $this->m_courrier_expedier->update($lement->courrierexpid, $lement->num_cour, $lement->departcolis, $exocours);
                    $nbr++;
                }}
                $ok = ($pacr !== false && $nbr > 0);
            }} else {{
                $ok = ($saved === 'REUSSIE');
                $nbr = $savedNbr;
            }}
            $re = $ok ? 'REUSSIE' : 'NON REUSSIE';
            $qs = http_build_query(array_filter(array(
                'datedebutcrcl' => $dt1,
                'datefincrcl' => $dt2,
                'axelignecrcl' => $lign,
                '_compagcrcl' => $comp,
                'departgarcrcl' => $gid,
                'typcourscl' => $tyc,
                'decl_statut' => $re,
                'decl_nbr' => (string) $nbr,
                {QS_CONNECT}
            )));
            return array(
                'titre' => 'DECLARATION  ' . $cieNom . ' ' . $garNom . ' ' . $ty3 . ' DU ' . $days . ' AU ' . $days1 . ' ' . $re,
                'lignes' => array(array('statut' => $re, 'nbr' => $nbr)),
                'total' => 0.0,
                'filters_qs' => $qs,
                {RETOUR}
                {COLS_STATUT}
                'export_base' => site_url('Rapport/exoclarercourrier_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }}

        public function exoclarercourrier($ckey, $g)
        {{
            return $this->_etat_render_view('Déclaration courrier', $this->_exoclarercourrier_payload($ckey, $g, true));
        }}

        public function exoclarercourrier_export($ckey, $g)
        {{
            $this->_etat_export_dispatch($this->_exoclarercourrier_payload($ckey, $g, false));
        }}
'''

code_exoclarercourrieresc = f'''
        protected function _exoclarercourrieresc_payload($ckey, $g, $doUpdate = true)
        {{
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebutcrclesc'));
            $dt2 = trim((string) $this->input->get_post('datefincrclesc'));
            $lign = trim((string) $this->input->get_post('axelignecrclesc'));
            $comp = trim((string) $this->input->get_post('_compagcrclesc'));
            $tyc = trim((string) $this->input->get_post('typcoursclesc'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgarcrclesc'));
            if ($gid === '') {{
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }}
            $this->_assert_recap_global_filters($this->entreprise->ekey, $dt1, $dt2, $comp);
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            {gare_label()}
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            {ty3_block('tyc')}
            $saved = trim((string) $this->input->get_post('decl_statut'));
            $savedNbr = (int) $this->input->get_post('decl_nbr');
            $nbr = 0;
            $ok = false;
            if ($doUpdate && $saved === '') {{
                $recapcourrier = $this->m_courrier_expedieresc->recaptexopligr($this->entreprise->ekey, $dt1, $dt2, $gid, $comp, $tyc, $lign);
                if (!is_array($recapcourrier)) {{
                    $recapcourrier = array();
                }}
                $pacr = false;
                foreach ($recapcourrier as $lement) {{
                    $exocours = array('exocresc' => 1);
                    $pacr = $this->m_courrier_expedieresc->update($lement->courrierexpidesc, $lement->num_couresc, $lement->departcolisesc, $exocours);
                    $nbr++;
                }}
                $ok = ($pacr !== false && $nbr > 0);
            }} else {{
                $ok = ($saved === 'REUSSIE');
                $nbr = $savedNbr;
            }}
            $re = $ok ? 'REUSSIE' : 'NON REUSSIE';
            $qs = http_build_query(array_filter(array(
                'datedebutcrclesc' => $dt1,
                'datefincrclesc' => $dt2,
                'axelignecrclesc' => $lign,
                '_compagcrclesc' => $comp,
                'departgarcrclesc' => $gid,
                'typcoursclesc' => $tyc,
                'decl_statut' => $re,
                'decl_nbr' => (string) $nbr,
                {QS_CONNECT}
            )));
            return array(
                'titre' => 'DECLARATION ESCAL ' . $cieNom . ' ' . $garNom . ' ' . $ty3 . ' DU ' . $days . ' AU ' . $days1 . ' ' . $re,
                'lignes' => array(array('statut' => $re, 'nbr' => $nbr)),
                'total' => 0.0,
                'filters_qs' => $qs,
                {RETOUR}
                {COLS_STATUT}
                'export_base' => site_url('Rapport/exoclarercourrieresc_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }}

        public function exoclarercourrieresc($ckey, $g)
        {{
            return $this->_etat_render_view('Déclaration courrier escal', $this->_exoclarercourrieresc_payload($ckey, $g, true));
        }}

        public function exoclarercourrieresc_export($ckey, $g)
        {{
            $this->_etat_export_dispatch($this->_exoclarercourrieresc_payload($ckey, $g, false));
        }}
'''

# ---- ETATS DECLARATION (read-only) ----

code_exerdeclarer = f'''
        protected function _exerdeclarer_payload($ckey, $g)
        {{
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebutd'));
            $dt2 = trim((string) $this->input->get_post('datefind'));
            $lign = trim((string) $this->input->get_post('axeligned'));
            $comp = trim((string) $this->input->get_post('_compagd'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgard'));
            if ($gid === '') {{
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }}
            $this->_assert_recap_global_filters($this->entreprise->ekey, $dt1, $dt2, $comp);
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            {gare_label()}
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            $reportick = $this->m_passager->reporticketcptd($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
            $reportickretors = $this->m_non_passager->reporticketretourcptd($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
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
                'datedebutd' => $dt1,
                'datefind' => $dt2,
                'axeligned' => $lign,
                '_compagd' => $comp,
                'departgard' => $gid,
                {QS_CONNECT}
            )));
            return array(
                'titre' => 'ETATS DES DECLARATION DES TICKETS ' . $cieNom . ' ' . $garNom . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                {RETOUR}
                {COLS}
                'export_base' => site_url('Rapport/exerdeclarer_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }}

        public function exerdeclarer($ckey, $g)
        {{
            return $this->_etat_render_view('États déclaration tickets', $this->_exerdeclarer_payload($ckey, $g));
        }}

        public function exerdeclarer_export($ckey, $g)
        {{
            $this->_etat_export_dispatch($this->_exerdeclarer_payload($ckey, $g));
        }}
'''

code_exerdeclareres = f'''
        protected function _exerdeclareres_payload($ckey, $g)
        {{
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebutdes'));
            $dt2 = trim((string) $this->input->get_post('datefindes'));
            $lign = trim((string) $this->input->get_post('axelignedes'));
            $comp = trim((string) $this->input->get_post('_compagdes'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgardes'));
            if ($gid === '') {{
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }}
            $this->_assert_recap_global_filters($this->entreprise->ekey, $dt1, $dt2, $comp);
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            {gare_label()}
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            $reportick = $this->m_escalclients->reporticketcptd($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $lign);
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
                'datedebutdes' => $dt1,
                'datefindes' => $dt2,
                'axelignedes' => $lign,
                '_compagdes' => $comp,
                'departgardes' => $gid,
                {QS_CONNECT}
            )));
            return array(
                'titre' => 'ETATS DES DECLARATION DES TICKETS ESCAL ' . $cieNom . ' ' . $garNom . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                {RETOUR}
                {COLS}
                'export_base' => site_url('Rapport/exerdeclareres_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }}

        public function exerdeclareres($ckey, $g)
        {{
            return $this->_etat_render_view('États déclaration tickets escal', $this->_exerdeclareres_payload($ckey, $g));
        }}

        public function exerdeclareres_export($ckey, $g)
        {{
            $this->_etat_export_dispatch($this->_exerdeclareres_payload($ckey, $g));
        }}
'''

code_exerdeclarerbg = f'''
        protected function _exerdeclarerbg_payload($ckey, $g)
        {{
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebutdbg'));
            $dt2 = trim((string) $this->input->get_post('datefindbg'));
            $lign = trim((string) $this->input->get_post('axelignedbg'));
            $comp = trim((string) $this->input->get_post('_compagdbg'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgardbg'));
            if ($gid === '') {{
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }}
            $this->_assert_recap_global_filters($this->entreprise->ekey, $dt1, $dt2, $comp);
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            {gare_label()}
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            $reportbaga = $this->m_bagage->reportbagcptd($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $lign);
            if (!is_array($reportbaga)) {{
                $reportbaga = array();
            }}
            $lignes = array();
            $total = 0.0;
            foreach ($reportbaga as $lement) {{
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
                'datedebutdbg' => $dt1,
                'datefindbg' => $dt2,
                'axelignedbg' => $lign,
                '_compagdbg' => $comp,
                'departgardbg' => $gid,
                {QS_CONNECT}
            )));
            return array(
                'titre' => 'ETATS DES DECLARATION BAGAGES ' . $cieNom . ' ' . $garNom . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                {RETOUR}
                {COLS}
                'export_base' => site_url('Rapport/exerdeclarerbg_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }}

        public function exerdeclarerbg($ckey, $g)
        {{
            return $this->_etat_render_view('États déclaration bagages', $this->_exerdeclarerbg_payload($ckey, $g));
        }}

        public function exerdeclarerbg_export($ckey, $g)
        {{
            $this->_etat_export_dispatch($this->_exerdeclarerbg_payload($ckey, $g));
        }}
'''

code_exerdeclarerbgesc = f'''
        protected function _exerdeclarerbgesc_payload($ckey, $g)
        {{
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebutdbgesc'));
            $dt2 = trim((string) $this->input->get_post('datefindbgesc'));
            $lign = trim((string) $this->input->get_post('axelignedbgesc'));
            $comp = trim((string) $this->input->get_post('_compagdbgesc'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgardbgesc'));
            if ($gid === '') {{
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }}
            $this->_assert_recap_global_filters($this->entreprise->ekey, $dt1, $dt2, $comp);
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            {gare_label()}
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            $reportbaga = $this->m_bagageesc->reportbagcptd($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $lign);
            if (!is_array($reportbaga)) {{
                $reportbaga = array();
            }}
            $lignes = array();
            $total = 0.0;
            foreach ($reportbaga as $lement) {{
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
                'datedebutdbgesc' => $dt1,
                'datefindbgesc' => $dt2,
                'axelignedbgesc' => $lign,
                '_compagdbgesc' => $comp,
                'departgardbgesc' => $gid,
                {QS_CONNECT}
            )));
            return array(
                'titre' => 'ETATS DES DECLARATION BAGAGES ESCAL ' . $cieNom . ' ' . $garNom . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                {RETOUR}
                {COLS}
                'export_base' => site_url('Rapport/exerdeclarerbgesc_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }}

        public function exerdeclarerbgesc($ckey, $g)
        {{
            return $this->_etat_render_view('États déclaration bagages escal', $this->_exerdeclarerbgesc_payload($ckey, $g));
        }}

        public function exerdeclarerbgesc_export($ckey, $g)
        {{
            $this->_etat_export_dispatch($this->_exerdeclarerbgesc_payload($ckey, $g));
        }}
'''

code_exodeclarercourrier = f'''
        protected function _exodeclarercourrier_payload($ckey, $g)
        {{
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebutcrcld'));
            $dt2 = trim((string) $this->input->get_post('datefincrcld'));
            $lign = trim((string) $this->input->get_post('axelignecrcld'));
            $comp = trim((string) $this->input->get_post('_compagcrcld'));
            $tyc = trim((string) $this->input->get_post('typcourscld'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgarcrcld'));
            if ($gid === '') {{
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }}
            $this->_assert_recap_global_filters($this->entreprise->ekey, $dt1, $dt2, $comp);
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            {gare_label()}
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            {ty3_block('tyc')}
            $recapcourrier = $this->m_courrier_expedier->recaptexoplid($this->entreprise->ekey, $dt1, $dt2, $gid, $comp, $tyc, $lign);
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
                'datedebutcrcld' => $dt1,
                'datefincrcld' => $dt2,
                'axelignecrcld' => $lign,
                '_compagcrcld' => $comp,
                'departgarcrcld' => $gid,
                'typcourscld' => $tyc,
                {QS_CONNECT}
            )));
            return array(
                'titre' => 'ETATS DES DECLARATION  ' . $cieNom . ' ' . $garNom . ' ' . $ty3 . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                {RETOUR}
                {COLS}
                'export_base' => site_url('Rapport/exodeclarercourrier_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }}

        public function exodeclarercourrier($ckey, $g)
        {{
            return $this->_etat_render_view('États déclaration courrier', $this->_exodeclarercourrier_payload($ckey, $g));
        }}

        public function exodeclarercourrier_export($ckey, $g)
        {{
            $this->_etat_export_dispatch($this->_exodeclarercourrier_payload($ckey, $g));
        }}
'''

code_exodeclarercourrieresc = f'''
        protected function _exodeclarercourrieresc_payload($ckey, $g)
        {{
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebutcrcldesc'));
            $dt2 = trim((string) $this->input->get_post('datefincrcldesc'));
            $lign = trim((string) $this->input->get_post('axelignecrcldesc'));
            $comp = trim((string) $this->input->get_post('_compagcrcldesc'));
            $tyc = trim((string) $this->input->get_post('typcourscldesc'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgarcrcldesc'));
            if ($gid === '') {{
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }}
            $this->_assert_recap_global_filters($this->entreprise->ekey, $dt1, $dt2, $comp);
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            {gare_label()}
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            {ty3_block('tyc')}
            $recapcourrier = $this->m_courrier_expedieresc->recaptexoplid($this->entreprise->ekey, $dt1, $dt2, $gid, $comp, $tyc, $lign);
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
                'datedebutcrcldesc' => $dt1,
                'datefincrcldesc' => $dt2,
                'axelignecrcldesc' => $lign,
                '_compagcrcldesc' => $comp,
                'departgarcrcldesc' => $gid,
                'typcourscldesc' => $tyc,
                {QS_CONNECT}
            )));
            return array(
                'titre' => 'ETATS DES DECLARATION ESCAL ' . $cieNom . ' ' . $garNom . ' ' . $ty3 . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                {RETOUR}
                {COLS}
                'export_base' => site_url('Rapport/exodeclarercourrieresc_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }}

        public function exodeclarercourrieresc($ckey, $g)
        {{
            return $this->_etat_render_view('États déclaration courrier escal', $this->_exodeclarercourrieresc_payload($ckey, $g));
        }}

        public function exodeclarercourrieresc_export($ckey, $g)
        {{
            $this->_etat_export_dispatch($this->_exodeclarercourrieresc_payload($ckey, $g));
        }}
'''

replacements = [
    ('exerclarer', code_exerclarer),
    ('exerdeclarer', code_exerdeclarer),
    ('exerclarerbg', code_exerclarerbg),
    ('exerclarerbgesc', code_exerclarerbgesc),
    ('exerdeclarerbg', code_exerdeclarerbg),
    ('exerdeclarerbgesc', code_exerdeclarerbgesc),
    ('exoclarercourrier', code_exoclarercourrier),
    ('exoclarercourrieresc', code_exoclarercourrieresc),
    ('exodeclarercourrier', code_exodeclarercourrier),
    ('exodeclarercourrieresc', code_exodeclarercourrieresc),
    ('exerclareres', code_exerclareres),
    ('exerdeclareres', code_exerdeclareres),
]

for name, code in replacements:
    src = replace_method(src, name, code)
    print('OK', name)

path.write_text(src, encoding='utf-8')
print('Wrote', path)
