#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""One-shot: convert bagage/courrier/escale Rapport methods to in-app payloads."""
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
    return src[:start] + new_code.rstrip() + '\n\n' + src[end:] if False else src[:m.start()] + new_code.rstrip() + '\n\n' + src[end:]


def connect_qs():
    return """'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),"""


def retour_block():
    return """'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),"""


def cols_ligne_nbr_pu_mt():
    return """'columns' => array(
                    array('key' => 'ligne', 'label' => 'Ligne', 'align' => 'left'),
                    array('key' => 'nbr', 'label' => 'Nombre', 'align' => 'center'),
                    array('key' => 'pu', 'label' => 'Prix unitaire', 'align' => 'right', 'money' => true),
                    array('key' => 'montant', 'label' => 'Prix total', 'align' => 'right', 'money' => true),
                ),"""


def cols_date_mt(type_col=False):
    if type_col:
        return """'columns' => array(
                    array('key' => 'date', 'label' => 'Date', 'align' => 'left'),
                    array('key' => 'type', 'label' => 'Type', 'align' => 'left'),
                    array('key' => 'montant', 'label' => 'Montant', 'align' => 'right', 'money' => true),
                ),"""
    return """'columns' => array(
                    array('key' => 'date', 'label' => 'Date', 'align' => 'left'),
                    array('key' => 'montant', 'label' => 'Montant', 'align' => 'right', 'money' => true),
                ),"""


# --- reportsesc ---
code_reportsesc = f'''
        protected function _reportsesc_payload($ckey, $g)
        {{
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebutesc'));
            $dt2 = trim((string) $this->input->get_post('datefinesc'));
            $cais = trim((string) $this->input->get_post('caissieresc'));
            $lign = trim((string) $this->input->get_post('axeligneesc'));
            $comp = trim((string) $this->input->get_post('_compagesc'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgaresc'));
            if ($gid === '') {{
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }}
            $op = $this->_resolve_report_operateur($cais);
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            $onreport = $this->m_escalclients->listereportesc($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $cais, $lign);
            if (!is_array($onreport)) {{
                $onreport = array();
            }}
            $lignes = array();
            $total = 0.0;
            foreach ($onreport as $element) {{
                $nbr = isset($element->escalp) ? (int) round((float) $element->escalp) : 0;
                $mt = $this->_recap_line_amount(
                    isset($element->tota) ? $element->tota : null,
                    $nbr,
                    isset($element->prixescal) ? $element->prixescal : 0
                );
                $lignes[] = array(
                    'ligne' => isset($element->nom_ligne) ? (string) $element->nom_ligne : '',
                    'nbr' => $nbr,
                    'pu' => isset($element->prixescal) ? (float) $element->prixescal : 0,
                    'montant' => $mt,
                );
                $total += $mt;
            }}
            $qs = http_build_query(array_filter(array(
                'datedebutesc' => $dt1,
                'datefinesc' => $dt2,
                'caissieresc' => $cais,
                'axeligneesc' => $lign,
                '_compagesc' => $comp,
                'departgaresc' => $gid,
                {connect_qs()}
            )));
            return array(
                'titre' => 'ETAT GLOBAL TICKET GUICHETIER ESCAL ' . $op['label'] . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                {retour_block()}
                {cols_ligne_nbr_pu_mt()}
                'export_base' => site_url('Rapport/reportsesc_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }}

        public function reportsesc($ckey, $g)
        {{
            return $this->_etat_render_view('État global ticket guichetier escal', $this->_reportsesc_payload($ckey, $g));
        }}

        public function reportsesc_export($ckey, $g)
        {{
            $this->_etat_export_dispatch($this->_reportsesc_payload($ckey, $g));
        }}
'''

code_reporticketesc = f'''
        protected function _reporticketesc_payload($ckey, $g)
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
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            $reportick = $this->m_escalclients->reporticketcptad($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
            if (!is_array($reportick)) {{
                $reportick = array();
            }}
            $lignes = array();
            $total = 0.0;
            foreach ($reportick as $lement) {{
                $nbr = isset($lement->escalp) ? (int) round((float) $lement->escalp) : 0;
                $mt = $this->_recap_line_amount(
                    isset($lement->tota) ? $lement->tota : null,
                    $nbr,
                    isset($lement->prixescal) ? $lement->prixescal : 0
                );
                $lignes[] = array(
                    'ligne' => isset($lement->nom_ligne) ? (string) $lement->nom_ligne : '',
                    'nbr' => $nbr,
                    'pu' => isset($lement->prixescal) ? (float) $lement->prixescal : 0,
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
                {connect_qs()}
            )));
            return array(
                'titre' => 'RECAP GLOBAL TICKET ESCAL DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                {retour_block()}
                {cols_ligne_nbr_pu_mt()}
                'export_base' => site_url('Rapport/reporticketesc_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }}

        public function reporticketesc($ckey, $g)
        {{
            return $this->_etat_render_view('Récap global ticket escal', $this->_reporticketesc_payload($ckey, $g));
        }}

        public function reporticketesc_export($ckey, $g)
        {{
            $this->_etat_export_dispatch($this->_reporticketesc_payload($ckey, $g));
        }}
'''

code_triencaissementsexoesc = f'''
        protected function _triencaissementsexoesc_payload($ckey, $g)
        {{
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $ivd = trim((string) $this->input->get_post('vendeuseidexoesc'));
            $ddbt = trim((string) $this->input->get_post('datedexoesc'));
            $dfin = trim((string) $this->input->get_post('datefexoesc'));
            $comp = trim((string) $this->input->get_post('_compagexoesc'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgarexoesc'));
            if ($gid === '') {{
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }}
            $op = $this->_resolve_report_operateur($ivd);
            $ncgd = $this->m_gare_depart->getn($gid);
            $gar = ($ncgd && isset($ncgd->nom_gaep)) ? $ncgd->nom_gaep : $gid;
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            list($days, $days1) = $this->_recap_title_dates($ddbt, $dfin);
            $onreport = $this->m_escalclients->listereportverscptglexo($this->entreprise->ekey, $comp, $gid, $ddbt, $dfin, $ivd);
            if (!is_array($onreport)) {{
                $onreport = array();
            }}
            $lignes = array();
            $total = 0.0;
            foreach ($onreport as $element) {{
                $mt = isset($element->tota) ? (float) $element->tota : 0.0;
                $d = isset($element->datedepescal) ? (string) $element->datedepescal : '';
                $parts = explode('-', $d);
                $daysar = (count($parts) === 3) ? ($parts[2] . '-' . $parts[1] . '-' . $parts[0]) : $d;
                $lignes[] = array('date' => $daysar, 'montant' => $mt);
                $total += $mt;
            }}
            $qs = http_build_query(array_filter(array(
                'vendeuseidexoesc' => $ivd,
                'datedexoesc' => $ddbt,
                'datefexoesc' => $dfin,
                '_compagexoesc' => $comp,
                'departgarexoesc' => $gid,
                {connect_qs()}
            )));
            return array(
                'titre' => 'BROUILLARD(EXERCICE)TICKET ESCAL ' . $op['label'] . ' ' . $cieNom . ' ' . $gar . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                {retour_block()}
                {cols_date_mt()}
                'export_base' => site_url('Rapport/triencaissementsexoesc_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }}

        public function triencaissementsexoesc($ckey, $g)
        {{
            return $this->_etat_render_view('Brouillard exercice ticket escal', $this->_triencaissementsexoesc_payload($ckey, $g));
        }}

        public function triencaissementsexoesc_export($ckey, $g)
        {{
            $this->_etat_export_dispatch($this->_triencaissementsexoesc_payload($ckey, $g));
        }}
'''

# Generic bagage recap (reportbag / reportbagesc)
def bag_recap(name, post_prefix, model_expr, count_f, price_f, total_f, title, label, export_name):
    # post fields: datedebut{{p}}, datefin{{p}}, axeligne{{p}}, _compag{{p}}, departgar{{p}}
    p = post_prefix
    return f'''
        protected function _{name}_payload($ckey, $g)
        {{
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebut{p}'));
            $dt2 = trim((string) $this->input->get_post('datefin{p}'));
            $lign = trim((string) $this->input->get_post('axeligne{p}'));
            $comp = trim((string) $this->input->get_post('_compag{p}'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgar{p}'));
            if ($gid === '') {{
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }}
            $this->_assert_recap_global_filters($this->entreprise->ekey, $dt1, $dt2, $comp);
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            $reportick = {model_expr};
            if (!is_array($reportick)) {{
                $reportick = array();
            }}
            $lignes = array();
            $total = 0.0;
            foreach ($reportick as $lement) {{
                $nbr = isset($lement->{count_f}) ? (int) round((float) $lement->{count_f}) : 0;
                $mt = $this->_recap_line_amount(
                    isset($lement->{total_f}) ? $lement->{total_f} : null,
                    $nbr,
                    isset($lement->{price_f}) ? $lement->{price_f} : 0
                );
                $lignes[] = array(
                    'ligne' => isset($lement->nom_ligne) ? (string) $lement->nom_ligne : '',
                    'nbr' => $nbr,
                    'pu' => isset($lement->{price_f}) ? (float) $lement->{price_f} : 0,
                    'montant' => $mt,
                );
                $total += $mt;
            }}
            $qs = http_build_query(array_filter(array(
                'datedebut{p}' => $dt1,
                'datefin{p}' => $dt2,
                'axeligne{p}' => $lign,
                '_compag{p}' => $comp,
                'departgar{p}' => $gid,
                {connect_qs()}
            )));
            return array(
                'titre' => '{title} DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                {retour_block()}
                {cols_ligne_nbr_pu_mt()}
                'export_base' => site_url('Rapport/{export_name}/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }}

        public function {name}($ckey, $g)
        {{
            return $this->_etat_render_view('{label}', $this->_{name}_payload($ckey, $g));
        }}

        public function {name}_export($ckey, $g)
        {{
            $this->_etat_export_dispatch($this->_{name}_payload($ckey, $g));
        }}
'''


def bag_gl(name, post_map, model_expr, count_f, price_f, title_prefix, label, export_name, multiply=False):
    # post_map: keys to field names
    dt1, dt2, lign, comp, gidf, vend = post_map
    mt_expr = f"(isset($lement->{count_f}) ? (float) $lement->{count_f} : 0) * (isset($lement->{price_f}) ? (float) $lement->{price_f} : 0)" if multiply else None
    if multiply:
        mt_block = f'''$mt = {mt_expr};
                $nbr = isset($lement->{count_f}) ? (int) round((float) $lement->{count_f}) : 0;'''
    else:
        mt_block = f'''$nbr = isset($lement->{count_f}) ? (int) round((float) $lement->{count_f}) : 0;
                $mt = $this->_recap_line_amount(
                    isset($lement->total) ? $lement->total : null,
                    $nbr,
                    isset($lement->{price_f}) ? $lement->{price_f} : 0
                );'''
    return f'''
        protected function _{name}_payload($ckey, $g)
        {{
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('{dt1}'));
            $dt2 = trim((string) $this->input->get_post('{dt2}'));
            $lign = trim((string) $this->input->get_post('{lign}'));
            $comp = trim((string) $this->input->get_post('{comp}'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('{gidf}'));
            if ($gid === '') {{
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }}
            $ivd = trim((string) $this->input->get_post('{vend}'));
            $op = $this->_resolve_report_operateur($ivd);
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            $reportick = {model_expr};
            if (!is_array($reportick)) {{
                $reportick = array();
            }}
            $lignes = array();
            $total = 0.0;
            foreach ($reportick as $lement) {{
                {mt_block}
                $lignes[] = array(
                    'ligne' => isset($lement->nom_ligne) ? (string) $lement->nom_ligne : '',
                    'nbr' => $nbr,
                    'pu' => isset($lement->{price_f}) ? (float) $lement->{price_f} : 0,
                    'montant' => $mt,
                );
                $total += $mt;
            }}
            $qs = http_build_query(array_filter(array(
                '{dt1}' => $dt1,
                '{dt2}' => $dt2,
                '{lign}' => $lign,
                '{comp}' => $comp,
                '{gidf}' => $gid,
                '{vend}' => $ivd,
                {connect_qs()}
            )));
            return array(
                'titre' => '{title_prefix} ' . $op['label'] . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                {retour_block()}
                {cols_ligne_nbr_pu_mt()}
                'export_base' => site_url('Rapport/{export_name}/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }}

        public function {name}($ckey, $g)
        {{
            return $this->_etat_render_view('{label}', $this->_{name}_payload($ckey, $g));
        }}

        public function {name}_export($ckey, $g)
        {{
            $this->_etat_export_dispatch($this->_{name}_payload($ckey, $g));
        }}
'''


def bag_brouillard(name, posts, model_expr, date_f, total_f, title_prefix, label, export_name):
    vend, ddbt, dfin, comp, gidf = posts
    return f'''
        protected function _{name}_payload($ckey, $g)
        {{
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $ivd = trim((string) $this->input->get_post('{vend}'));
            $ddbt = trim((string) $this->input->get_post('{ddbt}'));
            $dfin = trim((string) $this->input->get_post('{dfin}'));
            $comp = trim((string) $this->input->get_post('{comp}'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('{gidf}'));
            if ($gid === '') {{
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }}
            $op = $this->_resolve_report_operateur($ivd);
            $ncgd = $this->m_gare_depart->getn($gid);
            $gar = ($ncgd && isset($ncgd->nom_gaep)) ? $ncgd->nom_gaep : $gid;
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            list($days, $days1) = $this->_recap_title_dates($ddbt, $dfin);
            $onreport = {model_expr};
            if (!is_array($onreport)) {{
                $onreport = array();
            }}
            $lignes = array();
            $total = 0.0;
            foreach ($onreport as $element) {{
                $mt = isset($element->{total_f}) ? (float) $element->{total_f} : 0.0;
                $d = isset($element->{date_f}) ? (string) $element->{date_f} : '';
                $parts = explode('-', $d);
                $daysar = (count($parts) === 3) ? ($parts[2] . '-' . $parts[1] . '-' . $parts[0]) : $d;
                $lignes[] = array('date' => $daysar, 'montant' => $mt);
                $total += $mt;
            }}
            $qs = http_build_query(array_filter(array(
                '{vend}' => $ivd,
                '{ddbt}' => $ddbt,
                '{dfin}' => $dfin,
                '{comp}' => $comp,
                '{gidf}' => $gid,
                {connect_qs()}
            )));
            return array(
                'titre' => '{title_prefix} ' . $op['label'] . ' ' . $cieNom . ' ' . $gar . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                {retour_block()}
                {cols_date_mt()}
                'export_base' => site_url('Rapport/{export_name}/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }}

        public function {name}($ckey, $g)
        {{
            return $this->_etat_render_view('{label}', $this->_{name}_payload($ckey, $g));
        }}

        public function {name}_export($ckey, $g)
        {{
            $this->_etat_export_dispatch($this->_{name}_payload($ckey, $g));
        }}
'''

# Courrier helpers - need type label logic from existing code. Keep simple.
code_recaptglcourrier = f'''
        protected function _recaptglcourrier_payload($ckey, $g)
        {{
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebutcrgl'));
            $dt2 = trim((string) $this->input->get_post('datefincrgl'));
            $lign = trim((string) $this->input->get_post('axelignecrgl'));
            $comp = trim((string) $this->input->get_post('_compagcrgl'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgarcrgl'));
            if ($gid === '') {{
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }}
            $typ = trim((string) $this->input->get_post('typcoursgl'));
            $sg = $this->_normalize_recap_sousgare_filter($this->input->get_post('sousgarecrgl'));
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            $this->_assert_recap_global_filters($this->entreprise->ekey, $dt1, $dt2, $comp);
            $compLabel = $this->_recap_compagnie_label($comp);
            if ($typ === 'Gros_plis') {{
                $ty3 = 'COLIS';
            }} elseif ($typ === 'Petit_plis') {{
                $ty3 = 'PLIS ';
            }} else {{
                $ty3 = 'PLIS/COLIS';
            }}
            $rows = $this->m_courrier_expedier->trecaptpligl($this->entreprise->ekey, $dt1, $dt2, $comp, $gid, $typ, $lign, $sg);
            if (!is_array($rows)) {{
                $rows = array();
            }}
            $sgTitre = '';
            if ($sg !== null && $sg !== '') {{
                $sgrow = $this->db->get_where('sousgare', array('idsousgare' => $sg))->row();
                if ($sgrow && !empty($sgrow->nomsousgare)) {{
                    $sgTitre = ' ' . $sgrow->nomsousgare;
                }}
            }}
            $lignes = array();
            $total = 0.0;
            foreach ($rows as $r) {{
                $nbr = isset($r->nombres) ? (int) round((float) $r->nombres) : 0;
                $mt = $this->_recap_line_amount(
                    isset($r->montant) ? $r->montant : null,
                    $nbr,
                    isset($r->prixcolis) ? $r->prixcolis : 0
                );
                $lignes[] = array(
                    'ligne' => isset($r->nom_ligne) ? (string) $r->nom_ligne : '',
                    'nbr' => $nbr,
                    'pu' => isset($r->prixcolis) ? (float) $r->prixcolis : 0,
                    'montant' => $mt,
                );
                $total += $mt;
            }}
            $qs = http_build_query(array_filter(array(
                'datedebutcrgl' => $dt1,
                'datefincrgl' => $dt2,
                'axelignecrgl' => $lign,
                '_compagcrgl' => $comp,
                'departgarcrgl' => $gid,
                'typcoursgl' => $typ,
                'sousgarecrgl' => $sg,
                {connect_qs()}
            )));
            return array(
                'titre' => 'RECAP GLOBAL COURRIER ' . $ty3 . $compLabel . $sgTitre . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                {retour_block()}
                {cols_ligne_nbr_pu_mt()}
                'export_base' => site_url('Rapport/recaptglcourrier_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }}

        public function recaptglcourrier($ckey, $g)
        {{
            return $this->_etat_render_view('Récap global courrier', $this->_recaptglcourrier_payload($ckey, $g));
        }}

        public function recaptglcourrier_export($ckey, $g)
        {{
            $this->_etat_export_dispatch($this->_recaptglcourrier_payload($ckey, $g));
        }}
'''

# Check trecaptpligl signature - may differ. We'll fix after php lint if needed.

code_recaptglcourrieresc = f'''
        protected function _recaptglcourrieresc_payload($ckey, $g)
        {{
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebutcrglesc'));
            $dt2 = trim((string) $this->input->get_post('datefincrglesc'));
            $lign = trim((string) $this->input->get_post('axelignecrglesc'));
            $comp = trim((string) $this->input->get_post('_compagcrglesc'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgarcrglesc'));
            if ($gid === '') {{
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }}
            $typ = trim((string) $this->input->get_post('typcoursglesc'));
            $this->_assert_recap_global_filters($this->entreprise->ekey, $dt1, $dt2, $comp);
            $compLabel = $this->_recap_compagnie_label($comp);
            if ($typ === 'Gros_plis') {{
                $ty3 = 'COLIS';
            }} elseif ($typ === 'Petit_plis') {{
                $ty3 = 'PLIS';
            }} else {{
                $ty3 = 'PLIS/COLIS';
            }}
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            $rows = $this->m_courrier_expedieresc->trecaptpligl($this->entreprise->ekey, $dt1, $dt2, $comp, $gid, $typ, $lign);
            if (!is_array($rows)) {{
                $rows = array();
            }}
            $lignes = array();
            $total = 0.0;
            foreach ($rows as $r) {{
                $nbr = isset($r->nombresesc) ? (int) round((float) $r->nombresesc) : 0;
                $mt = $this->_recap_line_amount(
                    isset($r->montantesc) ? $r->montantesc : null,
                    $nbr,
                    isset($r->prixcolisesc) ? $r->prixcolisesc : 0
                );
                $lignes[] = array(
                    'ligne' => isset($r->nom_ligne) ? (string) $r->nom_ligne : '',
                    'nbr' => $nbr,
                    'pu' => isset($r->prixcolisesc) ? (float) $r->prixcolisesc : 0,
                    'montant' => $mt,
                );
                $total += $mt;
            }}
            $qs = http_build_query(array_filter(array(
                'datedebutcrglesc' => $dt1,
                'datefincrglesc' => $dt2,
                'axelignecrglesc' => $lign,
                '_compagcrglesc' => $comp,
                'departgarcrglesc' => $gid,
                'typcoursglesc' => $typ,
                {connect_qs()}
            )));
            return array(
                'titre' => 'RECAP GLOBAL COURRIERESCAL ' . $ty3 . $compLabel . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                {retour_block()}
                {cols_ligne_nbr_pu_mt()}
                'export_base' => site_url('Rapport/recaptglcourrieresc_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }}

        public function recaptglcourrieresc($ckey, $g)
        {{
            return $this->_etat_render_view('Récap global courrier escal', $this->_recaptglcourrieresc_payload($ckey, $g));
        }}

        public function recaptglcourrieresc_export($ckey, $g)
        {{
            $this->_etat_export_dispatch($this->_recaptglcourrieresc_payload($ckey, $g));
        }}
'''

code_etatsglcourrier = f'''
        protected function _etatsglcourrier_payload($ckey, $g)
        {{
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datesdebutsplig'));
            $dt2 = trim((string) $this->input->get_post('datesfinsplig'));
            $lign = trim((string) $this->input->get_post('axelignesplig'));
            $comp = trim((string) $this->input->get_post('_compagnplig'));
            $typ = trim((string) $this->input->get_post('types_coursplig'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('deptgaresidplig'));
            if ($gid === '') {{
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }}
            $caisRaw = trim((string) $this->input->get_post('caissesidplig'));
            $caisPos = strpos($caisRaw, '/');
            $cais1 = ($caisPos === false) ? $caisRaw : substr($caisRaw, 0, $caisPos);
            $cais2 = ($caisPos === false) ? '' : substr($caisRaw, $caisPos + 1);
            $op = $this->_resolve_report_operateur($cais1);
            $opLabel = ($cais2 !== '') ? $cais2 : $op['label'];
            $ncgd = $this->m_gare_depart->getn($gid);
            $gar = ($ncgd && isset($ncgd->nom_gaep)) ? $ncgd->nom_gaep : $gid;
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            $rows = $this->m_courrier_expedier->texpetatspligl($this->entreprise->ekey, $dt1, $dt2, $gid, $comp, $cais1, $typ, $lign);
            if (!is_array($rows)) {{
                $rows = array();
            }}
            if ($typ === 'Gros_plis') {{
                $ty3 = 'COLIS';
            }} elseif ($typ === 'Petit_plis') {{
                $ty3 = 'PLIS ';
            }} else {{
                $ty3 = '';
            }}
            $lignes = array();
            $total = 0.0;
            foreach ($rows as $r) {{
                $nbr = isset($r->nombres) ? (int) round((float) $r->nombres) : 0;
                $mt = $this->_recap_line_amount(
                    isset($r->montant) ? $r->montant : null,
                    $nbr,
                    isset($r->prixcolis) ? $r->prixcolis : 0
                );
                $lignes[] = array(
                    'ligne' => isset($r->nom_ligne) ? (string) $r->nom_ligne : '',
                    'nbr' => $nbr,
                    'pu' => isset($r->prixcolis) ? (float) $r->prixcolis : 0,
                    'montant' => $mt,
                );
                $total += $mt;
            }}
            $qs = http_build_query(array_filter(array(
                'datesdebutsplig' => $dt1,
                'datesfinsplig' => $dt2,
                'axelignesplig' => $lign,
                '_compagnplig' => $comp,
                'types_coursplig' => $typ,
                'deptgaresidplig' => $gid,
                'caissesidplig' => $caisRaw,
                {connect_qs()}
            )));
            return array(
                'titre' => 'ETAT GLOBAL COURRIER' . $ty3 . '  ' . $cieNom . ' ' . $gar . ' DU ' . $days . ' AU ' . $days1 . ' ' . $opLabel,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                {retour_block()}
                {cols_ligne_nbr_pu_mt()}
                'export_base' => site_url('Rapport/etatsglcourrier_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }}

        public function etatsglcourrier($ckey, $g)
        {{
            return $this->_etat_render_view('État global courrier', $this->_etatsglcourrier_payload($ckey, $g));
        }}

        public function etatsglcourrier_export($ckey, $g)
        {{
            $this->_etat_export_dispatch($this->_etatsglcourrier_payload($ckey, $g));
        }}
'''

code_etatsglcourrieresc = f'''
        protected function _etatsglcourrieresc_payload($ckey, $g)
        {{
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datesdebutspligesc'));
            $dt2 = trim((string) $this->input->get_post('datesfinspligesc'));
            $lign = trim((string) $this->input->get_post('axelignespligesc'));
            $comp = trim((string) $this->input->get_post('_compagnpligesc'));
            $typ = trim((string) $this->input->get_post('types_courspligesc'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('deptgaresidpligesc'));
            if ($gid === '') {{
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }}
            $caisRaw = trim((string) $this->input->get_post('caissesidpligesc'));
            $caisPos = strpos($caisRaw, '/');
            $cais1 = ($caisPos === false) ? $caisRaw : substr($caisRaw, 0, $caisPos);
            $cais2 = ($caisPos === false) ? '' : substr($caisRaw, $caisPos + 1);
            $op = $this->_resolve_report_operateur($cais1);
            $opLabel = ($cais2 !== '') ? $cais2 : $op['label'];
            $ncgd = $this->m_gare_depart->getn($gid);
            $gar = ($ncgd && isset($ncgd->nom_gaep)) ? $ncgd->nom_gaep : $gid;
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            $rows = $this->m_courrier_expedieresc->texpetatspligl($this->entreprise->ekey, $dt1, $dt2, $gid, $comp, $cais1, $typ, $lign);
            if (!is_array($rows)) {{
                $rows = array();
            }}
            if ($typ === 'Gros_plis') {{
                $ty3 = 'COLIS';
            }} elseif ($typ === 'Petit_plis') {{
                $ty3 = 'PLIS ';
            }} else {{
                $ty3 = '';
            }}
            $lignes = array();
            $total = 0.0;
            foreach ($rows as $r) {{
                $nbr = isset($r->nombresesc) ? (int) round((float) $r->nombresesc) : 0;
                $mt = $this->_recap_line_amount(
                    isset($r->montantesc) ? $r->montantesc : null,
                    $nbr,
                    isset($r->prixcolisesc) ? $r->prixcolisesc : 0
                );
                $lignes[] = array(
                    'ligne' => isset($r->nom_ligne) ? (string) $r->nom_ligne : '',
                    'nbr' => $nbr,
                    'pu' => isset($r->prixcolisesc) ? (float) $r->prixcolisesc : 0,
                    'montant' => $mt,
                );
                $total += $mt;
            }}
            $qs = http_build_query(array_filter(array(
                'datesdebutspligesc' => $dt1,
                'datesfinspligesc' => $dt2,
                'axelignespligesc' => $lign,
                '_compagnpligesc' => $comp,
                'types_courspligesc' => $typ,
                'deptgaresidpligesc' => $gid,
                'caissesidpligesc' => $caisRaw,
                {connect_qs()}
            )));
            return array(
                'titre' => 'ETAT GLOBAL COURRIERESCAL' . $ty3 . '  ' . $cieNom . ' ' . $gar . ' DU ' . $days . ' AU ' . $days1 . ' ' . $opLabel,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                {retour_block()}
                {cols_ligne_nbr_pu_mt()}
                'export_base' => site_url('Rapport/etatsglcourrieresc_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }}

        public function etatsglcourrieresc($ckey, $g)
        {{
            return $this->_etat_render_view('État global courrier escal', $this->_etatsglcourrieresc_payload($ckey, $g));
        }}

        public function etatsglcourrieresc_export($ckey, $g)
        {{
            $this->_etat_export_dispatch($this->_etatsglcourrieresc_payload($ckey, $g));
        }}
'''

code_etatsverseplis = f'''
        protected function _etatsverseplis_payload($ckey, $g)
        {{
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datesdebutsplivers'));
            $dt2 = trim((string) $this->input->get_post('datesfinsplivers'));
            $lign = trim((string) $this->input->get_post('axelignesplivers'));
            $comp = trim((string) $this->input->get_post('_compagnplivers'));
            $typ = trim((string) $this->input->get_post('types_coursplivers'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('deptgaresidplivers'));
            if ($gid === '') {{
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }}
            $caisRaw = trim((string) $this->input->get_post('caissesidplivers'));
            $caisPos = strpos($caisRaw, '/');
            $cais1 = ($caisPos === false) ? $caisRaw : substr($caisRaw, 0, $caisPos);
            $cais2 = ($caisPos === false) ? '' : substr($caisRaw, $caisPos + 1);
            $op = $this->_resolve_report_operateur($cais1);
            $opLabel = ($cais2 !== '') ? $cais2 : $op['label'];
            $ncgd = $this->m_gare_depart->getn($gid);
            $gar = ($ncgd && isset($ncgd->nom_gaep)) ? $ncgd->nom_gaep : $gid;
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            $rows = $this->m_courrier_expedier->expverspli($this->entreprise->ekey, $dt1, $dt2, $comp, $gid, $cais1, $typ);
            if (!is_array($rows)) {{
                $rows = array();
            }}
            if ($typ === 'Gros_plis') {{
                $ty3 = 'COLIS';
            }} elseif ($typ === 'Petit_plis') {{
                $ty3 = 'PLIS';
            }} elseif ($typ === '') {{
                $ty3 = 'PLIS/COLIS';
            }} else {{
                $ty3 = $typ;
            }}
            $lignes = array();
            $total = 0.0;
            foreach ($rows as $r) {{
                $mt = isset($r->montant) ? (float) $r->montant : 0.0;
                $d = isset($r->dateenvoi) ? (string) $r->dateenvoi : '';
                $parts = explode('-', $d);
                $daysar = (count($parts) === 3) ? ($parts[2] . '-' . $parts[1] . '-' . $parts[0]) : $d;
                $lignes[] = array('date' => $daysar, 'montant' => $mt);
                $total += $mt;
            }}
            $qs = http_build_query(array_filter(array(
                'datesdebutsplivers' => $dt1,
                'datesfinsplivers' => $dt2,
                'axelignesplivers' => $lign,
                '_compagnplivers' => $comp,
                'types_coursplivers' => $typ,
                'deptgaresidplivers' => $gid,
                'caissesidplivers' => $caisRaw,
                {connect_qs()}
            )));
            return array(
                'titre' => 'BROUILLARD(EXERCICE) COURRIER ' . $opLabel . ' ' . $ty3 . '  ' . $cieNom . ' ' . $gar . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                {retour_block()}
                {cols_date_mt()}
                'export_base' => site_url('Rapport/etatsverseplis_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }}

        public function etatsverseplis($ckey, $g)
        {{
            return $this->_etat_render_view('Brouillard exercice courrier', $this->_etatsverseplis_payload($ckey, $g));
        }}

        public function etatsverseplis_export($ckey, $g)
        {{
            $this->_etat_export_dispatch($this->_etatsverseplis_payload($ckey, $g));
        }}
'''

code_etatsverseplisesc = f'''
        protected function _etatsverseplisesc_payload($ckey, $g)
        {{
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datesdebutspliversesc'));
            $dt2 = trim((string) $this->input->get_post('datesfinspliversesc'));
            $lign = trim((string) $this->input->get_post('axelignespliversesc'));
            $comp = trim((string) $this->input->get_post('_compagnpliversesc'));
            $typ = trim((string) $this->input->get_post('types_courspliversesc'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('deptgaresidpliversesc'));
            if ($gid === '') {{
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }}
            $caisRaw = trim((string) $this->input->get_post('caissesidpliversesc'));
            $caisPos = strpos($caisRaw, '/');
            $cais1 = ($caisPos === false) ? $caisRaw : substr($caisRaw, 0, $caisPos);
            $cais2 = ($caisPos === false) ? '' : substr($caisRaw, $caisPos + 1);
            $op = $this->_resolve_report_operateur($cais1);
            $opLabel = ($cais2 !== '') ? $cais2 : $op['label'];
            $ncgd = $this->m_gare_depart->getn($gid);
            $gar = ($ncgd && isset($ncgd->nom_gaep)) ? $ncgd->nom_gaep : $gid;
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            // Même ordre d'args que l'ancien PDF (signature modèle esc différente).
            $rows = $this->m_courrier_expedieresc->expverspli($this->entreprise->ekey, $dt1, $dt2, $comp, $gid, $cais1, $typ);
            if (!is_array($rows)) {{
                $rows = array();
            }}
            if ($typ === 'Gros_plis') {{
                $ty3 = 'COLIS';
            }} elseif ($typ === 'Petit_plis') {{
                $ty3 = 'PLIS';
            }} elseif ($typ === '') {{
                $ty3 = 'PLIS/COLIS';
            }} else {{
                $ty3 = $typ;
            }}
            $lignes = array();
            $total = 0.0;
            foreach ($rows as $r) {{
                $mt = isset($r->montantesc) ? (float) $r->montantesc : 0.0;
                $d = isset($r->dateenvoiesc) ? (string) $r->dateenvoiesc : '';
                $parts = explode('-', $d);
                $daysar = (count($parts) === 3) ? ($parts[2] . '-' . $parts[1] . '-' . $parts[0]) : $d;
                $lignes[] = array('date' => $daysar, 'montant' => $mt);
                $total += $mt;
            }}
            $qs = http_build_query(array_filter(array(
                'datesdebutspliversesc' => $dt1,
                'datesfinspliversesc' => $dt2,
                'axelignespliversesc' => $lign,
                '_compagnpliversesc' => $comp,
                'types_courspliversesc' => $typ,
                'deptgaresidpliversesc' => $gid,
                'caissesidpliversesc' => $caisRaw,
                {connect_qs()}
            )));
            return array(
                'titre' => 'BROUILLARD(EXERCICE) COURRIERESCAL ' . $opLabel . ' ' . $ty3 . '  ' . $cieNom . ' ' . $gar . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                {retour_block()}
                {cols_date_mt()}
                'export_base' => site_url('Rapport/etatsverseplisesc_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }}

        public function etatsverseplisesc($ckey, $g)
        {{
            return $this->_etat_render_view('Brouillard exercice courrier escal', $this->_etatsverseplisesc_payload($ckey, $g));
        }}

        public function etatsverseplisesc_export($ckey, $g)
        {{
            $this->_etat_export_dispatch($this->_etatsverseplisesc_payload($ckey, $g));
        }}
'''

# Apply replacements
pairs = [
    ('reportsesc', code_reportsesc),
    ('reporticketesc', code_reporticketesc),
    ('triencaissementsexoesc', code_triencaissementsexoesc),
    ('reportbag', bag_recap('reportbag', 'bg',
        "$this->m_bagage->reportbag($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign)",
        'codid_bagage', 'prix_bagage', 'total', 'RECAP GLOBAL BAGAGES', 'Récap global bagages', 'reportbag_export')),
    ('reportbagesc', bag_recap('reportbagesc', 'bgesc',
        "$this->m_bagageesc->reportbag($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $lign)",
        'codid_bagageesc', 'prix_bagageesc', 'total', 'RECAP GLOBAL BAGAGES ESCAL', 'Récap global bagages escal', 'reportbagesc_export')),
    ('reportbaggl', bag_gl('reportbaggl',
        ('datedebutbagopgl', 'datefinbagopgl', 'axelignebagopgl', '_compagbagopgl', 'departgarbagopgl', 'vendeuseidopgl'),
        "$this->m_bagage->reportbaggl($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $ivd, $lign)",
        'codid_bagage', 'prix_bagage', 'ETAT GLOBAL BAGAGES', 'État global bagages', 'reportbaggl_export', True)),
    ('reportbagglesc', bag_gl('reportbagglesc',
        ('datedebutbagopglesc', 'datefinbagopglesc', 'axelignebagopglesc', '_compagbagopglesc', 'departgarbagopglesc', 'vendeuseidopglesc'),
        "$this->m_bagageesc->reportbaggl($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $ivd, $lign)",
        'codid_bagageesc', 'prix_bagageesc', 'ETAT GLOBAL BAGAGES ESCAL', 'État global bagages escal', 'reportbagglesc_export', True)),
    ('triencaissementsexobag', bag_brouillard('triencaissementsexobag',
        ('vendeuseidexobg', 'datedexobg', 'datefexobg', '_compagexobg', 'departgarexobg'),
        "$this->m_bagage->listereportverscptglexo($this->entreprise->ekey, $comp, $ddbt, $dfin, $gid, $ivd)",
        'date_create', 'total', 'BROUILLARD(EXERCICE)BAGAGES', 'Brouillard exercice bagages', 'triencaissementsexobag_export')),
    ('triencaissementsexobagesc', bag_brouillard('triencaissementsexobagesc',
        ('vendeuseidexobgesc', 'datedexobgesc', 'datefexobgesc', '_compagexobgesc', 'departgarexobgesc'),
        "$this->m_bagageesc->listereportverscptglexo($this->entreprise->ekey, $comp, $ddbt, $dfin, $gid, $ivd)",
        'date_createesc', 'total', 'BROUILLARD(EXERCICE)BAGAGES ESCAL', 'Brouillard exercice bagages escal', 'triencaissementsexobagesc_export')),
    ('recaptglcourrier', code_recaptglcourrier),
    ('recaptglcourrieresc', code_recaptglcourrieresc),
    ('etatsglcourrier', code_etatsglcourrier),
    ('etatsglcourrieresc', code_etatsglcourrieresc),
    ('etatsverseplis', code_etatsverseplis),
    ('etatsverseplisesc', code_etatsverseplisesc),
]

for name, code in pairs:
    src = replace_method(src, name, code)
    print('OK', name)

path.write_text(src, encoding='utf-8')
print('Wrote', path)
