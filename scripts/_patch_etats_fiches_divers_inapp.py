#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""Lot 4: fiches / divers / soldes / tris caisse / bagages → in-app."""
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


def pub(name, label, args='$ckey', payload_args=None):
    if payload_args is None:
        payload_args = args
    return f'''
        public function {name}({args})
        {{
            return $this->_etat_render_view('{label}', $this->_{name}_payload({payload_args}));
        }}

        public function {name}_export({args})
        {{
            $this->_etat_export_dispatch($this->_{name}_payload({payload_args}));
        }}
'''


def pub_mutate(name, label, args='$ckey'):
    return f'''
        public function {name}({args})
        {{
            return $this->_etat_render_view('{label}', $this->_{name}_payload({args}, true));
        }}

        public function {name}_export({args})
        {{
            $this->_etat_export_dispatch($this->_{name}_payload({args}, false));
        }}
'''


# --- soldes (params URL) ---
code_reportsolde = f'''
        protected function _reportsolde_payload($ckey, $iuser, $r, $dpe, $dpo, $d)
        {{
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            list($days, ) = $this->_recap_title_dates($d, $d);
            $rec = (float) $r;
            $dep = (float) $dpe;
            $depo = (float) $dpo;
            $solde = $rec - $dep - $depo;
            $lignes = array(
                array('recettes' => $rec, 'depenses' => $dep, 'depots' => $depo, 'solde' => $solde),
            );
            return array(
                'titre' => 'ETATS DU ' . $days,
                'lignes' => $lignes,
                'total' => $solde,
                'filters_qs' => '',
                {RETOUR}
                'columns' => array(
                    array('key' => 'recettes', 'label' => 'Recettes', 'align' => 'right', 'money' => true),
                    array('key' => 'depenses', 'label' => 'Dépenses', 'align' => 'right', 'money' => true),
                    array('key' => 'depots', 'label' => 'Dépôts', 'align' => 'right', 'money' => true),
                    array('key' => 'solde', 'label' => 'Solde', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/reportsolde_export/' . rawurlencode($ckey) . '/' . rawurlencode($iuser) . '/' . rawurlencode($r) . '/' . rawurlencode($dpe) . '/' . rawurlencode($dpo) . '/' . rawurlencode($d)),
            );
        }}
{pub('reportsolde', 'États solde', '$ckey, $iuser, $r, $dpe, $dpo, $d')}
'''

code_solde = f'''
        protected function _solde_payload($ckey, $iuser, $dpo, $d)
        {{
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            list($days, ) = $this->_recap_title_dates($d, $d);
            $mt = (float) $dpo;
            $lignes = array(array('solde' => $mt));
            return array(
                'titre' => 'ETATS DU ' . $days,
                'lignes' => $lignes,
                'total' => $mt,
                'filters_qs' => '',
                {RETOUR}
                'columns' => array(
                    array('key' => 'solde', 'label' => 'Solde', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/solde_export/' . rawurlencode($ckey) . '/' . rawurlencode($iuser) . '/' . rawurlencode($dpo) . '/' . rawurlencode($d)),
            );
        }}
{pub('solde', 'Solde', '$ckey, $iuser, $dpo, $d')}
'''

TYC_LABEL = '''
            $ty = 'PLIS';
            $ty2 = 'COLIS';
            if ($tyc === 'Gros_plis') {
                $ty3 = $ty2;
            } elseif ($tyc === 'Petit_plis') {
                $ty3 = $ty;
            } else {
                $ty3 = $ty . '/' . $ty2;
            }
'''

code_exoscourrieresc = f'''
        protected function _exoscourrieresc_payload($ckey, $g)
        {{
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebutcrexesc'));
            $dt2 = trim((string) $this->input->get_post('datefincrexesc'));
            $lign = trim((string) $this->input->get_post('axelignecrexesc'));
            $comp = trim((string) $this->input->get_post('_compagcrexesc'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgarcrexesc'));
            if ($gid === '') {{
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }}
            $tyc = trim((string) $this->input->get_post('typcoursexesc'));
            $ncgd = $this->m_gare_depart->getn($gid);
            $gar = ($ncgd && isset($ncgd->nom_gaep)) ? $ncgd->nom_gaep : $gid;
            {TYC_LABEL}
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            $rows = $this->m_courrier_expedieresc->expetatspliexo($this->entreprise->ekey, $dt1, $dt2, $comp, $gid, $tyc, $lign);
            if (!is_array($rows)) {{
                $rows = array();
            }}
            $lignes = array();
            $total = 0.0;
            foreach ($rows as $crr) {{
                $mt = isset($crr->prixcolisesc) ? (float) $crr->prixcolisesc : 0.0;
                $lignes[] = array(
                    'code' => isset($crr->num_couresc) ? (string) $crr->num_couresc : '',
                    'client' => trim((isset($crr->nom_client) ? (string) $crr->nom_client : '') . ' ' . (isset($crr->prenom_client) ? (string) $crr->prenom_client : '')),
                    'contact' => isset($crr->contact_client) ? (string) $crr->contact_client : '',
                    'prix' => $mt,
                );
                $total += $mt;
            }}
            $qs = http_build_query(array_filter(array(
                'datedebutcrexesc' => $dt1, 'datefincrexesc' => $dt2, 'axelignecrexesc' => $lign,
                '_compagcrexesc' => $comp, 'departgarcrexesc' => $gid, 'typcoursexesc' => $tyc,
                {QS}
            )));
            return array(
                'titre' => 'EXERCICE LISTE ESCAL ' . $ty3 . ' ' . $gar . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes, 'total' => $total, 'filters_qs' => $qs, {RETOUR}
                'columns' => array(
                    array('key' => 'code', 'label' => 'Code', 'align' => 'left'),
                    array('key' => 'client', 'label' => 'Nom / prénom', 'align' => 'left'),
                    array('key' => 'contact', 'label' => 'Contact', 'align' => 'left'),
                    array('key' => 'prix', 'label' => 'Prix', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/exoscourrieresc_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }}
{pub('exoscourrieresc', 'Exercice liste courrier escal', '$ckey, $g')}
'''

code_courrierglobesc = f'''
        protected function _courrierglobesc_payload($ckey, $g)
        {{
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebutcrglbesc'));
            $dt2 = trim((string) $this->input->get_post('datefincrglbesc'));
            $lign = trim((string) $this->input->get_post('axelignecrglbesc'));
            $comp = trim((string) $this->input->get_post('_compagcrglbesc'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgarcrglbesc'));
            if ($gid === '') {{
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }}
            $tyc = trim((string) $this->input->get_post('typcoursglbesc'));
            {TYC_LABEL}
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            $rows = $this->m_courrier_expedieresc->expetatspliglob($this->entreprise->ekey, $dt1, $dt2, $gid, $comp, $tyc, $lign);
            if (!is_array($rows)) {{
                $rows = array();
            }}
            $lignes = array();
            $total = 0.0;
            foreach ($rows as $crr) {{
                $mt = isset($crr->prixcolisesc) ? (float) $crr->prixcolisesc : (isset($crr->prixcolis) ? (float) $crr->prixcolis : 0.0);
                $lignes[] = array(
                    'code' => isset($crr->num_couresc) ? (string) $crr->num_couresc : (isset($crr->num_cour) ? (string) $crr->num_cour : ''),
                    'client' => trim((isset($crr->nom_client) ? (string) $crr->nom_client : '') . ' ' . (isset($crr->prenom_client) ? (string) $crr->prenom_client : '')),
                    'contact' => isset($crr->contact_client) ? (string) $crr->contact_client : '',
                    'prix' => $mt,
                );
                $total += $mt;
            }}
            $qs = http_build_query(array_filter(array(
                'datedebutcrglbesc' => $dt1, 'datefincrglbesc' => $dt2, 'axelignecrglbesc' => $lign,
                '_compagcrglbesc' => $comp, 'departgarcrglbesc' => $gid, 'typcoursglbesc' => $tyc,
                {QS}
            )));
            return array(
                'titre' => 'LISTE GLOBALE ESCAL ' . $ty3 . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes, 'total' => $total, 'filters_qs' => $qs, {RETOUR}
                'columns' => array(
                    array('key' => 'code', 'label' => 'Code', 'align' => 'left'),
                    array('key' => 'client', 'label' => 'Nom / prénom', 'align' => 'left'),
                    array('key' => 'contact', 'label' => 'Contact', 'align' => 'left'),
                    array('key' => 'prix', 'label' => 'Prix', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/courrierglobesc_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }}
{pub('courrierglobesc', 'Liste globale courrier escal', '$ckey, $g')}
'''

code_verse = f'''
        protected function _verse_payload($ckey)
        {{
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebut'));
            $dt2 = trim((string) $this->input->get_post('datefin'));
            $ver = trim((string) $this->input->get_post('type'));
            $nm = trim((string) $this->input->get_post('nom'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgar'));
            $comp = trim((string) $this->input->get_post('_compag'));
            $this->_assert_cashbox_recap_filters($this->entreprise->ekey, $dt1, $dt2, $comp, $gid);
            $consultedCashbox = $this->_secured_consulted_cashbox_operator($this->entreprise->ekey);
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            $uopera = trim((string) $this->input->get_post('useropered'));
            if ($consultedCashbox !== null) {{
                $uopera = $consultedCashbox;
            }}
            $cai = $this->m_compte_user->cpuseres($uopera);
            $caiLabel = ($cai && isset($cai->first_name)) ? trim($cai->first_name . ' ' . $cai->last_name) : '';
            $ngrd = $this->m_gare_depart->getno($gid);
            $garNom = ($ngrd && isset($ngrd->garenom)) ? $ngrd->garenom : $gid;
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            $role = isset($this->session->agent->userole) ? (string) $this->session->agent->userole : '';
            if ($consultedCashbox !== null) {{
                $trivers = $this->m_versements->valiget($this->entreprise->ekey, $gid, $uopera, $dt1, $dt2, $comp, $ver, $nm);
            }} elseif ($role === '1' || $role === '2') {{
                $trivers = $this->m_versements->valigetadmin($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $ver, $nm);
            }} else {{
                $trivers = $this->m_versements->valiget($this->entreprise->ekey, $gid, $uopera, $dt1, $dt2, $comp, $ver, $nm);
            }}
            if (!is_array($trivers)) {{
                $trivers = array();
            }}
            $lignes = array();
            $total = 0.0;
            foreach ($trivers as $lement) {{
                $mt = isset($lement->montant_verser) ? (float) $lement->montant_verser : 0.0;
                $lignes[] = array(
                    'date' => isset($lement->date_versement) ? (string) $lement->date_versement : '',
                    'nom' => isset($lement->nom_beneficiaire) ? (string) $lement->nom_beneficiaire : '',
                    'type' => isset($lement->type_versement) ? (string) $lement->type_versement : '',
                    'montant' => $mt,
                );
                $total += $mt;
            }}
            $qs = http_build_query(array_filter(array(
                'datedebut' => $dt1, 'datefin' => $dt2, 'type' => $ver, 'nom' => $nm,
                'departgar' => $gid, '_compag' => $comp, 'useropered' => $uopera,
                {QS}
            )));
            $titre = 'ETATS DES VERSEMENTS ' . $cieNom . ' ' . $garNom . ' DU ' . $days . ' AU ' . $days1;
            if ($caiLabel !== '') {{
                $titre .= ' — CAISSE DE ' . $caiLabel;
            }}
            return array(
                'titre' => $titre,
                'lignes' => $lignes, 'total' => $total, 'filters_qs' => $qs, {RETOUR}
                'columns' => array(
                    array('key' => 'date', 'label' => 'Date', 'align' => 'left'),
                    array('key' => 'nom', 'label' => 'Nom', 'align' => 'left'),
                    array('key' => 'type', 'label' => 'Type', 'align' => 'left'),
                    array('key' => 'montant', 'label' => 'Montant', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/verse_export/' . rawurlencode($ckey)),
            );
        }}
{pub('verse', 'États des versements')}
'''

COLS_FICHE = """'columns' => array(
                    array('key' => 'date', 'label' => 'Date validation', 'align' => 'left'),
                    array('key' => 'ligne', 'label' => 'Ligne', 'align' => 'left'),
                    array('key' => 'montant', 'label' => 'Montant', 'align' => 'right', 'money' => true),
                ),"""

code_ficheinventaire = f'''
        protected function _ficheinventaire_payload($ckey, $gd, $usename, $cpid)
        {{
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $ddbt = trim((string) $this->input->get_post('dated'));
            $dfin = trim((string) $this->input->get_post('datef'));
            $comp = trim((string) $this->input->get_post('_compag'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgar'));
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            list($days, $days1) = $this->_recap_title_dates($ddbt, $dfin);
            $triversements = $this->m_passager->versfiltre($this->entreprise->ekey, $gid, $ddbt, $dfin, $comp, $cpid);
            $triversenonp = $this->m_non_passager->versefiltr($this->entreprise->ekey, $gid, $ddbt, $dfin, $comp, $cpid);
            if (!is_array($triversements)) {{
                $triversements = array();
            }}
            if (!is_array($triversenonp)) {{
                $triversenonp = array();
            }}
            $lignes = array();
            $total = 0.0;
            foreach ($triversements as $item) {{
                $mt = isset($item->total) ? (float) $item->total : 0.0;
                $lignes[] = array(
                    'date' => isset($item->datep_create) ? (string) $item->datep_create : '',
                    'ligne' => isset($item->nom_ligne) ? (string) $item->nom_ligne : '',
                    'montant' => $mt,
                );
                $total += $mt;
            }}
            foreach ($triversenonp as $triversen) {{
                $mt = isset($triversen->totalr) ? (float) $triversen->totalr : 0.0;
                $lignes[] = array(
                    'date' => isset($triversen->datevente) ? (string) $triversen->datevente : '',
                    'ligne' => $this->_recap_invert_ligne_nom(isset($triversen->nom_ligne) ? $triversen->nom_ligne : ''),
                    'montant' => $mt,
                );
                $total += $mt;
            }}
            $qs = http_build_query(array_filter(array(
                'dated' => $ddbt, 'datef' => $dfin, '_compag' => $comp, 'departgar' => $gid,
                {QS}
            )));
            return array(
                'titre' => 'FICHES D\\'INVENTAIRE ' . $cieNom . ' DU ' . $days . ' AU ' . $days1 . ' — ' . $usename . ', ' . $gd,
                'lignes' => $lignes, 'total' => $total, 'filters_qs' => $qs, {RETOUR}
                {COLS_FICHE}
                'export_base' => site_url('Rapport/ficheinventaire_export/' . rawurlencode($ckey) . '/' . rawurlencode($gd) . '/' . rawurlencode($usename) . '/' . rawurlencode($cpid)),
            );
        }}
{pub('ficheinventaire', 'Fiche inventaire', '$ckey, $gd, $usename, $cpid')}
'''

code_fiches = f'''
        protected function _fiches_payload($ckey, $gd, $usename, $cpid)
        {{
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $ddbt = trim((string) $this->input->get_post('dated'));
            $dfin = trim((string) $this->input->get_post('datef'));
            $comp = trim((string) $this->input->get_post('_compag'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgar'));
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            list($days, $days1) = $this->_recap_title_dates($ddbt, $dfin);
            $rows = $this->m_bagage->filtrebag($this->entreprise->ekey, $gid, $ddbt, $dfin, $comp, $cpid);
            if (!is_array($rows)) {{
                $rows = array();
            }}
            $lignes = array();
            $total = 0.0;
            foreach ($rows as $item) {{
                $mt = isset($item->bagtotal) ? (float) $item->bagtotal : 0.0;
                $lignes[] = array(
                    'date' => isset($item->date_create) ? (string) $item->date_create : '',
                    'ligne' => isset($item->nom_ligne) ? (string) $item->nom_ligne : '',
                    'montant' => $mt,
                );
                $total += $mt;
            }}
            $qs = http_build_query(array_filter(array(
                'dated' => $ddbt, 'datef' => $dfin, '_compag' => $comp, 'departgar' => $gid,
                {QS}
            )));
            return array(
                'titre' => 'FICHES BAGAGES ' . $cieNom . ' DU ' . $days . ' AU ' . $days1 . ' — ' . $usename . ', ' . $gd,
                'lignes' => $lignes, 'total' => $total, 'filters_qs' => $qs, {RETOUR}
                {COLS_FICHE}
                'export_base' => site_url('Rapport/fiches_export/' . rawurlencode($ckey) . '/' . rawurlencode($gd) . '/' . rawurlencode($usename) . '/' . rawurlencode($cpid)),
            );
        }}
{pub('fiches', 'Fiches bagages', '$ckey, $gd, $usename, $cpid')}
'''

code_ficheinventaireesc = f'''
        protected function _ficheinventaireesc_payload($ckey, $gd, $usename, $cpid)
        {{
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $ddbt = trim((string) $this->input->get_post('dated'));
            $dfin = trim((string) $this->input->get_post('datef'));
            $comp = trim((string) $this->input->get_post('_compag'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgar'));
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            list($days, $days1) = $this->_recap_title_dates($ddbt, $dfin);
            $rows = $this->m_escalclients->versfiltre($this->entreprise->ekey, $gid, $ddbt, $dfin, $comp, $cpid);
            if (!is_array($rows)) {{
                $rows = array();
            }}
            $lignes = array();
            $total = 0.0;
            foreach ($rows as $item) {{
                $mt = isset($item->total) ? (float) $item->total : 0.0;
                $lignes[] = array(
                    'date' => isset($item->dateescal) ? (string) $item->dateescal : '',
                    'ligne' => isset($item->nom_ligne) ? (string) $item->nom_ligne : '',
                    'montant' => $mt,
                );
                $total += $mt;
            }}
            $qs = http_build_query(array_filter(array(
                'dated' => $ddbt, 'datef' => $dfin, '_compag' => $comp, 'departgar' => $gid,
                {QS}
            )));
            return array(
                'titre' => 'FICHES D\\'INVENTAIRE ESCAL ' . $cieNom . ' DU ' . $days . ' AU ' . $days1 . ' — ' . $usename . ', ' . $gd,
                'lignes' => $lignes, 'total' => $total, 'filters_qs' => $qs, {RETOUR}
                {COLS_FICHE}
                'export_base' => site_url('Rapport/ficheinventaireesc_export/' . rawurlencode($ckey) . '/' . rawurlencode($gd) . '/' . rawurlencode($usename) . '/' . rawurlencode($cpid)),
            );
        }}
{pub('ficheinventaireesc', 'Fiche inventaire escal', '$ckey, $gd, $usename, $cpid')}
'''

COLS_TRI_REC = """'columns' => array(
                    array('key' => 'date', 'label' => 'Date', 'align' => 'left'),
                    array('key' => 'type', 'label' => 'Type', 'align' => 'left'),
                    array('key' => 'operateur', 'label' => 'Opérateur', 'align' => 'left'),
                    array('key' => 'nom', 'label' => 'Nom', 'align' => 'left'),
                    array('key' => 'montant', 'label' => 'Total', 'align' => 'right', 'money' => true),
                    array('key' => 'commentaire', 'label' => 'Commentaire', 'align' => 'left'),
                ),"""

COLS_TRI_DEP = """'columns' => array(
                    array('key' => 'date', 'label' => 'Date', 'align' => 'left'),
                    array('key' => 'type', 'label' => 'Type', 'align' => 'left'),
                    array('key' => 'operateur', 'label' => 'Opérateur', 'align' => 'left'),
                    array('key' => 'nom', 'label' => 'Nom', 'align' => 'left'),
                    array('key' => 'montant', 'label' => 'Total', 'align' => 'right', 'money' => true),
                    array('key' => 'commentaire', 'label' => 'Commentaire', 'align' => 'left'),
                    array('key' => 'motif', 'label' => 'Motif', 'align' => 'left'),
                ),"""

code_recettetris = f'''
        protected function _recettetris_payload($ckey, $g, $cai, $us)
        {{
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $date1 = trim((string) $this->input->get_post('debutdate'));
            $date2 = trim((string) $this->input->get_post('findate'));
            $typ = trim((string) $this->input->get_post('typerecette'));
            $comp = trim((string) $this->input->get_post('_compag'));
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            list($days, $days1) = $this->_recap_title_dates($date1, $date2);
            $rows = $this->m_recette->trisrecet($this->entreprise->ekey, $g, $cai, $us, $date1, $date2, $comp, $typ);
            if (!is_array($rows)) {{
                $rows = array();
            }}
            $lignes = array();
            $total = 0.0;
            foreach ($rows as $recte) {{
                $mt = isset($recte->montant_recet) ? (float) $recte->montant_recet : 0.0;
                $lignes[] = array(
                    'date' => isset($recte->date_recet) ? (string) $recte->date_recet : '',
                    'type' => isset($recte->type_recet) ? (string) $recte->type_recet : '',
                    'operateur' => isset($recte->username) ? (string) $recte->username : '',
                    'nom' => isset($recte->nom) ? (string) $recte->nom : '',
                    'montant' => $mt,
                    'commentaire' => isset($recte->commentaire_recet) ? (string) $recte->commentaire_recet : '',
                );
                $total += $mt;
            }}
            $qs = http_build_query(array_filter(array(
                'debutdate' => $date1, 'findate' => $date2, 'typerecette' => $typ, '_compag' => $comp,
                {QS}
            )));
            return array(
                'titre' => 'ETATS DES RECETTES DE ' . $cieNom . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes, 'total' => $total, 'filters_qs' => $qs, {RETOUR}
                {COLS_TRI_REC}
                'export_base' => site_url('Rapport/recettetris_export/' . rawurlencode($ckey) . '/' . rawurlencode($g) . '/' . rawurlencode($cai) . '/' . rawurlencode($us)),
            );
        }}
{pub('recettetris', 'États recettes (tri)', '$ckey, $g, $cai, $us')}
'''

code_depensetris = f'''
        protected function _depensetris_payload($ckey, $g, $cai, $us)
        {{
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $date1 = trim((string) $this->input->get_post('debutdate'));
            $date2 = trim((string) $this->input->get_post('findate'));
            $typ = trim((string) $this->input->get_post('typedepense'));
            $comp = trim((string) $this->input->get_post('_compag'));
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            $profil = $this->db->query("SELECT userole FROM attributions_role WHERE roleattribut = ? LIMIT 1", array($us))->row();
            $userole = ($profil && isset($profil->userole)) ? $profil->userole : '';
            list($days, $days1) = $this->_recap_title_dates($date1, $date2);
            $rows = $this->m_depense->trisdepens_par_profil($this->entreprise->ekey, $g, $cai, $us, $userole, $date1, $date2, $comp, $typ);
            if (!is_array($rows)) {{
                $rows = array();
            }}
            $lignes = array();
            $total = 0.0;
            foreach ($rows as $dep) {{
                $mt = isset($dep->montant_depens) ? (float) $dep->montant_depens : 0.0;
                $lignes[] = array(
                    'date' => isset($dep->date_depens) ? (string) $dep->date_depens : '',
                    'type' => isset($dep->type_depense) ? (string) $dep->type_depense : '',
                    'operateur' => isset($dep->username) ? (string) $dep->username : '',
                    'nom' => isset($dep->nom_perso) ? (string) $dep->nom_perso : '',
                    'montant' => $mt,
                    'commentaire' => isset($dep->commentaire) ? (string) $dep->commentaire : '',
                    'motif' => isset($dep->motif) ? (string) $dep->motif : '',
                );
                $total += $mt;
            }}
            $qs = http_build_query(array_filter(array(
                'debutdate' => $date1, 'findate' => $date2, 'typedepense' => $typ, '_compag' => $comp,
                {QS}
            )));
            return array(
                'titre' => 'ETATS DES DEPENSES DE ' . $cieNom . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes, 'total' => $total, 'filters_qs' => $qs, {RETOUR}
                {COLS_TRI_DEP}
                'export_base' => site_url('Rapport/depensetris_export/' . rawurlencode($ckey) . '/' . rawurlencode($g) . '/' . rawurlencode($cai) . '/' . rawurlencode($us)),
            );
        }}
{pub('depensetris', 'États dépenses (tri)', '$ckey, $g, $cai, $us')}
'''

code_depottris = f'''
        protected function _depottris_payload($ckey, $g, $cai, $us)
        {{
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $date1 = trim((string) $this->input->get_post('debutdate'));
            $date2 = trim((string) $this->input->get_post('findate'));
            $typ = trim((string) $this->input->get_post('typedepot'));
            $comp = trim((string) $this->input->get_post('_compag'));
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            $profil = $this->db->query("SELECT userole FROM attributions_role WHERE roleattribut = ? LIMIT 1", array($us))->row();
            $userole = ($profil && isset($profil->userole)) ? $profil->userole : '';
            list($days, $days1) = $this->_recap_title_dates($date1, $date2);
            $rows = $this->m_depot->trisdepot_par_profil($this->entreprise->ekey, $g, $cai, $us, $userole, $date1, $date2, $comp, $typ);
            if (!is_array($rows)) {{
                $rows = array();
            }}
            $lignes = array();
            $total = 0.0;
            foreach ($rows as $depot) {{
                $mt = isset($depot->montant_depot) ? (float) $depot->montant_depot : 0.0;
                $lignes[] = array(
                    'date' => isset($depot->datedepot) ? (string) $depot->datedepot : '',
                    'type' => isset($depot->type_depot) ? (string) $depot->type_depot : '',
                    'operateur' => isset($depot->username) ? (string) $depot->username : '',
                    'nom' => isset($depot->nom_pre) ? (string) $depot->nom_pre : '',
                    'commentaire' => isset($depot->commentaire_depot) ? (string) $depot->commentaire_depot : '',
                    'montant' => $mt,
                );
                $total += $mt;
            }}
            $qs = http_build_query(array_filter(array(
                'debutdate' => $date1, 'findate' => $date2, 'typedepot' => $typ, '_compag' => $comp,
                {QS}
            )));
            return array(
                'titre' => 'ETATS DES DEPOTS DE ' . $cieNom . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes, 'total' => $total, 'filters_qs' => $qs, {RETOUR}
                'columns' => array(
                    array('key' => 'date', 'label' => 'Date', 'align' => 'left'),
                    array('key' => 'type', 'label' => 'Type', 'align' => 'left'),
                    array('key' => 'operateur', 'label' => 'Opérateur', 'align' => 'left'),
                    array('key' => 'nom', 'label' => 'Nom', 'align' => 'left'),
                    array('key' => 'commentaire', 'label' => 'Commentaire', 'align' => 'left'),
                    array('key' => 'montant', 'label' => 'Total', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/depottris_export/' . rawurlencode($ckey) . '/' . rawurlencode($g) . '/' . rawurlencode($cai) . '/' . rawurlencode($us)),
            );
        }}
{pub('depottris', 'États dépôts (tri)', '$ckey, $g, $cai, $us')}
'''

code_recettetries = f'''
        protected function _recettetries_payload($ckey, $g, $cai)
        {{
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $date1 = trim((string) $this->input->get_post('debutdate'));
            $date2 = trim((string) $this->input->get_post('findate'));
            $typ = trim((string) $this->input->get_post('typerecette'));
            $us = trim((string) $this->input->get_post('opera'));
            $comp = trim((string) $this->input->get_post('_compag'));
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            list($days, $days1) = $this->_recap_title_dates($date1, $date2);
            $rows = $this->m_recette->trisrecet($this->entreprise->ekey, $g, $comp, $cai, $us, $date1, $date2, $typ);
            if (!is_array($rows)) {{
                $rows = array();
            }}
            $lignes = array();
            $total = 0.0;
            foreach ($rows as $recte) {{
                $mt = isset($recte->montant_recet) ? (float) $recte->montant_recet : 0.0;
                $lignes[] = array(
                    'date' => isset($recte->date_recet) ? (string) $recte->date_recet : '',
                    'type' => isset($recte->type_recet) ? (string) $recte->type_recet : '',
                    'operateur' => isset($recte->username) ? (string) $recte->username : '',
                    'nom' => isset($recte->nom) ? (string) $recte->nom : '',
                    'commentaire' => isset($recte->commentaire_recet) ? (string) $recte->commentaire_recet : '',
                    'montant' => $mt,
                );
                $total += $mt;
            }}
            $qs = http_build_query(array_filter(array(
                'debutdate' => $date1, 'findate' => $date2, 'typerecette' => $typ, 'opera' => $us, '_compag' => $comp,
                {QS}
            )));
            return array(
                'titre' => 'ETATS DES RECETTES DE ' . $cieNom . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes, 'total' => $total, 'filters_qs' => $qs, {RETOUR}
                {COLS_TRI_REC}
                'export_base' => site_url('Rapport/recettetries_export/' . rawurlencode($ckey) . '/' . rawurlencode($g) . '/' . rawurlencode($cai)),
            );
        }}
{pub('recettetries', 'États recettes (tries)', '$ckey, $g, $cai')}
'''

code_depensetries = f'''
        protected function _depensetries_payload($ckey, $g, $cai)
        {{
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $date1 = trim((string) $this->input->get_post('debutdate'));
            $date2 = trim((string) $this->input->get_post('findate'));
            $typ = trim((string) $this->input->get_post('typedepense'));
            $us = trim((string) $this->input->get_post('opera'));
            $comp = trim((string) $this->input->get_post('_compag'));
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            list($days, $days1) = $this->_recap_title_dates($date1, $date2);
            $rows = $this->m_depense->trisdepens($this->entreprise->ekey, $g, $cai, $us, $date1, $date2, $comp, $typ);
            if (!is_array($rows)) {{
                $rows = array();
            }}
            $lignes = array();
            $total = 0.0;
            foreach ($rows as $dep) {{
                $mt = isset($dep->montant_depens) ? (float) $dep->montant_depens : 0.0;
                $lignes[] = array(
                    'date' => isset($dep->date_depens) ? (string) $dep->date_depens : '',
                    'type' => isset($dep->type_depense) ? (string) $dep->type_depense : '',
                    'operateur' => isset($dep->username) ? (string) $dep->username : '',
                    'nom' => isset($dep->nom_perso) ? (string) $dep->nom_perso : '',
                    'commentaire' => isset($dep->commentaire) ? (string) $dep->commentaire : '',
                    'motif' => isset($dep->motif) ? (string) $dep->motif : '',
                    'montant' => $mt,
                );
                $total += $mt;
            }}
            $qs = http_build_query(array_filter(array(
                'debutdate' => $date1, 'findate' => $date2, 'typedepense' => $typ, 'opera' => $us, '_compag' => $comp,
                {QS}
            )));
            return array(
                'titre' => 'ETATS DES DEPENSES DE ' . $cieNom . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes, 'total' => $total, 'filters_qs' => $qs, {RETOUR}
                {COLS_TRI_DEP}
                'export_base' => site_url('Rapport/depensetries_export/' . rawurlencode($ckey) . '/' . rawurlencode($g) . '/' . rawurlencode($cai)),
            );
        }}
{pub('depensetries', 'États dépenses (tries)', '$ckey, $g, $cai')}
'''

code_depottries = f'''
        protected function _depottries_payload($ckey, $g, $cai)
        {{
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $date1 = trim((string) $this->input->get_post('debutdate'));
            $date2 = trim((string) $this->input->get_post('findate'));
            $typ = trim((string) $this->input->get_post('typedepot'));
            $us = trim((string) $this->input->get_post('opera'));
            $comp = trim((string) $this->input->get_post('_compag'));
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            list($days, $days1) = $this->_recap_title_dates($date1, $date2);
            $rows = $this->m_depot->trisdepot($this->entreprise->ekey, $g, $comp, $cai, $us, $date1, $date2, $typ);
            if (!is_array($rows)) {{
                $rows = array();
            }}
            $lignes = array();
            $total = 0.0;
            foreach ($rows as $depot) {{
                $mt = isset($depot->montant_depot) ? (float) $depot->montant_depot : 0.0;
                $lignes[] = array(
                    'date' => isset($depot->datedepot) ? (string) $depot->datedepot : '',
                    'type' => isset($depot->type_depot) ? (string) $depot->type_depot : '',
                    'operateur' => isset($depot->username) ? (string) $depot->username : '',
                    'nom' => isset($depot->nom_pre) ? (string) $depot->nom_pre : '',
                    'commentaire' => isset($depot->commentaire_depot) ? (string) $depot->commentaire_depot : '',
                    'montant' => $mt,
                );
                $total += $mt;
            }}
            $qs = http_build_query(array_filter(array(
                'debutdate' => $date1, 'findate' => $date2, 'typedepot' => $typ, 'opera' => $us, '_compag' => $comp,
                {QS}
            )));
            return array(
                'titre' => 'ETATS DES DEPOTS DE ' . $cieNom . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes, 'total' => $total, 'filters_qs' => $qs, {RETOUR}
                'columns' => array(
                    array('key' => 'date', 'label' => 'Date', 'align' => 'left'),
                    array('key' => 'type', 'label' => 'Type', 'align' => 'left'),
                    array('key' => 'operateur', 'label' => 'Opérateur', 'align' => 'left'),
                    array('key' => 'nom', 'label' => 'Nom', 'align' => 'left'),
                    array('key' => 'commentaire', 'label' => 'Commentaire', 'align' => 'left'),
                    array('key' => 'montant', 'label' => 'Total', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/depottries_export/' . rawurlencode($ckey) . '/' . rawurlencode($g) . '/' . rawurlencode($cai)),
            );
        }}
{pub('depottries', 'États dépôts (tries)', '$ckey, $g, $cai')}
'''

code_bon = f'''
        protected function _bon_payload($ckey)
        {{
            $db = trim((string) $this->input->get_post('debutdate'));
            $df = trim((string) $this->input->get_post('findate'));
            $gd = trim((string) $this->input->get_post('stop'));
            $sg = trim((string) $this->input->get_post('sousgd'));
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $onbon = $this->m_bon_millitaire->voirliste($this->entreprise->ekey, $db, $df, $gd, $sg);
            if (!is_array($onbon)) {{
                $onbon = array();
            }}
            $lignes = array();
            $total = 0.0;
            foreach ($onbon as $element) {{
                $cinb = isset($element->num_CNIB) ? (string) $element->num_CNIB : '';
                if (!empty($element->date_delivre)) {{
                    $cinb .= ' ' . date('d/m/Y', strtotime($element->date_delivre));
                }}
                $lignes[] = array(
                    'date' => isset($element->date_bon) ? (string) $element->date_bon : '',
                    'num' => isset($element->bonsecondid) ? (string) $element->bonsecondid : '',
                    'code' => isset($element->code_bon) ? (string) $element->code_bon : '',
                    'trajet' => trim((isset($element->nom_gaep) ? (string) $element->nom_gaep : '') . '-' . (isset($element->nom_gadest) ? (string) $element->nom_gadest : '')),
                    'client' => trim((isset($element->nom_client) ? (string) $element->nom_client : '') . ' ' . (isset($element->prenom_client) ? (string) $element->prenom_client : '')),
                    'contact' => isset($element->contact_client) ? (string) $element->contact_client : '',
                    'cinb' => $cinb,
                );
                $total += 1;
            }}
            $qs = http_build_query(array_filter(array(
                'debutdate' => $db, 'findate' => $df, 'stop' => $gd, 'sousgd' => $sg,
                {QS}
            )));
            return array(
                'titre' => 'LISTE DES BONS',
                'lignes' => $lignes, 'total' => $total, 'filters_qs' => $qs, {RETOUR}
                'columns' => array(
                    array('key' => 'date', 'label' => 'Date', 'align' => 'left'),
                    array('key' => 'num', 'label' => 'N° bon', 'align' => 'left'),
                    array('key' => 'code', 'label' => 'Code bon', 'align' => 'left'),
                    array('key' => 'trajet', 'label' => 'Trajet', 'align' => 'left'),
                    array('key' => 'client', 'label' => 'Nom et prénom', 'align' => 'left'),
                    array('key' => 'contact', 'label' => 'Contact', 'align' => 'left'),
                    array('key' => 'cinb', 'label' => 'Réf CNIB', 'align' => 'left'),
                ),
                'export_base' => site_url('Rapport/bon_export/' . rawurlencode($ckey)),
            );
        }}
{pub('bon', 'Liste des bons')}
'''

# listesbagages with mutation flag
code_listesbagages = r'''
        protected function _listesbagages_payload($ckey, $doUpdate = true)
        {
            $cdbord = trim((string) $this->input->get_post('courschauffeurbg'));
            $cprgbord = trim((string) $this->input->get_post('courdeptprograbg'));
            $cvbord = trim((string) $this->input->get_post('courconvoibg'));
            $dabord = trim((string) $this->input->get_post('courborddeptdateenbg'));
            $lignebord = trim((string) $this->input->get_post('deptscourlignebg'));
            $usenam = trim((string) $this->input->get_post('usernames'));
            $nam = $this->m_compte_user->for($usenam);
            $lignequart = trim((string) $this->input->get_post('courdeptquartierbg'));
            $gd = trim((string) $this->input->get_post('gareattribuer'));
            $sgd = trim((string) $this->input->get_post('sousgareconnect'));
            $iduser = trim((string) $this->input->get_post('usernameconect'));
            $itinerairesg = $this->db->query(
                "SELECT sg.nomsousgare, sg.idsousgare FROM sousgare sg WHERE sg.idsousgare = ?",
                array($sgd)
            )->row();
            $sgNom = ($itinerairesg && isset($itinerairesg->nomsousgare)) ? $itinerairesg->nomsousgare : '';

            $ligne_lhbord = strpos($lignebord, '/');
            if ($ligne_lhbord === false) {
                $lignehbord = $lignebord;
                $lignelhrebord = $lignebord;
            } else {
                $lignehbord = substr($lignebord, 0, $ligne_lhbord);
                $lignelhrebord = substr($lignebord, $ligne_lhbord + 1);
            }
            $ligne_lhbord1 = strpos($lignehbord, '-');
            $lignehbord1 = ($ligne_lhbord1 === false) ? $lignehbord : substr($lignehbord, 0, $ligne_lhbord1);

            $post_heurebord = strpos($cprgbord, '/');
            if ($post_heurebord === false) {
                $sub_heurebord = $cprgbord;
                $dprogbord = '';
            } else {
                $sub_heurebord = substr($cprgbord, 0, $post_heurebord);
                $dprogbord = substr($cprgbord, $post_heurebord + 1);
            }
            $post_heurebord1 = strpos($dprogbord, '/');
            if ($post_heurebord1 === false) {
                $sub_heurebord1 = $dprogbord;
                $dprogbord1 = '';
            } else {
                $sub_heurebord1 = substr($dprogbord, 0, $post_heurebord1);
                $dprogbord1 = substr($dprogbord, $post_heurebord1 + 1);
            }
            $post_heurebord2 = strpos($dprogbord1, '/');
            if ($post_heurebord2 === false) {
                $sub_heurebord2 = $dprogbord1;
                $dprogbord2 = '';
            } else {
                $sub_heurebord2 = substr($dprogbord1, 0, $post_heurebord2);
                $dprogbord2 = substr($dprogbord1, $post_heurebord2 + 1);
            }

            $lignes = array();
            $total = 0.0;
            $titre = 'SUIVI BAGAGES';
            $numb = '';
            $agentLabel = ($nam && isset($nam->first_name)) ? trim($nam->first_name . ' ' . $nam->last_name) : '';

            if ($cdbord !== '' && $cprgbord !== '') {
                $this->entreprise = $this->m_entreprises->get_key($ckey);
                $role = isset($this->session->agent->userole) ? (string) $this->session->agent->userole : '';
                if ($role === '1' || $role === '2') {
                    $onbord = $this->m_envoibagages->listad1($this->entreprise->ekey, $sub_heurebord, $sub_heurebord2, $dabord, $lignequart);
                } else {
                    $onbord = $this->m_envoibagages->list1($this->entreprise->ekey, $gd, $sgd, $sub_heurebord, $sub_heurebord2, $dabord, $lignequart);
                }
                if (!is_array($onbord)) {
                    $onbord = array();
                }
                $onprogrambordbg = $this->m_bordereaubagage->get($this->entreprise->ekey, $gd, $sgd, $sub_heurebord, $dabord, $lignequart);
                $addtiragebordbg = array(
                    'idoperbordbag' => $iduser,
                    'idsousgdbordbag' => $sgd,
                    'programmebordbag' => $sub_heurebord,
                    'lignebordbag' => $lignehbord,
                    'quartierbordbag' => $lignequart,
                    'datebordbag' => $dabord,
                    'buschauffbordbag' => $cdbord,
                    'busconvoybordbag' => $cvbord,
                );
                if ($doUpdate) {
                    if ($onprogrambordbg === null) {
                        $numb = $this->m_bordereaubagage->create($addtiragebordbg);
                        $ln = $this->m_bordereaubagage->getnu($this->entreprise->ekey, $numb);
                    } else {
                        $this->m_bordereaubagage->update($onprogrambordbg->identbordbag, $addtiragebordbg);
                        $numb = $onprogrambordbg->identbordbag;
                        $ln = $this->m_bordereaubagage->getnu($this->entreprise->ekey, $numb);
                    }
                } else {
                    if ($onprogrambordbg !== null) {
                        $numb = $onprogrambordbg->identbordbag;
                        $ln = $this->m_bordereaubagage->getnu($this->entreprise->ekey, $numb);
                    } else {
                        $ln = null;
                    }
                }
                $ligneNom = ($ln && isset($ln->nom_ligne)) ? $ln->nom_ligne : '';
                $titre = 'SUIVI BAGAGES ' . $sgNom . ' ' . $ligneNom . ' ' . $lignequart;
                if ($numb !== '' && $numb !== null) {
                    $titre .= ' — N° BORDEREAU ' . $numb;
                }
                foreach ($onbord as $elementbord) {
                    $mt = isset($elementbord->prix_bagage) ? (float) $elementbord->prix_bagage : 0.0;
                    $gareArr = '';
                    if (!empty($elementbord->gidarrbag)) {
                        $ga = $this->m_gare_arrivee->g($elementbord->gidarrbag);
                        $gareArr = ($ga && isset($ga->nom_gaep)) ? $ga->nom_gaep : '';
                    }
                    $lignes[] = array(
                        'num' => isset($elementbord->identbagas) ? str_pad((string) $elementbord->identbagas, 3, '0', STR_PAD_LEFT) : '',
                        'code' => isset($elementbord->codebag) ? (string) $elementbord->codebag : '',
                        'designation' => trim((isset($elementbord->nombrebagageenv) ? (string) $elementbord->nombrebagageenv : '') . '/' . (isset($elementbord->nombrebagage) ? (string) $elementbord->nombrebagage : '') . ' ' . (isset($elementbord->contenubagageenv) ? (string) $elementbord->contenubagageenv : '')),
                        'destinataire' => trim((isset($elementbord->nom_client) ? (string) $elementbord->nom_client : '') . ' ' . (isset($elementbord->prenom_client) ? (string) $elementbord->prenom_client : '') . ' ' . (isset($elementbord->contact_client) ? (string) $elementbord->contact_client : '')),
                        'montant' => $mt,
                        'dest' => trim($gareArr . ' ' . (isset($elementbord->quartarr_bg) ? (string) $elementbord->quartarr_bg : '')),
                    );
                    $total += $mt;
                }
            }

            $qs = http_build_query(array_filter(array(
                'courschauffeurbg' => $cdbord,
                'courdeptprograbg' => $cprgbord,
                'courconvoibg' => $cvbord,
                'courborddeptdateenbg' => $dabord,
                'deptscourlignebg' => $lignebord,
                'usernames' => $usenam,
                'courdeptquartierbg' => $lignequart,
                'gareattribuer' => $gd,
                'sousgareconnect' => $sgd,
                'usernameconect' => $iduser,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
            )));
            if ($agentLabel !== '') {
                $titre .= ' — Agent ' . $agentLabel;
            }
            return array(
                'titre' => $titre,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    $sgd
                ),
                'columns' => array(
                    array('key' => 'num', 'label' => 'Num bagage', 'align' => 'left'),
                    array('key' => 'code', 'label' => 'Code', 'align' => 'left'),
                    array('key' => 'designation', 'label' => 'Quantité / désignation', 'align' => 'left'),
                    array('key' => 'destinataire', 'label' => 'Destinataire / contact', 'align' => 'left'),
                    array('key' => 'montant', 'label' => 'Montant', 'align' => 'right', 'money' => true),
                    array('key' => 'dest', 'label' => 'Dest. finale', 'align' => 'left'),
                ),
                'export_base' => site_url('Rapport/listesbagages_export/' . rawurlencode($ckey)),
            );
        }

        public function listesbagages($ckey)
        {
            return $this->_etat_render_view('Suivi bagages', $this->_listesbagages_payload($ckey, true));
        }

        public function listesbagages_export($ckey)
        {
            $this->_etat_export_dispatch($this->_listesbagages_payload($ckey, false));
        }
'''

code_reimpressionlistebag = f'''
        protected function _reimpressionlistebag_payload($ckey, $gd, $sgd, $sub_heurebord, $sub_heurebord2, $dabord, $lignequart)
        {{
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $itinerairesg = $this->db->query(
                "SELECT sg.nomsousgare, sg.idsousgare FROM sousgare sg WHERE sg.idsousgare = ?",
                array($sgd)
            )->row();
            $sgNom = ($itinerairesg && isset($itinerairesg->nomsousgare)) ? $itinerairesg->nomsousgare : '';
            $onbord = $this->m_envoibagages->list1($this->entreprise->ekey, $gd, $sgd, $sub_heurebord, $sub_heurebord2, $dabord, $lignequart);
            if (!is_array($onbord)) {{
                $onbord = array();
            }}
            $onprogrambordaxe = $this->m_bordereaubagage->get($this->entreprise->ekey, $gd, $sgd, $sub_heurebord, $dabord, $lignequart);
            $nam = ($onprogrambordaxe && isset($onprogrambordaxe->idoperbordbag))
                ? $this->m_compte_user->cpuseres($onprogrambordaxe->idoperbordbag)
                : null;
            $agentLabel = ($nam && isset($nam->first_name)) ? trim($nam->first_name . ' ' . $nam->last_name) : '';
            $ligneNom = ($onprogrambordaxe && isset($onprogrambordaxe->nom_ligne)) ? $onprogrambordaxe->nom_ligne : '';
            $numb = ($onprogrambordaxe && isset($onprogrambordaxe->identbordbag)) ? $onprogrambordaxe->identbordbag : '';
            $lignes = array();
            $total = 0.0;
            foreach ($onbord as $lementbord) {{
                $mt = isset($lementbord->prix_bagage) ? (float) $lementbord->prix_bagage : 0.0;
                $gareArr = '';
                if (!empty($lementbord->gidarrbag)) {{
                    $ga = $this->m_gare_arrivee->g($lementbord->gidarrbag);
                    $gareArr = ($ga && isset($ga->nom_gaep)) ? $ga->nom_gaep : '';
                }}
                $lignes[] = array(
                    'num' => isset($lementbord->identbagas) ? (string) $lementbord->identbagas : '',
                    'code' => isset($lementbord->codebag) ? (string) $lementbord->codebag : '',
                    'designation' => trim((isset($lementbord->nombrebagageenv) ? (string) $lementbord->nombrebagageenv : '') . '/' . (isset($lementbord->nombrebagage) ? (string) $lementbord->nombrebagage : '') . ' ' . (isset($lementbord->contenubagageenv) ? (string) $lementbord->contenubagageenv : '')),
                    'destinataire' => trim((isset($lementbord->nom_client) ? (string) $lementbord->nom_client : '') . ' ' . (isset($lementbord->prenom_client) ? (string) $lementbord->prenom_client : '') . ' ' . (isset($lementbord->contact_client) ? (string) $lementbord->contact_client : '')),
                    'montant' => $mt,
                    'dest' => trim($gareArr . ' ' . (isset($lementbord->quartarr_bg) ? (string) $lementbord->quartarr_bg : '')),
                );
                $total += $mt;
            }}
            $titre = 'SUIVI BAGAGES ' . $sgNom . ' ' . $ligneNom . ' ' . $lignequart;
            if ($numb !== '') {{
                $titre .= ' — N° BORDEREAU ' . $numb;
            }}
            if ($agentLabel !== '') {{
                $titre .= ' — Agent ' . $agentLabel;
            }}
            return array(
                'titre' => $titre,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => '',
                {RETOUR}
                'columns' => array(
                    array('key' => 'num', 'label' => 'Num bagage', 'align' => 'left'),
                    array('key' => 'code', 'label' => 'Code', 'align' => 'left'),
                    array('key' => 'designation', 'label' => 'Quantité / désignation', 'align' => 'left'),
                    array('key' => 'destinataire', 'label' => 'Destinataire / contact', 'align' => 'left'),
                    array('key' => 'montant', 'label' => 'Montant', 'align' => 'right', 'money' => true),
                    array('key' => 'dest', 'label' => 'Dest. finale', 'align' => 'left'),
                ),
                'export_base' => site_url('Rapport/reimpressionlistebag_export/' . rawurlencode($ckey) . '/' . rawurlencode($gd) . '/' . rawurlencode($sgd) . '/' . rawurlencode($sub_heurebord) . '/' . rawurlencode($sub_heurebord2) . '/' . rawurlencode($dabord) . '/' . rawurlencode($lignequart)),
            );
        }}
{pub('reimpressionlistebag', 'Réimpression liste bagages', '$ckey, $gd, $sgd, $sub_heurebord, $sub_heurebord2, $dabord, $lignequart')}
'''


for name, code in [
    ('reportsolde', code_reportsolde),
    ('solde', code_solde),
    ('exoscourrieresc', code_exoscourrieresc),
    ('courrierglobesc', code_courrierglobesc),
    ('verse', code_verse),
    ('ficheinventaire', code_ficheinventaire),
    ('fiches', code_fiches),
    ('ficheinventaireesc', code_ficheinventaireesc),
    ('recettetris', code_recettetris),
    ('depensetris', code_depensetris),
    ('depottris', code_depottris),
    ('recettetries', code_recettetries),
    ('depensetries', code_depensetries),
    ('depottries', code_depottries),
    ('bon', code_bon),
    ('listesbagages', code_listesbagages),
    ('reimpressionlistebag', code_reimpressionlistebag),
]:
    src = replace_method(src, name, code)
    print('OK', name)

path.write_text(src, encoding='utf-8')
print('Wrote', path)
