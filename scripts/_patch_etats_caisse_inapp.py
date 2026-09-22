#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""Lot G: états caisse (recette/dépense/dépôt/versements) → in-app."""
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

# Shared snippets
COMMON_HEAD = """
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $date1 = trim((string) $this->input->get_post('{d1}'));
            $date2 = trim((string) $this->input->get_post('{d2}'));
            $typ = trim((string) $this->input->get_post('{typ}'));
            $gen = trim((string) $this->input->get_post('{gen}'));
            $nm = trim((string) $this->input->get_post('{nm}'));
            $comp = trim((string) $this->input->get_post('{comp}'));
            $gid = trim((string) $this->input->get_post('{gid}'));
            $atr = roleattribut_guard_post_hint($this->entreprise->ekey{atr_extra});
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            $ngrd = $this->m_gare_depart->getno($gid);
            $garNom = ($ngrd && isset($ngrd->garenom)) ? $ngrd->garenom : $gid;
            list($days, $days1) = $this->_recap_title_dates($date1, $date2);
            $role = (string) $this->session->agent->userole;
"""

def head(d1='datedebut', d2='datefin', typ='type', gen='genre', nm='nom',
         comp='_compag', gid='gareconnect', atr_extra=''):
    return COMMON_HEAD.format(
        d1=d1, d2=d2, typ=typ, gen=gen, nm=nm, comp=comp, gid=gid,
        atr_extra=(', ' + atr_extra) if atr_extra else '',
    )


def pub(name, label, payload):
    return f'''
        public function {name}($ckey)
        {{
            return $this->_etat_render_view('{label}', $this->_{name}_payload($ckey));
        }}

        public function {name}_export($ckey)
        {{
            $this->_etat_export_dispatch($this->_{name}_payload($ckey));
        }}
'''


code_recette = f'''
        protected function _recette_payload($ckey)
        {{{head()}
            if ($role === '4') {{
                $rows = $this->m_recette->trirecette($this->entreprise->ekey, $gid, $date1, $date2, $atr, $comp, $typ, $gen, $nm);
            }} elseif ($role === '18') {{
                $rows = $this->m_recette->adtrirecette($this->entreprise->ekey, $gid, $date1, $date2, $atr, $comp, $typ, $gen, $nm);
            }} elseif ($role === '1' || $role === '2') {{
                $rows = $this->m_recette->trirecetteadmin($this->entreprise->ekey, $gid, $date1, $date2, $comp, $typ, $gen, $nm);
            }} else {{
                $rows = $this->m_recette->trirecette_adjoint($this->entreprise->ekey, $gid, $atr, $date1, $date2, $comp, $typ, $gen, $nm);
            }}
            if (!is_array($rows)) {{
                $rows = array();
            }}
            $lignes = array();
            $total = 0.0;
            foreach ($rows as $rect) {{
                $mt = isset($rect->montant_recet) ? (float) $rect->montant_recet : 0.0;
                $lignes[] = array(
                    'date' => isset($rect->date_recet) ? (string) $rect->date_recet : '',
                    'type' => isset($rect->type_recet) ? (string) $rect->type_recet : '',
                    'genre' => isset($rect->type_personnel) ? (string) $rect->type_personnel : '',
                    'nom' => isset($rect->nom) ? (string) $rect->nom : '',
                    'commentaire' => isset($rect->commentaire_recet) ? (string) $rect->commentaire_recet : '',
                    'montant' => $mt,
                );
                $total += $mt;
            }}
            $qs = http_build_query(array_filter(array(
                'datedebut' => $date1, 'datefin' => $date2, 'type' => $typ, 'genre' => $gen,
                'nom' => $nm, '_compag' => $comp, 'gareconnect' => $gid, {QS}
            )));
            return array(
                'titre' => 'ETATS DES RECETTES DE ' . $cieNom . ' ' . $garNom . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes, 'total' => $total, 'filters_qs' => $qs, {RETOUR}
                'columns' => array(
                    array('key' => 'date', 'label' => 'Date', 'align' => 'left'),
                    array('key' => 'type', 'label' => 'Type', 'align' => 'left'),
                    array('key' => 'genre', 'label' => 'Genre', 'align' => 'left'),
                    array('key' => 'nom', 'label' => 'Nom', 'align' => 'left'),
                    array('key' => 'commentaire', 'label' => 'Commentaire', 'align' => 'left'),
                    array('key' => 'montant', 'label' => 'Total', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/recette_export/' . rawurlencode($ckey)),
            );
        }}
{pub('recette', 'États des recettes', '_recette_payload')}
'''

code_recettecr = f'''
        protected function _recettecr_payload($ckey)
        {{{head(d1='datedebutcr', d2='datefincr', typ='typecr', gen='genrecr', nm='nomcr', comp='_compagcr', gid='gareconnectcr', atr_extra="'gareconnect', 'userconnectedcr'")}
            $serole = $this->m_compte_user->attcpus($atr);
            $srole = ($serole && isset($serole->userole)) ? (string) $serole->userole : $role;
            if ($srole === '4') {{
                $rows = $this->m_recette->trirecettecr($this->entreprise->ekey, $gid, $date1, $date2, $atr, $gen, $comp, $nm);
            }} elseif ($srole === '18') {{
                $rows = $this->m_recette->adtrirecettecr($this->entreprise->ekey, $gid, $date1, $date2, $atr, $gen, $comp, $nm);
            }} elseif ($srole === '1' || $srole === '2') {{
                $rows = $this->m_recette->trirecetteadmincr($this->entreprise->ekey, $gid, $date1, $date2, $gen, $comp, $nm);
            }} else {{
                $rows = $this->m_recette->trirecette_adjointcr($this->entreprise->ekey, $gid, $atr, $date1, $date2, $gen, $comp, $nm);
            }}
            if (!is_array($rows)) {{
                $rows = array();
            }}
            $lignes = array();
            $total = 0.0;
            foreach ($rows as $rect) {{
                $mt = isset($rect->montant_recet) ? (float) $rect->montant_recet : 0.0;
                $lignes[] = array(
                    'date' => isset($rect->date_recet) ? (string) $rect->date_recet : '',
                    'type' => isset($rect->type_recet) ? (string) $rect->type_recet : '',
                    'genre' => isset($rect->type_personnel) ? (string) $rect->type_personnel : '',
                    'nom' => isset($rect->nom) ? (string) $rect->nom : '',
                    'commentaire' => isset($rect->commentaire_recet) ? (string) $rect->commentaire_recet : '',
                    'montant' => $mt,
                );
                $total += $mt;
            }}
            $qs = http_build_query(array_filter(array(
                'datedebutcr' => $date1, 'datefincr' => $date2, 'typecr' => $typ, 'genrecr' => $gen,
                'nomcr' => $nm, '_compagcr' => $comp, 'gareconnectcr' => $gid, {QS}
            )));
            return array(
                'titre' => 'ETATS DES RECETTES COURRIER ' . $cieNom . ' ' . $garNom . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes, 'total' => $total, 'filters_qs' => $qs, {RETOUR}
                'columns' => array(
                    array('key' => 'date', 'label' => 'Date', 'align' => 'left'),
                    array('key' => 'type', 'label' => 'Type', 'align' => 'left'),
                    array('key' => 'genre', 'label' => 'Genre', 'align' => 'left'),
                    array('key' => 'nom', 'label' => 'Nom', 'align' => 'left'),
                    array('key' => 'commentaire', 'label' => 'Commentaire', 'align' => 'left'),
                    array('key' => 'montant', 'label' => 'Total', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/recettecr_export/' . rawurlencode($ckey)),
            );
        }}
{pub('recettecr', 'États des recettes courrier', '_recettecr_payload')}
'''

code_depense = f'''
        protected function _depense_payload($ckey)
        {{{head()}
            if (recette_role_is_validateur_principal($role)) {{
                $rows = $this->m_depense->tridepense($this->entreprise->ekey, $gid, $atr, $comp, $date1, $date2, $gen, $nm);
            }} elseif (recette_role_is_validateur_adjoint($role)) {{
                $rows = $this->m_depense->adtridepense($this->entreprise->ekey, $gid, $atr, $comp, $date1, $date2, $gen, $nm);
            }} elseif ($role === '1' || $role === '2') {{
                $rows = $this->m_depense->tridepenseadmin($this->entreprise->ekey, $gid, $comp, $date1, $date2, $typ, $gen, $nm);
            }} elseif (recette_role_is_saisie($role)) {{
                $rows = $this->m_depense->tridepense_adjoint($this->entreprise->ekey, $gid, $atr, $date1, $date2, $comp, $typ, $gen, $nm);
            }} else {{
                $rows = array();
            }}
            if (!is_array($rows)) {{
                $rows = array();
            }}
            $lignes = array();
            $total = 0.0;
            foreach ($rows as $depen) {{
                $mt = isset($depen->montant_depens) ? (float) $depen->montant_depens : 0.0;
                $lignes[] = array(
                    'date' => isset($depen->date_depens) ? (string) $depen->date_depens : '',
                    'type' => isset($depen->type_depense) ? (string) $depen->type_depense : '',
                    'genre' => isset($depen->genre_depens) ? (string) $depen->genre_depens : '',
                    'nom' => isset($depen->nom_perso) ? (string) $depen->nom_perso : '',
                    'commentaire' => isset($depen->commentaire) ? (string) $depen->commentaire : '',
                    'motif' => isset($depen->motif) ? (string) $depen->motif : '',
                    'montant' => $mt,
                );
                $total += $mt;
            }}
            $qs = http_build_query(array_filter(array(
                'datedebut' => $date1, 'datefin' => $date2, 'type' => $typ, 'genre' => $gen,
                'nom' => $nm, '_compag' => $comp, 'gareconnect' => $gid, {QS}
            )));
            return array(
                'titre' => 'ETATS DES DEPENSES DE ' . $cieNom . ' ' . $garNom . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes, 'total' => $total, 'filters_qs' => $qs, {RETOUR}
                'columns' => array(
                    array('key' => 'date', 'label' => 'Date', 'align' => 'left'),
                    array('key' => 'type', 'label' => 'Type', 'align' => 'left'),
                    array('key' => 'genre', 'label' => 'Genre', 'align' => 'left'),
                    array('key' => 'nom', 'label' => 'Nom', 'align' => 'left'),
                    array('key' => 'commentaire', 'label' => 'Commentaire', 'align' => 'left'),
                    array('key' => 'motif', 'label' => 'Motif', 'align' => 'left'),
                    array('key' => 'montant', 'label' => 'Total', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/depense_export/' . rawurlencode($ckey)),
            );
        }}
{pub('depense', 'États des dépenses', '_depense_payload')}
'''

code_depot = f'''
        protected function _depot_payload($ckey)
        {{{head()}
            if (recette_role_is_validateur_principal($role)) {{
                $rows = $this->m_depot->tridepot($this->entreprise->ekey, $gid, $atr, $comp, $date1, $date2, $gen, $nm);
            }} elseif (recette_role_is_validateur_adjoint($role)) {{
                $rows = $this->m_depot->adtridepot($this->entreprise->ekey, $gid, $atr, $comp, $date1, $date2, $gen, $nm);
            }} elseif ($role === '1' || $role === '2') {{
                $rows = $this->m_depot->tridepotadmin($this->entreprise->ekey, $gid, $comp, $date1, $date2, $typ, $gen, $nm);
            }} elseif (recette_role_is_saisie($role)) {{
                $rows = $this->m_depot->tridepot_adjoint($this->entreprise->ekey, $gid, $atr, $comp, $date1, $date2, $typ, $gen, $nm);
            }} else {{
                $rows = array();
            }}
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
                    'genre' => isset($depot->type_personnel) ? (string) $depot->type_personnel : '',
                    'nom' => isset($depot->nom_pre) ? (string) $depot->nom_pre : '',
                    'commentaire' => isset($depot->commentaire_depot) ? (string) $depot->commentaire_depot : '',
                    'montant' => $mt,
                );
                $total += $mt;
            }}
            $qs = http_build_query(array_filter(array(
                'datedebut' => $date1, 'datefin' => $date2, 'type' => $typ, 'genre' => $gen,
                'nom' => $nm, '_compag' => $comp, 'gareconnect' => $gid, {QS}
            )));
            return array(
                'titre' => 'ETATS DES DEPOTS DE ' . $cieNom . ' ' . $garNom . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes, 'total' => $total, 'filters_qs' => $qs, {RETOUR}
                'columns' => array(
                    array('key' => 'date', 'label' => 'Date', 'align' => 'left'),
                    array('key' => 'type', 'label' => 'Type', 'align' => 'left'),
                    array('key' => 'genre', 'label' => 'Genre', 'align' => 'left'),
                    array('key' => 'nom', 'label' => 'Nom', 'align' => 'left'),
                    array('key' => 'commentaire', 'label' => 'Commentaire', 'align' => 'left'),
                    array('key' => 'montant', 'label' => 'Total', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/depot_export/' . rawurlencode($ckey)),
            );
        }}
{pub('depot', 'États des dépôts', '_depot_payload')}
'''

code_autredepot = f'''
        protected function _autredepot_payload($ckey)
        {{{head()}
            if ($role === '4') {{
                $rows = $this->m_depot->autretridepot($this->entreprise->ekey, $gid, $atr, $comp, $date1, $date2, $gen, $nm);
            }} elseif ($role === '18') {{
                $rows = $this->m_depot->adautretridepot($this->entreprise->ekey, $gid, $atr, $comp, $date1, $date2, $gen, $nm);
            }} else {{
                $rows = $this->m_depot->autretridepot_adjoint($this->entreprise->ekey, $gid, $comp, $atr, $date1, $date2, $typ, $gen, $nm);
            }}
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
                    'genre' => isset($depot->genre_depot) ? (string) $depot->genre_depot : '',
                    'nom' => isset($depot->nom_pre) ? (string) $depot->nom_pre : '',
                    'montant' => $mt,
                    'commentaire' => isset($depot->commentaire_depot) ? (string) $depot->commentaire_depot : '',
                );
                $total += $mt;
            }}
            $qs = http_build_query(array_filter(array(
                'datedebut' => $date1, 'datefin' => $date2, 'type' => $typ, 'genre' => $gen,
                'nom' => $nm, '_compag' => $comp, 'gareconnect' => $gid, {QS}
            )));
            return array(
                'titre' => 'ETATS DES DEPOTS DE ' . $cieNom . ' ' . $garNom . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes, 'total' => $total, 'filters_qs' => $qs, {RETOUR}
                'columns' => array(
                    array('key' => 'date', 'label' => 'Date', 'align' => 'left'),
                    array('key' => 'type', 'label' => 'Type', 'align' => 'left'),
                    array('key' => 'genre', 'label' => 'Genre', 'align' => 'left'),
                    array('key' => 'nom', 'label' => 'Nom', 'align' => 'left'),
                    array('key' => 'montant', 'label' => 'Total', 'align' => 'right', 'money' => true),
                    array('key' => 'commentaire', 'label' => 'Commentaire', 'align' => 'left'),
                ),
                'export_base' => site_url('Rapport/autredepot_export/' . rawurlencode($ckey)),
            );
        }}
{pub('autredepot', 'États des autres dépôts', '_autredepot_payload')}
'''

# Recap helpers
RECAP_HEAD = """
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $date1 = trim((string) $this->input->get_post('datedebut'));
            $date2 = trim((string) $this->input->get_post('datefin'));
            $typ = trim((string) $this->input->get_post('type'));
            $gen = trim((string) $this->input->get_post('genre'));
            $nm = trim((string) $this->input->get_post('nom'));
            $comp = trim((string) $this->input->get_post('_compag'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgar'));
            $this->_assert_cashbox_recap_filters($this->entreprise->ekey, $date1, $date2, $comp, $gid);
            $consultedCashbox = $this->_secured_consulted_cashbox_operator($this->entreprise->ekey);
            $role = (string) $this->session->agent->userole;
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            $ngrd = $this->m_gare_depart->getno($gid);
            $garNom = ($ngrd && isset($ngrd->garenom)) ? $ngrd->garenom : $gid;
            list($days, $days1) = $this->_recap_title_dates($date1, $date2);
"""

code_recaptrecette = f'''
        protected function _recaptrecette_payload($ckey)
        {{{RECAP_HEAD}
            $uopera = roleattribut_guard_post_hint($this->entreprise->ekey);
            if ($consultedCashbox !== null) {{
                $uopera = $consultedCashbox;
            }}
            if ($consultedCashbox !== null) {{
                $rows = $this->m_recette->valdtrirecette($this->entreprise->ekey, $comp, $gid, $uopera, $date1, $date2, $typ, $gen, $nm);
            }} elseif (recette_role_is_saisie($role)) {{
                $rows = $this->m_recette->trirecette_adjoint($this->entreprise->ekey, $gid, $uopera, $date1, $date2, $comp, $typ, $gen, $nm);
            }} elseif (recette_role_is_validateur_adjoint($role)) {{
                $rows = $this->m_recette->valdtrirecettead($this->entreprise->ekey, $comp, $gid, $uopera, $date1, $date2, $typ, $gen, $nm);
            }} elseif ($role === '1' || $role === '2') {{
                $rows = $this->m_recette->valdtrirecettead($this->entreprise->ekey, $comp, $gid, $uopera, $date1, $date2, $typ, $gen, $nm);
            }} else {{
                $uopera = trim((string) $this->input->get_post('useropered'));
                if ($consultedCashbox !== null) {{
                    $uopera = $consultedCashbox;
                }}
                $rows = $this->m_recette->valdtrirecette($this->entreprise->ekey, $comp, $gid, $uopera, $date1, $date2, $typ, $gen, $nm);
            }}
            if (!is_array($rows)) {{
                $rows = array();
            }}
            $cai = $this->m_compte_user->cpuseres($uopera);
            $caiLabel = ($cai && isset($cai->first_name)) ? (trim($cai->first_name . ' ' . $cai->last_name)) : '';
            $lignes = array();
            $total = 0.0;
            foreach ($rows as $rect) {{
                $mt = isset($rect->montant_recet) ? (float) $rect->montant_recet : 0.0;
                $lignes[] = array(
                    'date' => isset($rect->date_recet) ? (string) $rect->date_recet : '',
                    'type' => isset($rect->type_recet) ? (string) $rect->type_recet : '',
                    'genre' => isset($rect->type_personnel) ? (string) $rect->type_personnel : '',
                    'nom' => isset($rect->nom) ? (string) $rect->nom : '',
                    'commentaire' => isset($rect->commentaire_recet) ? (string) $rect->commentaire_recet : '',
                    'montant' => $mt,
                );
                $total += $mt;
            }}
            $qs = http_build_query(array_filter(array(
                'datedebut' => $date1, 'datefin' => $date2, 'type' => $typ, 'genre' => $gen,
                'nom' => $nm, '_compag' => $comp, 'departgar' => $gid, 'useropered' => $uopera, {QS}
            )));
            $titre = 'ETATS DES RECETTES DE ' . $cieNom . ' ' . $garNom . ' DU ' . $days . ' AU ' . $days1;
            if ($caiLabel !== '') {{
                $titre .= ' — CAISSE DE ' . $caiLabel;
            }}
            return array(
                'titre' => $titre,
                'lignes' => $lignes, 'total' => $total, 'filters_qs' => $qs, {RETOUR}
                'columns' => array(
                    array('key' => 'date', 'label' => 'Date', 'align' => 'left'),
                    array('key' => 'type', 'label' => 'Type', 'align' => 'left'),
                    array('key' => 'genre', 'label' => 'Genre', 'align' => 'left'),
                    array('key' => 'nom', 'label' => 'Nom', 'align' => 'left'),
                    array('key' => 'commentaire', 'label' => 'Commentaire', 'align' => 'left'),
                    array('key' => 'montant', 'label' => 'Total', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/recaptrecette_export/' . rawurlencode($ckey)),
            );
        }}
{pub('recaptrecette', 'Récap recettes caisse', '_recaptrecette_payload')}
'''

code_recaptdepense = f'''
        protected function _recaptdepense_payload($ckey)
        {{{RECAP_HEAD}
            $uopera = roleattribut_guard_post_hint($this->entreprise->ekey);
            if ($uopera === null || $uopera === '') {{
                $uopera = trim((string) $this->input->get_post('useropered'));
            }}
            if ($consultedCashbox !== null) {{
                $uopera = $consultedCashbox;
            }}
            if ($consultedCashbox !== null) {{
                $rows = $this->m_depense->valdtridepense($this->entreprise->ekey, $comp, $gid, $uopera, $date1, $date2, $typ, $gen, $nm);
            }} elseif (recette_role_is_saisie($role)) {{
                $rows = $this->m_depense->tridepense_adjoint($this->entreprise->ekey, $gid, $uopera, $date1, $date2, $comp, $typ, $gen, $nm);
            }} elseif (recette_role_is_validateur_adjoint($role)) {{
                $rows = $this->m_depense->adtridepense($this->entreprise->ekey, $gid, $uopera, $comp, $date1, $date2, $gen, $nm);
            }} elseif ($role === '1' || $role === '2') {{
                $rows = $this->m_depense->valdtridepensead($this->entreprise->ekey, $comp, $gid, $uopera, $date1, $date2, $typ, $gen, $nm);
            }} else {{
                $uopera = trim((string) $this->input->get_post('useropered'));
                if ($consultedCashbox !== null) {{
                    $uopera = $consultedCashbox;
                }}
                $rows = $this->m_depense->valdtridepense($this->entreprise->ekey, $comp, $gid, $uopera, $date1, $date2, $typ, $gen, $nm);
            }}
            if (!is_array($rows)) {{
                $rows = array();
            }}
            $cai = $this->m_compte_user->cpuseres($uopera);
            $caiLabel = ($cai && isset($cai->first_name)) ? (trim($cai->first_name . ' ' . $cai->last_name)) : '';
            $lignes = array();
            $total = 0.0;
            foreach ($rows as $depen) {{
                $mt = isset($depen->montant_depens) ? (float) $depen->montant_depens : 0.0;
                $lignes[] = array(
                    'date' => isset($depen->date_depens) ? (string) $depen->date_depens : '',
                    'type' => isset($depen->type_depense) ? (string) $depen->type_depense : '',
                    'genre' => isset($depen->genre_depens) ? (string) $depen->genre_depens : '',
                    'nom' => isset($depen->nom_perso) ? (string) $depen->nom_perso : '',
                    'commentaire' => isset($depen->commentaire) ? (string) $depen->commentaire : '',
                    'motif' => isset($depen->motif) ? (string) $depen->motif : '',
                    'montant' => $mt,
                );
                $total += $mt;
            }}
            $qs = http_build_query(array_filter(array(
                'datedebut' => $date1, 'datefin' => $date2, 'type' => $typ, 'genre' => $gen,
                'nom' => $nm, '_compag' => $comp, 'departgar' => $gid, 'useropered' => $uopera, {QS}
            )));
            $titre = 'ETATS DES DEPENSES DE ' . $cieNom . ' ' . $garNom . ' DU ' . $days . ' AU ' . $days1;
            if ($caiLabel !== '') {{
                $titre .= ' — CAISSE DE ' . $caiLabel;
            }}
            return array(
                'titre' => $titre,
                'lignes' => $lignes, 'total' => $total, 'filters_qs' => $qs, {RETOUR}
                'columns' => array(
                    array('key' => 'date', 'label' => 'Date', 'align' => 'left'),
                    array('key' => 'type', 'label' => 'Type', 'align' => 'left'),
                    array('key' => 'genre', 'label' => 'Genre', 'align' => 'left'),
                    array('key' => 'nom', 'label' => 'Nom', 'align' => 'left'),
                    array('key' => 'commentaire', 'label' => 'Commentaire', 'align' => 'left'),
                    array('key' => 'motif', 'label' => 'Motif', 'align' => 'left'),
                    array('key' => 'montant', 'label' => 'Total', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/recaptdepense_export/' . rawurlencode($ckey)),
            );
        }}
{pub('recaptdepense', 'Récap dépenses caisse', '_recaptdepense_payload')}
'''

code_recaptautredepense = f'''
        protected function _recaptautredepense_payload($ckey)
        {{{RECAP_HEAD}
            $uopera = trim((string) $this->input->get_post('useropered'));
            if ($consultedCashbox !== null) {{
                $uopera = $consultedCashbox;
            }}
            if ($consultedCashbox !== null) {{
                $rows = $this->m_depense->valdautretridepense($this->entreprise->ekey, $comp, $gid, $uopera, $date1, $date2, $typ, $gen, $nm);
            }} elseif ($role === '1' || $role === '2') {{
                $rows = $this->m_depense->valdautretridepensead($this->entreprise->ekey, $comp, $gid, $uopera, $date1, $date2, $typ, $gen, $nm);
            }} else {{
                $rows = $this->m_depense->valdautretridepense($this->entreprise->ekey, $comp, $gid, $uopera, $date1, $date2, $typ, $gen, $nm);
            }}
            if (!is_array($rows)) {{
                $rows = array();
            }}
            $cai = $this->m_compte_user->cpuseres($uopera);
            $caiLabel = ($cai && isset($cai->first_name)) ? (trim($cai->first_name . ' ' . $cai->last_name)) : '';
            $lignes = array();
            $total = 0.0;
            foreach ($rows as $depen) {{
                $mt = isset($depen->montant_depens) ? (float) $depen->montant_depens : 0.0;
                $lignes[] = array(
                    'date' => isset($depen->date_depens) ? (string) $depen->date_depens : '',
                    'type' => isset($depen->type_depense) ? (string) $depen->type_depense : '',
                    'genre' => isset($depen->genre_depens) ? (string) $depen->genre_depens : '',
                    'nom' => isset($depen->nom_perso) ? (string) $depen->nom_perso : '',
                    'montant' => $mt,
                    'commentaire' => isset($depen->commentaire) ? (string) $depen->commentaire : '',
                    'motif' => isset($depen->motif) ? (string) $depen->motif : '',
                );
                $total += $mt;
            }}
            $qs = http_build_query(array_filter(array(
                'datedebut' => $date1, 'datefin' => $date2, 'type' => $typ, 'genre' => $gen,
                'nom' => $nm, '_compag' => $comp, 'departgar' => $gid, 'useropered' => $uopera, {QS}
            )));
            $titre = 'ETATS DES DEPENSES ' . $garNom . ' DU ' . $days . ' AU ' . $days1;
            if ($caiLabel !== '') {{
                $titre .= ' — CAISSE DE ' . $caiLabel;
            }}
            return array(
                'titre' => $titre,
                'lignes' => $lignes, 'total' => $total, 'filters_qs' => $qs, {RETOUR}
                'columns' => array(
                    array('key' => 'date', 'label' => 'Date', 'align' => 'left'),
                    array('key' => 'type', 'label' => 'Type', 'align' => 'left'),
                    array('key' => 'genre', 'label' => 'Genre', 'align' => 'left'),
                    array('key' => 'nom', 'label' => 'Nom', 'align' => 'left'),
                    array('key' => 'montant', 'label' => 'Total', 'align' => 'right', 'money' => true),
                    array('key' => 'commentaire', 'label' => 'Commentaire', 'align' => 'left'),
                    array('key' => 'motif', 'label' => 'Motif', 'align' => 'left'),
                ),
                'export_base' => site_url('Rapport/recaptautredepense_export/' . rawurlencode($ckey)),
            );
        }}
{pub('recaptautredepense', 'Récap autres dépenses caisse', '_recaptautredepense_payload')}
'''

code_recaptdepot = f'''
        protected function _recaptdepot_payload($ckey)
        {{{RECAP_HEAD}
            $uopera = roleattribut_guard_post_hint($this->entreprise->ekey);
            if ($uopera === null || $uopera === '') {{
                $uopera = trim((string) $this->input->get_post('useropered'));
            }}
            if ($consultedCashbox !== null) {{
                $uopera = $consultedCashbox;
            }}
            if ($consultedCashbox !== null) {{
                $rows = $this->m_depot->valdtridepot($this->entreprise->ekey, $gid, $uopera, $date1, $date2, $comp, $typ, $gen, $nm);
            }} elseif (recette_role_is_saisie($role)) {{
                $rows = $this->m_depot->tridepot_adjoint($this->entreprise->ekey, $gid, $comp, $uopera, $date1, $date2, $typ, $gen, $nm);
            }} elseif (recette_role_is_validateur_adjoint($role)) {{
                $rows = $this->m_depot->adtridepot($this->entreprise->ekey, $gid, $uopera, $date1, $date2, $comp, $gen, $nm);
            }} elseif ($role === '1' || $role === '2') {{
                $rows = $this->m_depot->tridepotadmin($this->entreprise->ekey, $gid, $date1, $date2, $typ, $gen, $nm, $comp);
            }} else {{
                $uopera = trim((string) $this->input->get_post('useropered'));
                if ($consultedCashbox !== null) {{
                    $uopera = $consultedCashbox;
                }}
                $rows = $this->m_depot->valdtridepot($this->entreprise->ekey, $gid, $uopera, $date1, $date2, $comp, $typ, $gen, $nm);
            }}
            if (!is_array($rows)) {{
                $rows = array();
            }}
            $cai = $this->m_compte_user->cpuseres($uopera);
            $caiLabel = ($cai && isset($cai->first_name)) ? (trim($cai->first_name . ' ' . $cai->last_name)) : '';
            $lignes = array();
            $total = 0.0;
            foreach ($rows as $depot) {{
                $mt = isset($depot->montant_depot) ? (float) $depot->montant_depot : 0.0;
                $lignes[] = array(
                    'date' => isset($depot->datedepot) ? (string) $depot->datedepot : '',
                    'type' => isset($depot->type_depot) ? (string) $depot->type_depot : '',
                    'genre' => isset($depot->type_personnel) ? (string) $depot->type_personnel : '',
                    'nom' => isset($depot->nom_pre) ? (string) $depot->nom_pre : '',
                    'commentaire' => isset($depot->commentaire_depot) ? (string) $depot->commentaire_depot : '',
                    'montant' => $mt,
                );
                $total += $mt;
            }}
            $qs = http_build_query(array_filter(array(
                'datedebut' => $date1, 'datefin' => $date2, 'type' => $typ, 'genre' => $gen,
                'nom' => $nm, '_compag' => $comp, 'departgar' => $gid, 'useropered' => $uopera, {QS}
            )));
            $titre = 'ETATS DES DEPOTS DE ' . $cieNom . ' ' . $garNom . ' DU ' . $days . ' AU ' . $days1;
            if ($caiLabel !== '') {{
                $titre .= ' — CAISSE DE ' . $caiLabel;
            }}
            return array(
                'titre' => $titre,
                'lignes' => $lignes, 'total' => $total, 'filters_qs' => $qs, {RETOUR}
                'columns' => array(
                    array('key' => 'date', 'label' => 'Date', 'align' => 'left'),
                    array('key' => 'type', 'label' => 'Type', 'align' => 'left'),
                    array('key' => 'genre', 'label' => 'Genre', 'align' => 'left'),
                    array('key' => 'nom', 'label' => 'Nom', 'align' => 'left'),
                    array('key' => 'commentaire', 'label' => 'Commentaire', 'align' => 'left'),
                    array('key' => 'montant', 'label' => 'Total', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/recaptdepot_export/' . rawurlencode($ckey)),
            );
        }}
{pub('recaptdepot', 'Récap dépôts caisse', '_recaptdepot_payload')}
'''

code_recaptautredepot = f'''
        protected function _recaptautredepot_payload($ckey)
        {{{RECAP_HEAD}
            $uopera = trim((string) $this->input->get_post('useropered'));
            if ($consultedCashbox !== null) {{
                $uopera = $consultedCashbox;
            }}
            $rows = $this->m_depot->valdautretridepot($this->entreprise->ekey, $gid, $uopera, $date1, $date2, $comp, $typ, $gen, $nm);
            if (!is_array($rows)) {{
                $rows = array();
            }}
            $cai = $this->m_compte_user->cpuseres($uopera);
            $caiLabel = ($cai && isset($cai->first_name)) ? (trim($cai->first_name . ' ' . $cai->last_name)) : '';
            $lignes = array();
            $total = 0.0;
            foreach ($rows as $depot) {{
                $mt = isset($depot->montant_depot) ? (float) $depot->montant_depot : 0.0;
                $lignes[] = array(
                    'date' => isset($depot->datedepot) ? (string) $depot->datedepot : '',
                    'type' => isset($depot->type_depot) ? (string) $depot->type_depot : '',
                    'genre' => isset($depot->genre_depot) ? (string) $depot->genre_depot : '',
                    'nom' => isset($depot->nom_pre) ? (string) $depot->nom_pre : '',
                    'montant' => $mt,
                    'commentaire' => isset($depot->commentaire_depot) ? (string) $depot->commentaire_depot : '',
                );
                $total += $mt;
            }}
            $qs = http_build_query(array_filter(array(
                'datedebut' => $date1, 'datefin' => $date2, 'type' => $typ, 'genre' => $gen,
                'nom' => $nm, '_compag' => $comp, 'departgar' => $gid, 'useropered' => $uopera, {QS}
            )));
            $titre = 'ETATS DES DEPOTS ' . $garNom . ' DU ' . $days . ' AU ' . $days1;
            if ($caiLabel !== '') {{
                $titre .= ' — CAISSE DE ' . $caiLabel;
            }}
            return array(
                'titre' => $titre,
                'lignes' => $lignes, 'total' => $total, 'filters_qs' => $qs, {RETOUR}
                'columns' => array(
                    array('key' => 'date', 'label' => 'Date', 'align' => 'left'),
                    array('key' => 'type', 'label' => 'Type', 'align' => 'left'),
                    array('key' => 'genre', 'label' => 'Genre', 'align' => 'left'),
                    array('key' => 'nom', 'label' => 'Nom', 'align' => 'left'),
                    array('key' => 'montant', 'label' => 'Total', 'align' => 'right', 'money' => true),
                    array('key' => 'commentaire', 'label' => 'Commentaire', 'align' => 'left'),
                ),
                'export_base' => site_url('Rapport/recaptautredepot_export/' . rawurlencode($ckey)),
            );
        }}
{pub('recaptautredepot', 'Récap autres dépôts caisse', '_recaptautredepot_payload')}
'''

code_versementbanq = f'''
        protected function _versementbanq_payload($ckey)
        {{{head()}
            $rows = $this->m_versements->versembanque($this->entreprise->ekey, $comp, $gid, $atr, $date1, $date2, $gen, $nm);
            if (!is_array($rows)) {{
                $rows = array();
            }}
            $lignes = array();
            $total = 0.0;
            foreach ($rows as $row) {{
                $mt = isset($row->montant_verser) ? (float) $row->montant_verser : 0.0;
                $lignes[] = array(
                    'date' => isset($row->date_versement) ? (string) $row->date_versement : '',
                    'type' => isset($row->type_versement) ? (string) $row->type_versement : '',
                    'genre' => isset($row->genre_depot) ? (string) $row->genre_depot : '',
                    'nom' => isset($row->nom_beneficiaire) ? (string) $row->nom_beneficiaire : '',
                    'bordereau' => isset($row->bordereau_verser) ? (string) $row->bordereau_verser : '',
                    'commentaire' => isset($row->commentaire) ? (string) $row->commentaire : '',
                    'montant' => $mt,
                );
                $total += $mt;
            }}
            $qs = http_build_query(array_filter(array(
                'datedebut' => $date1, 'datefin' => $date2, 'type' => $typ, 'genre' => $gen,
                'nom' => $nm, '_compag' => $comp, 'gareconnect' => $gid, {QS}
            )));
            return array(
                'titre' => 'ETATS DES VERSEMENTS BANQUE DE ' . $cieNom . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes, 'total' => $total, 'filters_qs' => $qs, {RETOUR}
                'columns' => array(
                    array('key' => 'date', 'label' => 'Date', 'align' => 'left'),
                    array('key' => 'type', 'label' => 'Type', 'align' => 'left'),
                    array('key' => 'genre', 'label' => 'Genre', 'align' => 'left'),
                    array('key' => 'nom', 'label' => 'Nom', 'align' => 'left'),
                    array('key' => 'bordereau', 'label' => 'Bordereau', 'align' => 'left'),
                    array('key' => 'commentaire', 'label' => 'Commentaire', 'align' => 'left'),
                    array('key' => 'montant', 'label' => 'Total', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/versementbanq_export/' . rawurlencode($ckey)),
            );
        }}
{pub('versementbanq', 'États versements banque', '_versementbanq_payload')}
'''

code_versementfour = f'''
        protected function _versementfour_payload($ckey)
        {{{head(gid='departgar').replace(
            "$gid = trim((string) $this->input->get_post('departgar'));",
            "$gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgar'));",
        )}
            $rows = $this->m_versements->versemfourni($this->entreprise->ekey, $comp, $gid, $atr, $date1, $date2, $gen, $nm);
            if (!is_array($rows)) {{
                $rows = array();
            }}
            $lignes = array();
            $total = 0.0;
            foreach ($rows as $row) {{
                $mt = isset($row->montant_verser) ? (float) $row->montant_verser : 0.0;
                $lignes[] = array(
                    'date' => isset($row->date_versement) ? (string) $row->date_versement : '',
                    'type' => isset($row->type_versement) ? (string) $row->type_versement : '',
                    'genre' => isset($row->genre_depense) ? (string) $row->genre_depense : '',
                    'nom' => isset($row->nom_beneficiaire) ? (string) $row->nom_beneficiaire : '',
                    'bordereau' => isset($row->bordereau_verser) ? (string) $row->bordereau_verser : '',
                    'commentaire' => isset($row->commentaire) ? (string) $row->commentaire : '',
                    'montant' => $mt,
                );
                $total += $mt;
            }}
            $qs = http_build_query(array_filter(array(
                'datedebut' => $date1, 'datefin' => $date2, 'type' => $typ, 'genre' => $gen,
                'nom' => $nm, '_compag' => $comp, 'departgar' => $gid, {QS}
            )));
            return array(
                'titre' => 'ETATS DES VERSEMENTS FOURNISSEUR DE ' . $cieNom . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes, 'total' => $total, 'filters_qs' => $qs, {RETOUR}
                'columns' => array(
                    array('key' => 'date', 'label' => 'Date', 'align' => 'left'),
                    array('key' => 'type', 'label' => 'Type', 'align' => 'left'),
                    array('key' => 'genre', 'label' => 'Genre', 'align' => 'left'),
                    array('key' => 'nom', 'label' => 'Nom', 'align' => 'left'),
                    array('key' => 'bordereau', 'label' => 'Bordereau', 'align' => 'left'),
                    array('key' => 'commentaire', 'label' => 'Commentaire', 'align' => 'left'),
                    array('key' => 'montant', 'label' => 'Total', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/versementfour_export/' . rawurlencode($ckey)),
            );
        }}
{pub('versementfour', 'États versements fournisseur', '_versementfour_payload')}
'''

replacements = [
    ('recette', code_recette),
    ('recettecr', code_recettecr),
    ('depense', code_depense),
    ('depot', code_depot),
    ('autredepot', code_autredepot),
    ('recaptrecette', code_recaptrecette),
    ('recaptdepense', code_recaptdepense),
    ('recaptautredepense', code_recaptautredepense),
    ('recaptdepot', code_recaptdepot),
    ('recaptautredepot', code_recaptautredepot),
    ('versementbanq', code_versementbanq),
    ('versementfour', code_versementfour),
]

for name, code in replacements:
    src = replace_method(src, name, code)
    print('OK', name)

path.write_text(src, encoding='utf-8')
print('Wrote', path)
