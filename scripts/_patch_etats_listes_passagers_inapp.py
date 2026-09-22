#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""Lot 3: listes passagers → in-app (Retour + export)."""
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


def pub(name, label, args='$ckey'):
    return f'''
        public function {name}({args})
        {{
            return $this->_etat_render_view('{label}', $this->_{name}_payload({args}));
        }}

        public function {name}_export({args})
        {{
            $this->_etat_export_dispatch($this->_{name}_payload({args}));
        }}
'''


SPLIT_LIGNE = '''
            $rawLign = trim((string) $this->input->get_post('{field}'));
            $sb1 = strpos($rawLign, '/');
            if ($sb1 === false) {{
                $lign = $rawLign;
                $nomlign = $rawLign;
            }} else {{
                $lign = substr($rawLign, 0, $sb1);
                $nomlign = substr($rawLign, $sb1 + 1);
            }}
'''

SPLIT_HEURE = '''
            $rawHeure = trim((string) $this->input->get_post('{field}'));
            $sb2 = strpos($rawHeure, '/');
            if ($sb2 === false) {{
                $her = $rawHeure;
                $heur = $rawHeure;
            }} else {{
                $her = substr($rawHeure, 0, $sb2);
                $heur = substr($rawHeure, $sb2 + 1);
            }}
'''

code_trinombre = f'''
        protected function _trinombre_payload($ckey)
        {{
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('date1'));
            $dt2 = trim((string) $this->input->get_post('date2'));
            $cp = trim((string) $this->input->get_post('nomcomp'));
            $gid = trim((string) $this->input->get_post('nomgare'));
{SPLIT_LIGNE.format(field='lignear')}{SPLIT_HEURE.format(field='heuredepart')}
            $nbrpas = $this->m_passager->reporpass($this->entreprise->ekey, $cp, $gid, $dt1, $dt2, $lign, $her);
            if (!is_array($nbrpas)) {{
                $nbrpas = array();
            }}
            $lignes = array();
            $total = 0.0;
            foreach ($nbrpas as $pass) {{
                $nbr = isset($pass->nbr) ? (int) round((float) $pass->nbr) : 0;
                $lignes[] = array(
                    'dateheure' => trim((isset($pass->date_progr) ? (string) $pass->date_progr : '') . ' ' . (isset($pass->heure) ? (string) $pass->heure : '')),
                    'ligne' => isset($pass->nom_ligne) ? (string) $pass->nom_ligne : '',
                    'nbr' => $nbr,
                );
                $total += $nbr;
            }}
            $qs = http_build_query(array_filter(array(
                'date1' => $dt1, 'date2' => $dt2, 'nomcomp' => $cp, 'nomgare' => $gid,
                'lignear' => $rawLign, 'heuredepart' => $rawHeure,
                {QS}
            )));
            return array(
                'titre' => 'ETATS PASSAGERS ' . $nomlign . ' ' . $heur,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                {RETOUR}
                'columns' => array(
                    array('key' => 'dateheure', 'label' => 'Date / heure', 'align' => 'left'),
                    array('key' => 'ligne', 'label' => 'Ligne', 'align' => 'left'),
                    array('key' => 'nbr', 'label' => 'Nbr passagers', 'align' => 'center'),
                ),
                'export_base' => site_url('Rapport/trinombre_export/' . rawurlencode($ckey)),
            );
        }}
{pub('trinombre', 'États passagers')}
'''

code_trinombrees = f'''
        protected function _trinombrees_payload($ckey)
        {{
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('date1es'));
            $dt2 = trim((string) $this->input->get_post('date2es'));
            $cp = trim((string) $this->input->get_post('nomcompes'));
            $gid = trim((string) $this->input->get_post('nomgarees'));
{SPLIT_LIGNE.format(field='ligneares')}{SPLIT_HEURE.format(field='heuredepartes')}
            $nbrpas = $this->m_escalclients->reporpass($this->entreprise->ekey, $cp, $gid, $dt1, $dt2, $lign, $her);
            if (!is_array($nbrpas)) {{
                $nbrpas = array();
            }}
            $lignes = array();
            $total = 0.0;
            foreach ($nbrpas as $pass) {{
                $lignes[] = array(
                    'dateheure' => trim((isset($pass->datedepescal) ? (string) $pass->datedepescal : '') . ' ' . (isset($pass->heure) ? (string) $pass->heure : '')),
                    'ligne' => isset($pass->nom_ligne) ? (string) $pass->nom_ligne : '',
                    'client' => trim((isset($pass->nom_client) ? (string) $pass->nom_client : '') . ' ' . (isset($pass->prenom_client) ? (string) $pass->prenom_client : '') . ' ' . (isset($pass->contact_client) ? (string) $pass->contact_client : '')),
                );
                $total += 1;
            }}
            $qs = http_build_query(array_filter(array(
                'date1es' => $dt1, 'date2es' => $dt2, 'nomcompes' => $cp, 'nomgarees' => $gid,
                'ligneares' => $rawLign, 'heuredepartes' => $rawHeure,
                {QS}
            )));
            return array(
                'titre' => 'ETAT PASSAGERS ESCAL ' . $nomlign . ' ' . $heur,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                {RETOUR}
                'columns' => array(
                    array('key' => 'dateheure', 'label' => 'Date / heure', 'align' => 'left'),
                    array('key' => 'ligne', 'label' => 'Ligne', 'align' => 'left'),
                    array('key' => 'client', 'label' => 'Nom / prénom / contact', 'align' => 'left'),
                ),
                'export_base' => site_url('Rapport/trinombrees_export/' . rawurlencode($ckey)),
            );
        }}
{pub('trinombrees', 'États passagers escal')}
'''

COLS_PASS = """'columns' => array(
                    array('key' => 'code', 'label' => 'Code', 'align' => 'left'),
                    array('key' => 'nom', 'label' => 'Nom', 'align' => 'left'),
                    array('key' => 'prenom', 'label' => 'Prénom', 'align' => 'left'),
                    array('key' => 'contact', 'label' => 'Contact', 'align' => 'left'),
                    array('key' => 'ligne', 'label' => 'Ligne', 'align' => 'left'),
                    array('key' => 'prix', 'label' => 'Prix', 'align' => 'right', 'money' => true),
                ),"""

code_trinombrepass = f'''
        protected function _trinombrepass_payload($ckey)
        {{
            $this->_rapport_limits();
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dtp1 = trim((string) $this->input->get_post('dateps1'));
            $dtp2 = trim((string) $this->input->get_post('dateps2'));
            $cp = trim((string) $this->input->get_post('nomcomps'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('nomgares'));
            $ncomp = $this->m_compagnies->getn($cp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            list($day, $day2) = $this->_recap_title_dates($dtp1, $dtp2);

            $nbrpas = $this->m_passager->exopass($this->entreprise->ekey, $cp, $dtp1, $dtp2, $gid);
            $nbrpasrt = $this->m_non_passager->exopass($this->entreprise->ekey, $cp, $dtp1, $dtp2, $gid);
            if (!is_array($nbrpas)) {{
                $nbrpas = array();
            }}
            if (!is_array($nbrpasrt)) {{
                $nbrpasrt = array();
            }}
            usort($nbrpas, function ($a, $b) {{
                $cmpNom = strcmp(isset($a->nom_client) ? $a->nom_client : '', isset($b->nom_client) ? $b->nom_client : '');
                if ($cmpNom !== 0) {{
                    return $cmpNom;
                }}
                return strcmp(isset($a->prenom_client) ? $a->prenom_client : '', isset($b->prenom_client) ? $b->prenom_client : '');
            }});
            usort($nbrpasrt, function ($a, $b) {{
                $cmpNom = strcmp(isset($a->nom_client) ? $a->nom_client : '', isset($b->nom_client) ? $b->nom_client : '');
                if ($cmpNom !== 0) {{
                    return $cmpNom;
                }}
                return strcmp(isset($a->prenom_client) ? $a->prenom_client : '', isset($b->prenom_client) ? $b->prenom_client : '');
            }});

            $lignes = array();
            $total = 0.0;
            foreach ($nbrpas as $p) {{
                $mt = isset($p->prixvente) ? (float) $p->prixvente : 0.0;
                $lignes[] = array(
                    'code' => isset($p->code_ticket) ? (string) $p->code_ticket : '',
                    'nom' => isset($p->nom_client) ? (string) $p->nom_client : '',
                    'prenom' => isset($p->prenom_client) ? (string) $p->prenom_client : '',
                    'contact' => isset($p->contact_client) ? (string) $p->contact_client : '',
                    'ligne' => isset($p->nom_ligne) ? (string) $p->nom_ligne : '',
                    'prix' => $mt,
                    'type' => 'Aller',
                );
                $total += $mt;
            }}
            foreach ($nbrpasrt as $r) {{
                $mt = isset($r->prixretour) ? (float) $r->prixretour : 0.0;
                $lignes[] = array(
                    'code' => isset($r->codeticket) ? (string) $r->codeticket : '',
                    'nom' => isset($r->nom_client) ? (string) $r->nom_client : '',
                    'prenom' => isset($r->prenom_client) ? (string) $r->prenom_client : '',
                    'contact' => isset($r->contact_client) ? (string) $r->contact_client : '',
                    'ligne' => isset($r->nom_ligne) ? (string) $r->nom_ligne : '',
                    'prix' => $mt,
                    'type' => 'Retour',
                );
                $total += $mt;
            }}

            $qs = http_build_query(array_filter(array(
                'dateps1' => $dtp1, 'dateps2' => $dtp2, 'nomcomps' => $cp, 'nomgares' => $gid,
                {QS}
            )));
            return array(
                'titre' => 'EXERCICE LISTE PASSAGERS ' . $cieNom . ' DU ' . $day . ' AU ' . $day2,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                {RETOUR}
                'columns' => array(
                    array('key' => 'type', 'label' => 'Type', 'align' => 'left'),
                    array('key' => 'code', 'label' => 'Code', 'align' => 'left'),
                    array('key' => 'nom', 'label' => 'Nom', 'align' => 'left'),
                    array('key' => 'prenom', 'label' => 'Prénom', 'align' => 'left'),
                    array('key' => 'contact', 'label' => 'Contact', 'align' => 'left'),
                    array('key' => 'ligne', 'label' => 'Ligne', 'align' => 'left'),
                    array('key' => 'prix', 'label' => 'Prix', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/trinombrepass_export/' . rawurlencode($ckey)),
            );
        }}
{pub('trinombrepass', 'Exercice liste passagers')}
'''

code_trinombrepassesc = f'''
        protected function _trinombrepassesc_payload($ckey)
        {{
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dtp1 = trim((string) $this->input->get_post('dateps1esc'));
            $dtp2 = trim((string) $this->input->get_post('dateps2esc'));
            $cp = trim((string) $this->input->get_post('nomcompsesc'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('nomgaresesc'));
            $ncomp = $this->m_compagnies->getn($cp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            list($day, $day2) = $this->_recap_title_dates($dtp1, $dtp2);

            $nbrpas = $this->m_escalclients->exopass($this->entreprise->ekey, $cp, $gid, $dtp1, $dtp2);
            if (!is_array($nbrpas)) {{
                $nbrpas = array();
            }}
            $lignes = array();
            $total = 0.0;
            foreach ($nbrpas as $pass) {{
                $mt = isset($pass->prixescal) ? (float) $pass->prixescal : 0.0;
                $lignes[] = array(
                    'code' => isset($pass->idclescal) ? (string) $pass->idclescal : '',
                    'nom' => isset($pass->nom_client) ? (string) $pass->nom_client : '',
                    'prenom' => isset($pass->prenom_client) ? (string) $pass->prenom_client : '',
                    'contact' => isset($pass->contact_client) ? (string) $pass->contact_client : '',
                    'ligne' => isset($pass->nom_ligne) ? (string) $pass->nom_ligne : '',
                    'prix' => $mt,
                );
                $total += $mt;
            }}
            $qs = http_build_query(array_filter(array(
                'dateps1esc' => $dtp1, 'dateps2esc' => $dtp2, 'nomcompsesc' => $cp, 'nomgaresesc' => $gid,
                {QS}
            )));
            return array(
                'titre' => 'EXERCICE LISTE PASSAGERS ESCAL ' . $cieNom . ' DU ' . $day . ' AU ' . $day2,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                {RETOUR}
                {COLS_PASS}
                'export_base' => site_url('Rapport/trinombrepassesc_export/' . rawurlencode($ckey)),
            );
        }}
{pub('trinombrepassesc', 'Exercice liste passagers escal')}
'''

code_tripassagergr = f'''
        protected function _tripassagergr_payload($ckey, $us, $gd, $sgd)
        {{
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dtp1 = trim((string) $this->input->get_post('debutdateg'));
            $dtp2 = trim((string) $this->input->get_post('findateg'));
            list($day, $day2) = $this->_recap_title_dates($dtp1, $dtp2);

            $nbrpas = $this->m_ordres->gettr($this->entreprise->ekey, $gd, $dtp1, $dtp2);
            if (!is_array($nbrpas)) {{
                $nbrpas = array();
            }}
            $lignes = array();
            $total = 0.0;
            foreach ($nbrpas as $passg) {{
                $lignes[] = array(
                    'operateur' => trim((isset($passg->username) ? (string) $passg->username : '') . ' ' . (isset($passg->pourordre) ? (string) $passg->pourordre : '')),
                    'code' => isset($passg->code_ticket) ? (string) $passg->code_ticket : '',
                    'client' => trim((isset($passg->nom_client) ? (string) $passg->nom_client : '') . ' ' . (isset($passg->prenom_client) ? (string) $passg->prenom_client : '')),
                    'ligne' => isset($passg->nom_ligne) ? (string) $passg->nom_ligne : '',
                );
                $total += 1;
            }}
            $qs = http_build_query(array_filter(array(
                'debutdateg' => $dtp1, 'findateg' => $dtp2,
                {QS}
            )));
            return array(
                'titre' => 'ETATS AUTRES PASSAGERS ' . $day . ' ' . $day2,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                {RETOUR}
                'columns' => array(
                    array('key' => 'operateur', 'label' => 'Opérateur et P/O', 'align' => 'left'),
                    array('key' => 'code', 'label' => 'Code', 'align' => 'left'),
                    array('key' => 'client', 'label' => 'Nom / prénom', 'align' => 'left'),
                    array('key' => 'ligne', 'label' => 'Lignes', 'align' => 'left'),
                ),
                'export_base' => site_url('Rapport/tripassagergr_export/' . rawurlencode($ckey) . '/' . rawurlencode($us) . '/' . rawurlencode($gd) . '/' . rawurlencode($sgd)),
            );
        }}
{pub('tripassagergr', 'États autres passagers', '$ckey, $us, $gd, $sgd')}
'''

code_trinombrepassglob = f'''
        protected function _trinombrepassglob_payload($ckey)
        {{
            $this->_rapport_limits();
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dtp1 = trim((string) $this->input->get_post('dateps1'));
            $dtp2 = trim((string) $this->input->get_post('dateps2'));
            $cp = trim((string) $this->input->get_post('nomcomps'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('nomgares'));
            $ncomp = $this->m_compagnies->getn($cp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            list($day1, $day2) = $this->_recap_title_dates($dtp1, $dtp2);

            $nbrpas = $this->m_passager->exopassglob($this->entreprise->ekey, $cp, $dtp1, $dtp2, $gid);
            if (!is_array($nbrpas)) {{
                $nbrpas = array();
            }}
            usort($nbrpas, function ($a, $b) {{
                $cmpNom = strcmp(isset($a->nom_client) ? $a->nom_client : '', isset($b->nom_client) ? $b->nom_client : '');
                if ($cmpNom !== 0) {{
                    return $cmpNom;
                }}
                return strcmp(isset($a->prenom_client) ? $a->prenom_client : '', isset($b->prenom_client) ? $b->prenom_client : '');
            }});

            $lignes = array();
            $total = 0.0;
            foreach ($nbrpas as $p) {{
                $mt = isset($p->prixvente) ? (float) $p->prixvente : 0.0;
                $lignes[] = array(
                    'code' => isset($p->code_ticket) ? (string) $p->code_ticket : '',
                    'nom' => isset($p->nom_client) ? (string) $p->nom_client : '',
                    'prenom' => isset($p->prenom_client) ? (string) $p->prenom_client : '',
                    'contact' => isset($p->contact_client) ? (string) $p->contact_client : '',
                    'ligne' => isset($p->nom_ligne) ? (string) $p->nom_ligne : '',
                    'prix' => $mt,
                );
                $total += $mt;
            }}
            $qs = http_build_query(array_filter(array(
                'dateps1' => $dtp1, 'dateps2' => $dtp2, 'nomcomps' => $cp, 'nomgares' => $gid,
                {QS}
            )));
            return array(
                'titre' => 'LISTE PASSAGERS GLOBAL ' . $cieNom . ' DU ' . $day1 . ' AU ' . $day2,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                {RETOUR}
                {COLS_PASS}
                'export_base' => site_url('Rapport/trinombrepassglob_export/' . rawurlencode($ckey)),
            );
        }}
{pub('trinombrepassglob', 'Liste passagers global')}
'''

code_trinombrepassglobesc = f'''
        protected function _trinombrepassglobesc_payload($ckey)
        {{
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dtp1 = trim((string) $this->input->get_post('dateps1esc'));
            $dtp2 = trim((string) $this->input->get_post('dateps2esc'));
            $cp = trim((string) $this->input->get_post('nomcompsesc'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('nomgaresesc'));
            $ncomp = $this->m_compagnies->getn($cp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            list($day, $day2) = $this->_recap_title_dates($dtp1, $dtp2);

            $nbrpas = $this->m_escalclients->exopassglob($this->entreprise->ekey, $cp, $gid, $dtp1, $dtp2);
            if (!is_array($nbrpas)) {{
                $nbrpas = array();
            }}
            $lignes = array();
            $total = 0.0;
            foreach ($nbrpas as $pass) {{
                $mt = isset($pass->prixescal) ? (float) $pass->prixescal : 0.0;
                $lignes[] = array(
                    'code' => isset($pass->idclescal) ? (string) $pass->idclescal : '',
                    'nom' => isset($pass->nom_client) ? (string) $pass->nom_client : '',
                    'prenom' => isset($pass->prenom_client) ? (string) $pass->prenom_client : '',
                    'contact' => isset($pass->contact_client) ? (string) $pass->contact_client : '',
                    'ligne' => isset($pass->nom_ligne) ? (string) $pass->nom_ligne : '',
                    'prix' => $mt,
                );
                $total += $mt;
            }}
            $qs = http_build_query(array_filter(array(
                'dateps1esc' => $dtp1, 'dateps2esc' => $dtp2, 'nomcompsesc' => $cp, 'nomgaresesc' => $gid,
                {QS}
            )));
            return array(
                'titre' => 'LISTE GLOBALE PASSAGERS ESCAL ' . $cieNom . ' DU ' . $day . ' AU ' . $day2,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                {RETOUR}
                {COLS_PASS}
                'export_base' => site_url('Rapport/trinombrepassglobesc_export/' . rawurlencode($ckey)),
            );
        }}
{pub('trinombrepassglobesc', 'Liste passagers global escal')}
'''

code_etatpassagers = f'''
        protected function _etatpassagers_payload($ckey)
        {{
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('debudate'));
            $dt2 = trim((string) $this->input->get_post('fidate'));
            $user = trim((string) $this->input->get_post('vendeuseid'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgar'));
            $sta = trim((string) $this->input->get_post('statutticket'));
            $op = $this->_resolve_report_operateur($user);
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);

            if ($sta === 'confirm') {{
                $ticketetats = $this->m_passager->etatsc($this->entreprise->ekey, $dt1, $dt2, $gid, $user, $sta);
            }} elseif ($sta === 'repor') {{
                $ticketetats = $this->m_passager->etats($this->entreprise->ekey, $dt1, $dt2, $gid, $user, $sta);
            }} else {{
                $ticketetats = $this->m_passager->etats1($this->entreprise->ekey, $dt1, $dt2, $gid, $user);
            }}
            if (!is_array($ticketetats)) {{
                $ticketetats = array();
            }}
            $lignes = array();
            $total = 0.0;
            foreach ($ticketetats as $lement) {{
                $mt = isset($lement->prixvente) ? (float) $lement->prixvente : 0.0;
                $lignes[] = array(
                    'code' => isset($lement->code_ticket) ? (string) $lement->code_ticket : '',
                    'ligne' => isset($lement->nom_ligne) ? (string) $lement->nom_ligne : '',
                    'client' => trim((isset($lement->nom_client) ? (string) $lement->nom_client : '') . ' ' . (isset($lement->prenom_client) ? (string) $lement->prenom_client : '')),
                    'dateheure' => trim((isset($lement->date_progr) ? (string) $lement->date_progr : '') . ' ' . (isset($lement->heure) ? (string) $lement->heure : '')),
                    'prix' => $mt,
                );
                $total += $mt;
            }}
            $qs = http_build_query(array_filter(array(
                'debudate' => $dt1, 'fidate' => $dt2, 'vendeuseid' => $user,
                'departgar' => $gid, 'statutticket' => $sta,
                {QS}
            )));
            return array(
                'titre' => 'ETATS DES TICKETS ' . $op['label'] . ' ' . $sta . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                {RETOUR}
                'columns' => array(
                    array('key' => 'code', 'label' => 'Code', 'align' => 'left'),
                    array('key' => 'ligne', 'label' => 'Ligne', 'align' => 'left'),
                    array('key' => 'client', 'label' => 'Nom et prénom', 'align' => 'left'),
                    array('key' => 'dateheure', 'label' => 'Date et heure', 'align' => 'left'),
                    array('key' => 'prix', 'label' => 'Prix', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/etatpassagers_export/' . rawurlencode($ckey)),
            );
        }}
{pub('etatpassagers', 'États des tickets')}
'''

code_passagervendu = f'''
        protected function _passagervendu_payload($ckey, $gd, $cpu)
        {{
            $dd = trim((string) $this->input->get_post('debutdate'));
            $df = trim((string) $this->input->get_post('findate'));
            $comp = trim((string) $this->input->get_post('_compag'));
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            list($days, $days1) = $this->_recap_title_dates($dd, $df);
            $this->entreprise = $this->m_entreprises->get_key($ckey);

            $onvente = $this->m_passager->ventejour($this->entreprise->ekey, $gd, $cpu, $dd, $df);
            if (!is_array($onvente)) {{
                $onvente = array();
            }}
            $lignes = array();
            $total = 0.0;
            foreach ($onvente as $element) {{
                $lignes[] = array(
                    'siege' => isset($element->num_siege_categorie) ? (string) $element->num_siege_categorie : '',
                    'code' => isset($element->code_ticket) ? (string) $element->code_ticket : '',
                    'itineraire' => isset($element->nom_ligne) ? (string) $element->nom_ligne : '',
                    'quartier' => isset($element->quart) ? (string) $element->quart : '',
                    'client' => trim((isset($element->nom_client) ? (string) $element->nom_client : '') . ' ' . (isset($element->prenom_client) ? (string) $element->prenom_client : '')),
                    'contact' => isset($element->contact_client) ? (string) $element->contact_client : '',
                );
                $total += 1;
            }}
            $qs = http_build_query(array_filter(array(
                'debutdate' => $dd, 'findate' => $df, '_compag' => $comp,
                {QS}
            )));
            $titre = 'PASSAGERS VENDU DU ' . $days . ' AU ' . $days1;
            if ($cieNom !== '') {{
                $titre .= ' — ' . $cieNom;
            }}
            return array(
                'titre' => $titre,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                {RETOUR}
                'columns' => array(
                    array('key' => 'siege', 'label' => 'Siège', 'align' => 'center'),
                    array('key' => 'code', 'label' => 'Code', 'align' => 'left'),
                    array('key' => 'itineraire', 'label' => 'Itinéraire', 'align' => 'left'),
                    array('key' => 'quartier', 'label' => 'Quartier', 'align' => 'left'),
                    array('key' => 'client', 'label' => 'Nom passager', 'align' => 'left'),
                    array('key' => 'contact', 'label' => 'Contact', 'align' => 'left'),
                ),
                'export_base' => site_url('Rapport/passagervendu_export/' . rawurlencode($ckey) . '/' . rawurlencode($gd) . '/' . rawurlencode($cpu)),
            );
        }}
{pub('passagervendu', 'Passagers vendus', '$ckey, $gd, $cpu')}
'''

code_passagervenduesc = f'''
        protected function _passagervenduesc_payload($ckey, $gd, $cpu)
        {{
            $dd = trim((string) $this->input->get_post('debutdate'));
            $df = trim((string) $this->input->get_post('findate'));
            $comp = trim((string) $this->input->get_post('_compag'));
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            list($days, $days1) = $this->_recap_title_dates($dd, $df);
            $this->entreprise = $this->m_entreprises->get_key($ckey);

            $onvente = $this->m_escalclients->ventejour($this->entreprise->ekey, $gd, $cpu, $dd, $df);
            if (!is_array($onvente)) {{
                $onvente = array();
            }}
            $lignes = array();
            $total = 0.0;
            foreach ($onvente as $element) {{
                $lignes[] = array(
                    'code' => isset($element->idclescal) ? (string) $element->idclescal : '',
                    'itineraire' => isset($element->nom_ligne) ? (string) $element->nom_ligne : '',
                    'quartier' => isset($element->quartier_escal) ? (string) $element->quartier_escal : '',
                    'client' => trim((isset($element->nom_client) ? (string) $element->nom_client : '') . ' ' . (isset($element->prenom_client) ? (string) $element->prenom_client : '')),
                    'contact' => isset($element->contact_client) ? (string) $element->contact_client : '',
                );
                $total += 1;
            }}
            $qs = http_build_query(array_filter(array(
                'debutdate' => $dd, 'findate' => $df, '_compag' => $comp,
                {QS}
            )));
            $titre = 'PASSAGERS VENDU ESCAL DU ' . $days . ' AU ' . $days1;
            if ($cieNom !== '') {{
                $titre .= ' — ' . $cieNom;
            }}
            return array(
                'titre' => $titre,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                {RETOUR}
                'columns' => array(
                    array('key' => 'code', 'label' => 'Code', 'align' => 'left'),
                    array('key' => 'itineraire', 'label' => 'Itinéraire', 'align' => 'left'),
                    array('key' => 'quartier', 'label' => 'Quartier', 'align' => 'left'),
                    array('key' => 'client', 'label' => 'Nom passager', 'align' => 'left'),
                    array('key' => 'contact', 'label' => 'Contact', 'align' => 'left'),
                ),
                'export_base' => site_url('Rapport/passagervenduesc_export/' . rawurlencode($ckey) . '/' . rawurlencode($gd) . '/' . rawurlencode($cpu)),
            );
        }}
{pub('passagervenduesc', 'Passagers vendus escal', '$ckey, $gd, $cpu')}
'''


# Verify field names for passesc prix from original
esc_body = Path('/tmp/trinombrepassesc.php').read_text(encoding='utf-8', errors='replace')
# fix prix field if needed after reading

for name, code in [
    ('trinombre', code_trinombre),
    ('trinombrees', code_trinombrees),
    ('trinombrepass', code_trinombrepass),
    ('trinombrepassesc', code_trinombrepassesc),
    ('tripassagergr', code_tripassagergr),
    ('trinombrepassglob', code_trinombrepassglob),
    ('trinombrepassglobesc', code_trinombrepassglobesc),
    ('etatpassagers', code_etatpassagers),
    ('passagervendu', code_passagervendu),
    ('passagervenduesc', code_passagervenduesc),
]:
    src = replace_method(src, name, code)
    print('OK', name)

path.write_text(src, encoding='utf-8')
print('Wrote', path)
