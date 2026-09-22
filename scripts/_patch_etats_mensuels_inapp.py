#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""Lot: tridepensescour + exercices mensuels → in-app."""
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

COLS_LIGNE = """'columns' => array(
                    array('key' => 'ligne', 'label' => 'Ligne', 'align' => 'left'),
                    array('key' => 'nbr', 'label' => 'Nombre', 'align' => 'center'),
                    array('key' => 'pu', 'label' => 'Prix unitaire', 'align' => 'right', 'money' => true),
                    array('key' => 'montant', 'label' => 'Prix total', 'align' => 'right', 'money' => true),
                ),"""

COLS_DATE = """'columns' => array(
                    array('key' => 'date', 'label' => 'Date', 'align' => 'left'),
                    array('key' => 'montant', 'label' => 'Montant', 'align' => 'right', 'money' => true),
                ),"""

code_tridepensescour = f'''
        protected function _tridepensescour_payload($ckey, $g)
        {{
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $ivd = trim((string) $this->input->get_post('caissiercourdep'));
            $ddbt = trim((string) $this->input->get_post('datedebutcourdep'));
            $dfin = trim((string) $this->input->get_post('datefincourdep'));
            $comp = trim((string) $this->input->get_post('_compagcourdep'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgarcourdep'));
            if ($gid === '') {{
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }}
            $op = $this->_resolve_report_operateur($ivd);
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            list($days, $days1) = $this->_recap_title_dates($ddbt, $dfin);
            $rows = $this->m_comptes_courrierdepens->depsfiltrecour($this->entreprise->ekey, $gid, $ddbt, $dfin, $comp, $ivd);
            if (!is_array($rows)) {{
                $rows = array();
            }}
            $lignes = array();
            $total = 0.0;
            foreach ($rows as $items) {{
                $mt = isset($items->comptemontdepens) ? (float) $items->comptemontdepens : 0.0;
                $d = isset($items->comptdatearretdepens) ? (string) $items->comptdatearretdepens : '';
                $parts = explode('-', $d);
                $daysar = (count($parts) === 3) ? ($parts[2] . '-' . $parts[1] . '-' . $parts[0]) : $d;
                $lignes[] = array('date' => $daysar, 'montant' => $mt);
                $total += $mt;
            }}
            $qs = http_build_query(array_filter(array(
                'caissiercourdep' => $ivd,
                'datedebutcourdep' => $ddbt,
                'datefincourdep' => $dfin,
                '_compagcourdep' => $comp,
                'departgarcourdep' => $gid,
                {QS_CONNECT}
            )));
            return array(
                'titre' => 'RECAP DEPENSE COURRIER  ' . $op['label'] . ' ' . $cieNom . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                {RETOUR}
                {COLS_DATE}
                'export_base' => site_url('Rapport/tridepensescour_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }}

        public function tridepensescour($ckey, $g)
        {{
            return $this->_etat_render_view('Récap dépense courrier', $this->_tridepensescour_payload($ckey, $g));
        }}

        public function tridepensescour_export($ckey, $g)
        {{
            $this->_etat_export_dispatch($this->_tridepensescour_payload($ckey, $g));
        }}
'''

code_exoreports = f'''
        protected function _exoreports_payload($ckey, $g)
        {{
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebut'));
            $dt2 = trim((string) $this->input->get_post('datefin'));
            $cais = trim((string) $this->input->get_post('caissier'));
            $lign = trim((string) $this->input->get_post('axeligne'));
            $comp = trim((string) $this->input->get_post('_compag'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgar'));
            if ($gid === '') {{
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }}
            $op = $this->_resolve_report_operateur($cais);
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);

            $role = isset($this->session->agent->userole) ? (string) $this->session->agent->userole : '';
            $isAdmin = ($role === '1' || $role === '2');
            if ((string) $comp === '5002') {{
                $onreport = $this->m_passager->listereport($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $cais, $lign);
                $retourreport = $this->m_non_passager->listereportretour($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $cais, $lign);
            }} elseif ($isAdmin) {{
                $onreport = $this->m_passager->listereportcptadmin($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $cais, $lign);
                $retourreport = $this->m_non_passager->listereportretourcptadmin($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $cais, $lign);
            }} else {{
                $onreport = $this->m_passager->listereportcpt($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $cais, $lign);
                $retourreport = $this->m_non_passager->listereportretourcpt($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $cais, $lign);
            }}
            if (!is_array($onreport)) {{
                $onreport = array();
            }}
            if (!is_array($retourreport)) {{
                $retourreport = array();
            }}

            $lignes = array();
            $total = 0.0;
            foreach ($onreport as $pm) {{
                $nbr = isset($pm->codepassager) ? (int) round((float) $pm->codepassager) : 0;
                $mt = $this->_recap_line_amount(
                    isset($pm->total) ? $pm->total : null,
                    $nbr,
                    isset($pm->prixvente) ? $pm->prixvente : 0
                );
                $lignes[] = array(
                    'ligne' => isset($pm->nom_ligne) ? (string) $pm->nom_ligne : '',
                    'nbr' => $nbr,
                    'pu' => isset($pm->prixvente) ? (float) $pm->prixvente : 0,
                    'montant' => $mt,
                );
                $total += $mt;
            }}
            foreach ($retourreport as $rm) {{
                $nbr = isset($rm->code_non_pass) ? (int) round((float) $rm->code_non_pass) : 0;
                $mt = $this->_recap_line_amount(
                    isset($rm->totalr) ? $rm->totalr : null,
                    $nbr,
                    isset($rm->prixretour) ? $rm->prixretour : 0
                );
                $lignes[] = array(
                    'ligne' => $this->_recap_invert_ligne_nom(isset($rm->nom_ligne) ? $rm->nom_ligne : ''),
                    'nbr' => $nbr,
                    'pu' => isset($rm->prixretour) ? (float) $rm->prixretour : 0,
                    'montant' => $mt,
                );
                $total += $mt;
            }}

            $qs = http_build_query(array_filter(array(
                'datedebut' => $dt1,
                'datefin' => $dt2,
                'caissier' => $cais,
                'axeligne' => $lign,
                '_compag' => $comp,
                'departgar' => $gid,
                {QS_CONNECT}
            )));
            return array(
                'titre' => 'EXERCICE MENSUEL TICKET GUICHETIER ' . $op['label'] . ' ' . $cieNom . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                {RETOUR}
                {COLS_LIGNE}
                'export_base' => site_url('Rapport/exoreports_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }}

        public function exoreports($ckey, $g)
        {{
            return $this->_etat_render_view('Exercice mensuel ticket guichetier', $this->_exoreports_payload($ckey, $g));
        }}

        public function exoreports_export($ckey, $g)
        {{
            $this->_etat_export_dispatch($this->_exoreports_payload($ckey, $g));
        }}
'''

code_exoreportsesc = f'''
        protected function _exoreportsesc_payload($ckey, $g)
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
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            $onreport = $this->m_escalclients->listereportcptesc($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $cais, $lign);
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
                {QS_CONNECT}
            )));
            return array(
                'titre' => 'EXERCICE MENSUEL TICKET GUICHETIER ESCAL ' . $op['label'] . ' ' . $cieNom . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                {RETOUR}
                {COLS_LIGNE}
                'export_base' => site_url('Rapport/exoreportsesc_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }}

        public function exoreportsesc($ckey, $g)
        {{
            return $this->_etat_render_view('Exercice mensuel ticket guichetier escal', $this->_exoreportsesc_payload($ckey, $g));
        }}

        public function exoreportsesc_export($ckey, $g)
        {{
            $this->_etat_export_dispatch($this->_exoreportsesc_payload($ckey, $g));
        }}
'''

code_exercicesbagop = f'''
        protected function _exercicesbagop_payload($ckey, $g)
        {{
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebutbagop'));
            $dt2 = trim((string) $this->input->get_post('datefinbagop'));
            $lign = trim((string) $this->input->get_post('axelignebagop'));
            $comp = trim((string) $this->input->get_post('_compagbagop'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgarbagop'));
            if ($gid === '') {{
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }}
            $cais = trim((string) $this->input->get_post('vendeuseidop'));
            $op = $this->_resolve_report_operateur($cais);
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            $reportick = $this->m_bagage->reportbgcptop($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $cais, $lign);
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
                'datedebutbagop' => $dt1,
                'datefinbagop' => $dt2,
                'axelignebagop' => $lign,
                '_compagbagop' => $comp,
                'departgarbagop' => $gid,
                'vendeuseidop' => $cais,
                {QS_CONNECT}
            )));
            return array(
                'titre' => 'EXERCICE MENSUEL BAGAGE ' . $op['label'] . ' ' . $cieNom . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                {RETOUR}
                {COLS_LIGNE}
                'export_base' => site_url('Rapport/exercicesbagop_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }}

        public function exercicesbagop($ckey, $g)
        {{
            return $this->_etat_render_view('Exercice mensuel bagage', $this->_exercicesbagop_payload($ckey, $g));
        }}

        public function exercicesbagop_export($ckey, $g)
        {{
            $this->_etat_export_dispatch($this->_exercicesbagop_payload($ckey, $g));
        }}
'''

code_exercicesbagopesc = f'''
        protected function _exercicesbagopesc_payload($ckey, $g)
        {{
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebutbagopesc'));
            $dt2 = trim((string) $this->input->get_post('datefinbagopesc'));
            $lign = trim((string) $this->input->get_post('axelignebagopesc'));
            $comp = trim((string) $this->input->get_post('_compagbagopesc'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgarbagopesc'));
            if ($gid === '') {{
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }}
            $cais = trim((string) $this->input->get_post('vendeuseidopesc'));
            $op = $this->_resolve_report_operateur($cais);
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            $reportick = $this->m_bagageesc->reportbgcptop($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $cais, $lign);
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
                'datedebutbagopesc' => $dt1,
                'datefinbagopesc' => $dt2,
                'axelignebagopesc' => $lign,
                '_compagbagopesc' => $comp,
                'departgarbagopesc' => $gid,
                'vendeuseidopesc' => $cais,
                {QS_CONNECT}
            )));
            return array(
                'titre' => 'EXERCICE MENSUEL BAGAGE ESCAL ' . $op['label'] . ' ' . $cieNom . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                {RETOUR}
                {COLS_LIGNE}
                'export_base' => site_url('Rapport/exercicesbagopesc_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }}

        public function exercicesbagopesc($ckey, $g)
        {{
            return $this->_etat_render_view('Exercice mensuel bagage escal', $this->_exercicesbagopesc_payload($ckey, $g));
        }}

        public function exercicesbagopesc_export($ckey, $g)
        {{
            $this->_etat_export_dispatch($this->_exercicesbagopesc_payload($ckey, $g));
        }}
'''

code_etatsplis1 = f'''
        protected function _etatsplis1_payload($ckey, $g)
        {{
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datesdebutspli'));
            $dt2 = trim((string) $this->input->get_post('datesfinspli'));
            $lign = trim((string) $this->input->get_post('axelignespli'));
            $comp = trim((string) $this->input->get_post('_compagnpli'));
            $typcr = trim((string) $this->input->get_post('types_courspli'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('deptgaresidpli'));
            if ($gid === '') {{
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }}
            $caisRaw = trim((string) $this->input->get_post('caissesidpli'));
            $caisPos = strpos($caisRaw, '/');
            $cais1 = ($caisPos === false) ? $caisRaw : substr($caisRaw, 0, $caisPos);
            $cais2 = ($caisPos === false) ? '' : substr($caisRaw, $caisPos + 1);
            $op = $this->_resolve_report_operateur($cais1);
            $opLabel = ($cais2 !== '') ? $cais2 : $op['label'];
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            $ncgd = $this->m_gare_depart->getn($gid);
            $gar = ($ncgd && isset($ncgd->nom_gaep)) ? $ncgd->nom_gaep : $gid;
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            $expcours = $this->m_courrier_expedier->expetatsplis($this->entreprise->ekey, $dt1, $dt2, $gid, $comp, $cais1, $typcr, $lign);
            if (!is_array($expcours)) {{
                $expcours = array();
            }}
            if ($typcr === 'Gros_plis') {{
                $ty3 = 'COLIS';
            }} elseif ($typcr === 'Petit_plis') {{
                $ty3 = 'PLIS';
            }} elseif ($typcr === '') {{
                $ty3 = 'PLIS/COLIS';
            }} else {{
                $ty3 = $typcr;
            }}
            $lignes = array();
            $total = 0.0;
            foreach ($expcours as $element) {{
                $nbr = isset($element->nombres) ? (float) $element->nombres : 0.0;
                $pu = isset($element->prixcolis) ? (float) $element->prixcolis : 0.0;
                $mt = $nbr * $pu;
                $lignes[] = array(
                    'ligne' => isset($element->nom_ligne) ? (string) $element->nom_ligne : '',
                    'nbr' => (int) round($nbr),
                    'pu' => $pu,
                    'montant' => $mt,
                );
                $total += $mt;
            }}
            $qs = http_build_query(array_filter(array(
                'datesdebutspli' => $dt1,
                'datesfinspli' => $dt2,
                'axelignespli' => $lign,
                '_compagnpli' => $comp,
                'types_courspli' => $typcr,
                'deptgaresidpli' => $gid,
                'caissesidpli' => $caisRaw,
                {QS_CONNECT}
            )));
            return array(
                'titre' => 'EXERCICE MENSUEL GUICHETIER ' . $opLabel . ' ' . $ty3 . '  ' . $cieNom . ' ' . $gar . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                {RETOUR}
                {COLS_LIGNE}
                'export_base' => site_url('Rapport/etatsplis1_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }}

        public function etatsplis1($ckey, $g)
        {{
            return $this->_etat_render_view('Exercice mensuel courrier guichetier', $this->_etatsplis1_payload($ckey, $g));
        }}

        public function etatsplis1_export($ckey, $g)
        {{
            $this->_etat_export_dispatch($this->_etatsplis1_payload($ckey, $g));
        }}
'''

code_etatsplis1esc = f'''
        protected function _etatsplis1esc_payload($ckey, $g)
        {{
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datesdebutspliesc'));
            $dt2 = trim((string) $this->input->get_post('datesfinspliesc'));
            $lign = trim((string) $this->input->get_post('axelignespliesc'));
            $comp = trim((string) $this->input->get_post('_compagnpliesc'));
            $typcr = trim((string) $this->input->get_post('types_courspliesc'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('deptgaresidpliesc'));
            if ($gid === '') {{
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }}
            $caisRaw = trim((string) $this->input->get_post('caissesidpliesc'));
            $caisPos = strpos($caisRaw, '/');
            $cais1 = ($caisPos === false) ? $caisRaw : substr($caisRaw, 0, $caisPos);
            $cais2 = ($caisPos === false) ? '' : substr($caisRaw, $caisPos + 1);
            $op = $this->_resolve_report_operateur($cais1);
            $opLabel = ($cais2 !== '') ? $cais2 : $op['label'];
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            $ncgd = $this->m_gare_depart->getn($gid);
            $gar = ($ncgd && isset($ncgd->nom_gaep)) ? $ncgd->nom_gaep : $gid;
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            $expcours = $this->m_courrier_expedieresc->expetatsplis($this->entreprise->ekey, $dt1, $dt2, $gid, $comp, $cais1, $typcr, $lign);
            if (!is_array($expcours)) {{
                $expcours = array();
            }}
            if ($typcr === 'Gros_plis') {{
                $ty3 = 'COLIS';
            }} elseif ($typcr === 'Petit_plis') {{
                $ty3 = 'PLIS';
            }} elseif ($typcr === '') {{
                $ty3 = 'PLIS/COLIS';
            }} else {{
                $ty3 = $typcr;
            }}
            $lignes = array();
            $total = 0.0;
            foreach ($expcours as $element) {{
                $nbr = isset($element->nombresesc) ? (float) $element->nombresesc : (isset($element->nombres) ? (float) $element->nombres : 0.0);
                $pu = isset($element->prixcolisesc) ? (float) $element->prixcolisesc : (isset($element->prixcolis) ? (float) $element->prixcolis : 0.0);
                $mt = $nbr * $pu;
                $lignes[] = array(
                    'ligne' => isset($element->nom_ligne) ? (string) $element->nom_ligne : '',
                    'nbr' => (int) round($nbr),
                    'pu' => $pu,
                    'montant' => $mt,
                );
                $total += $mt;
            }}
            $qs = http_build_query(array_filter(array(
                'datesdebutspliesc' => $dt1,
                'datesfinspliesc' => $dt2,
                'axelignespliesc' => $lign,
                '_compagnpliesc' => $comp,
                'types_courspliesc' => $typcr,
                'deptgaresidpliesc' => $gid,
                'caissesidpliesc' => $caisRaw,
                {QS_CONNECT}
            )));
            return array(
                'titre' => 'EXERCICE MENSUEL ESCAL GUICHETIER ' . $opLabel . ' ' . $ty3 . '  ' . $cieNom . ' ' . $gar . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                {RETOUR}
                {COLS_LIGNE}
                'export_base' => site_url('Rapport/etatsplis1esc_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }}

        public function etatsplis1esc($ckey, $g)
        {{
            return $this->_etat_render_view('Exercice mensuel courrier escal guichetier', $this->_etatsplis1esc_payload($ckey, $g));
        }}

        public function etatsplis1esc_export($ckey, $g)
        {{
            $this->_etat_export_dispatch($this->_etatsplis1esc_payload($ckey, $g));
        }}
'''

pairs = [
    ('tridepensescour', code_tridepensescour),
    ('exoreports', code_exoreports),
    ('exoreportsesc', code_exoreportsesc),
    ('exercicesbagop', code_exercicesbagop),
    ('exercicesbagopesc', code_exercicesbagopesc),
    ('etatsplis1', code_etatsplis1),
    ('etatsplis1esc', code_etatsplis1esc),
]

for name, code in pairs:
    src = replace_method(src, name, code)
    print('OK', name)

path.write_text(src, encoding='utf-8')
print('Wrote', path)
