#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""Lot 2: reports / versements ticket → in-app (Retour + export)."""
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
    raise SystemExit('Unbalanced for ' + method_name)


QS = """'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
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

COLS_DATE_TYPE = """'columns' => array(
                    array('key' => 'date', 'label' => 'Date', 'align' => 'left'),
                    array('key' => 'type', 'label' => 'Type', 'align' => 'left'),
                    array('key' => 'montant', 'label' => 'Prix total', 'align' => 'right', 'money' => true),
                ),"""


def pub(name, label):
    return f'''
        public function {name}($ckey, $g)
        {{
            return $this->_etat_render_view('{label}', $this->_{name}_payload($ckey, $g));
        }}

        public function {name}_export($ckey, $g)
        {{
            $this->_etat_export_dispatch($this->_{name}_payload($ckey, $g));
        }}
'''


code_reportscour = f'''
        protected function _reportscour_payload($ckey, $g)
        {{
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebutcour'));
            $dt2 = trim((string) $this->input->get_post('datefincour'));
            $cais = trim((string) $this->input->get_post('caissiercour'));
            $lign = trim((string) $this->input->get_post('axelignecour'));
            $comp = trim((string) $this->input->get_post('_compagcour'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgarcour'));
            if ($gid === '') {{
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }}
            $op = $this->_resolve_report_operateur($cais);
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);

            $recettereport = $this->m_courrier_expedier->listereportcour($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $cais, $lign);
            $transfertreport = $this->m_courrier_recet->reporttransfert($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $cais);
            if (!is_array($recettereport)) {{
                $recettereport = array();
            }}
            if (!is_array($transfertreport)) {{
                $transfertreport = array();
            }}

            $lignes = array();
            $total = 0.0;
            foreach ($recettereport as $element) {{
                $nbr = isset($element->nombres) ? (int) round((float) $element->nombres) : 0;
                $mt = isset($element->montant) ? (float) $element->montant : 0.0;
                $lignes[] = array(
                    'ligne' => isset($element->nom_ligne) ? (string) $element->nom_ligne : '',
                    'nbr' => $nbr,
                    'pu' => isset($element->prixcolis) ? (float) $element->prixcolis : 0.0,
                    'montant' => $mt,
                );
                $total += $mt;
            }}
            // Transfert : appel conservé (comme PDF ; boucle historique commentée).
            if ($transfertreport) {{
                /* no-op */
            }}

            $qs = http_build_query(array_filter(array(
                'datedebutcour' => $dt1,
                'datefincour' => $dt2,
                'caissiercour' => $cais,
                'axelignecour' => $lign,
                '_compagcour' => $comp,
                'departgarcour' => $gid,
                {QS}
            )));
            return array(
                'titre' => 'REPORT GLOBAL DES COURRIERS ' . $op['label'] . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                {RETOUR}
                {COLS_LIGNE}
                'export_base' => site_url('Rapport/reportscour_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }}
{pub('reportscour', 'Report global courriers')}
'''

code_exoversement = f'''
        protected function _exoversement_payload($ckey, $g)
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
            foreach ($onreport as $element) {{
                $nbr = isset($element->codepassager) ? (int) round((float) $element->codepassager) : 0;
                $mt = $this->_recap_line_amount(
                    isset($element->total) ? $element->total : null,
                    $nbr,
                    isset($element->prixvente) ? $element->prixvente : 0
                );
                $lignes[] = array(
                    'ligne' => isset($element->nom_ligne) ? (string) $element->nom_ligne : '',
                    'nbr' => $nbr,
                    'pu' => isset($element->prixvente) ? (float) $element->prixvente : 0.0,
                    'montant' => $mt,
                );
                $total += $mt;
            }}
            foreach ($retourreport as $retour) {{
                $nbr = isset($retour->code_non_pass) ? (int) round((float) $retour->code_non_pass) : 0;
                $mt = $this->_recap_line_amount(
                    isset($retour->totalr) ? $retour->totalr : null,
                    $nbr,
                    isset($retour->prixretour) ? $retour->prixretour : 0
                );
                $lignes[] = array(
                    'ligne' => $this->_recap_invert_ligne_nom(isset($retour->nom_ligne) ? $retour->nom_ligne : ''),
                    'nbr' => $nbr,
                    'pu' => isset($retour->prixretour) ? (float) $retour->prixretour : 0.0,
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
                {QS}
            )));
            return array(
                'titre' => 'REPORT DES TICKETS ' . $op['label'] . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                {RETOUR}
                {COLS_LIGNE}
                'export_base' => site_url('Rapport/exoversement_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }}
{pub('exoversement', 'Report des tickets')}
'''

code_exoreportsvers = f'''
        protected function _exoreportsvers_payload($ckey, $g)
        {{
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebutvers'));
            $dt2 = trim((string) $this->input->get_post('datefinvers'));
            $cais = trim((string) $this->input->get_post('caissiervers'));
            $lign = trim((string) $this->input->get_post('axelignevers'));
            $comp = trim((string) $this->input->get_post('_compagvers'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgarvers'));
            if ($gid === '') {{
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }}
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            $ncgd = $this->m_gare_depart->getn($gid);
            $gar = ($ncgd && isset($ncgd->nom_gaep)) ? $ncgd->nom_gaep : $gid;
            $op = $this->_resolve_report_operateur($cais);
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);

            if ((string) $comp === '5002') {{
                $onreport = $this->m_passager->listereportverscptgl($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $cais, $lign);
                $retourreport = $this->m_non_passager->listereportversretourcptad($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $cais, $lign);
            }} else {{
                $onreport = $this->m_passager->listereportverscpt($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $cais);
                $retourreport = $this->m_non_passager->listereportversretourcpt($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $cais);
            }}
            if (!is_array($onreport)) {{
                $onreport = array();
            }}
            if (!is_array($retourreport)) {{
                $retourreport = array();
            }}

            $lignes = array();
            $total = 0.0;
            foreach ($onreport as $element) {{
                $mt = isset($element->total) ? (float) $element->total : 0.0;
                $lignes[] = array(
                    'date' => isset($element->datep_create) ? (string) $element->datep_create : '',
                    'type' => 'Aller',
                    'montant' => $mt,
                );
                $total += $mt;
            }}
            foreach ($retourreport as $retour) {{
                $mt = isset($retour->totalr) ? (float) $retour->totalr : 0.0;
                $lignes[] = array(
                    'date' => isset($retour->datevente) ? (string) $retour->datevente : '',
                    'type' => 'Retour',
                    'montant' => $mt,
                );
                $total += $mt;
            }}

            $qs = http_build_query(array_filter(array(
                'datedebutvers' => $dt1,
                'datefinvers' => $dt2,
                'caissiervers' => $cais,
                'axelignevers' => $lign,
                '_compagvers' => $comp,
                'departgarvers' => $gid,
                {QS}
            )));
            return array(
                'titre' => 'REPORT MENSUEL DES RECETTES ' . $cieNom . ' ' . $gar . ' ' . $op['label'] . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                {RETOUR}
                {COLS_DATE_TYPE}
                'export_base' => site_url('Rapport/exoreportsvers_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }}
{pub('exoreportsvers', 'Report mensuel recettes')}
'''

COLS_VENTE = """'columns' => array(
                    array('key' => 'tampon', 'label' => 'Code tampon', 'align' => 'left'),
                    array('key' => 'code_passager', 'label' => 'Code passager', 'align' => 'left'),
                    array('key' => 'code_ticket', 'label' => 'Code ticket', 'align' => 'left'),
                    array('key' => 'nom', 'label' => 'Nom', 'align' => 'left'),
                    array('key' => 'prenom', 'label' => 'Prénom', 'align' => 'left'),
                    array('key' => 'contact', 'label' => 'Contact', 'align' => 'left'),
                    array('key' => 'ligne', 'label' => 'Ligne', 'align' => 'left'),
                    array('key' => 'datevente', 'label' => 'Date vente', 'align' => 'left'),
                    array('key' => 'depart', 'label' => 'Départ', 'align' => 'left'),
                ),"""

code_exoreportsventegl = f'''
        protected function _exoreportsventegl_payload($ckey, $g)
        {{
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebutventegl'));
            $dt2 = trim((string) $this->input->get_post('datefinventegl'));
            $cais = trim((string) $this->input->get_post('caissierventegl'));
            $lign = trim((string) $this->input->get_post('axeligneventegl'));
            $comp = trim((string) $this->input->get_post('_compagventegl'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgarventegl'));
            if ($gid === '') {{
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }}
            $ncgd = $this->m_gare_depart->getn($gid);
            $gar = ($ncgd && isset($ncgd->nom_gaep)) ? $ncgd->nom_gaep : $gid;
            $op = $this->_resolve_report_operateur($cais);
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);

            $onreport = $this->m_passager->histoventeadmin($this->entreprise->ekey, $gid, $dt1, $dt2, $cais, $comp, $lign);
            if (!is_array($onreport)) {{
                $onreport = array();
            }}
            $lignes = array();
            foreach ($onreport as $element) {{
                $lignes[] = array(
                    'tampon' => isset($element->tamponcod) ? (string) $element->tamponcod : '',
                    'code_passager' => isset($element->code_passager) ? (string) $element->code_passager : '',
                    'code_ticket' => isset($element->code_ticket) ? (string) $element->code_ticket : '',
                    'nom' => isset($element->nom_client) ? (string) $element->nom_client : '',
                    'prenom' => isset($element->prenom_client) ? (string) $element->prenom_client : '',
                    'contact' => isset($element->contact_client) ? (string) $element->contact_client : '',
                    'ligne' => isset($element->nom_ligne) ? (string) $element->nom_ligne : '',
                    'datevente' => isset($element->datep_create) ? (string) $element->datep_create : '',
                    'depart' => isset($element->dateheure_prog) ? (string) $element->dateheure_prog : '',
                );
            }}

            $qs = http_build_query(array_filter(array(
                'datedebutventegl' => $dt1,
                'datefinventegl' => $dt2,
                'caissierventegl' => $cais,
                'axeligneventegl' => $lign,
                '_compagventegl' => $comp,
                'departgarventegl' => $gid,
                {QS}
            )));
            return array(
                'titre' => 'REPORT DES VENTES ' . $op['label'] . ' ' . $gar . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => 0.0,
                'filters_qs' => $qs,
                {RETOUR}
                {COLS_VENTE}
                'export_base' => site_url('Rapport/exoreportsventegl_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }}
{pub('exoreportsventegl', 'Report des ventes (global)')}
'''

code_exoreportsvente = f'''
        protected function _exoreportsvente_payload($ckey, $g)
        {{
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebutvente'));
            $dt2 = trim((string) $this->input->get_post('datefinvente'));
            $cais = trim((string) $this->input->get_post('caissiervente'));
            $lign = trim((string) $this->input->get_post('axelignevente'));
            $comp = trim((string) $this->input->get_post('_compagvente'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgarvente'));
            if ($gid === '') {{
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }}
            $op = $this->_resolve_report_operateur($cais);
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);

            if ((string) $comp === '5002') {{
                $onreport = $this->m_passager->histoventeadmin($this->entreprise->ekey, $gid, $dt1, $dt2, $cais, $comp, $lign);
            }} else {{
                $onreport = $this->m_passager->histovente($this->entreprise->ekey, $gid, $dt1, $dt2, $cais, $comp, $lign);
            }}
            if (!is_array($onreport)) {{
                $onreport = array();
            }}
            $lignes = array();
            foreach ($onreport as $element) {{
                $lignes[] = array(
                    'tampon' => isset($element->tamponcod) ? (string) $element->tamponcod : '',
                    'code_passager' => isset($element->code_passager) ? (string) $element->code_passager : '',
                    'code_ticket' => isset($element->code_ticket) ? (string) $element->code_ticket : '',
                    'nom' => isset($element->nom_client) ? (string) $element->nom_client : '',
                    'prenom' => isset($element->prenom_client) ? (string) $element->prenom_client : '',
                    'contact' => isset($element->contact_client) ? (string) $element->contact_client : '',
                    'ligne' => isset($element->nom_ligne) ? (string) $element->nom_ligne : '',
                    'datevente' => isset($element->datep_create) ? (string) $element->datep_create : '',
                    'depart' => isset($element->dateheure_prog) ? (string) $element->dateheure_prog : '',
                );
            }}

            $qs = http_build_query(array_filter(array(
                'datedebutvente' => $dt1,
                'datefinvente' => $dt2,
                'caissiervente' => $cais,
                'axelignevente' => $lign,
                '_compagvente' => $comp,
                'departgarvente' => $gid,
                {QS}
            )));
            return array(
                'titre' => 'REPORT DES VENTES ' . $op['label'] . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => 0.0,
                'filters_qs' => $qs,
                {RETOUR}
                {COLS_VENTE}
                'export_base' => site_url('Rapport/exoreportsvente_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }}
{pub('exoreportsvente', 'Report des ventes')}
'''


for name, code in [
    ('reportscour', code_reportscour),
    ('exoversement', code_exoversement),
    ('exoreportsvers', code_exoreportsvers),
    ('exoreportsventegl', code_exoreportsventegl),
    ('exoreportsvente', code_exoreportsvente),
]:
    src = replace_method(src, name, code)
    print('OK', name)

path.write_text(src, encoding='utf-8')
print('Wrote', path)
