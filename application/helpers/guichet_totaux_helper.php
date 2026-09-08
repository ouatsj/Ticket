<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Cache / refresh du SOLDE guichet.
 *
 * Règle métier : cumul agent (roleattribut) toutes gares, ventes non arrêtées.
 * Pas de filtre gare sur compteur() — le paramètre $g est volontairement ignoré.
 *
 * SOLDE tickets (rôles 1/6/10) = aller + retour + escale.
 * Bagage reste un badge séparé (« RECETTE BAGAGE »).
 */

if (!function_exists('guichet_totaux_load_cache_helper')) {
    function guichet_totaux_load_cache_helper()
    {
        if (!function_exists('app_cache_delete')) {
            $CI =& get_instance();
            $CI->load->helper('app_cache');
        }
    }
}

if (!function_exists('guichet_totaux_today_key')) {
    function guichet_totaux_today_key()
    {
        return mdate('%Y-%m-%d', now('UTC'));
    }
}

if (!function_exists('guichet_totaux_cache_key')) {
    /**
     * @param string   $ekey
     * @param int      $roleattribut
     * @param string|null $day Y-m-d (UTC)
     */
    function guichet_totaux_cache_key($ekey, $roleattribut, $day = null)
    {
        if ($day === null) {
            $day = guichet_totaux_today_key();
        }

        return 'guichet_totaux_' . $ekey . '_' . (int) $roleattribut . '_' . $day;
    }
}

if (!function_exists('guichet_totaux_cache_key_legacy')) {
    /** Ancienne clé (avec gare) — invalidée aussi pour transition. */
    function guichet_totaux_cache_key_legacy($ekey, $roleattribut, $gid, $day = null)
    {
        if ($day === null) {
            $day = guichet_totaux_today_key();
        }

        return 'guichet_totaux_' . $ekey . '_' . (int) $roleattribut . '_' . (int) $gid . '_' . $day;
    }
}

if (!function_exists('guichet_totaux_resolve_ekey')) {
    function guichet_totaux_resolve_ekey($ekey = null)
    {
        if ($ekey !== null && $ekey !== '') {
            return (string) $ekey;
        }
        $CI =& get_instance();
        if ($CI->session->userdata('company') && !empty($CI->session->company->ekey)) {
            return (string) $CI->session->company->ekey;
        }

        return '';
    }
}

if (!function_exists('guichet_totaux_cache_invalidate')) {
    /**
     * Invalide le cache SOLDE pour un opérateur (roleattribut).
     *
     * @param int|string  $roleattribut
     * @param string|null $ekey
     * @param int|string|null $gid gare (optionnel, pour clé legacy)
     */
    function guichet_totaux_cache_invalidate($roleattribut, $ekey = null, $gid = null)
    {
        $ra = (int) $roleattribut;
        if ($ra <= 0) {
            return;
        }

        guichet_totaux_load_cache_helper();
        $ekey = guichet_totaux_resolve_ekey($ekey);
        if ($ekey === '') {
            return;
        }

        $today = guichet_totaux_today_key();
        $yesterday = date('Y-m-d', strtotime($today . ' -1 day'));

        foreach (array($today, $yesterday) as $day) {
            app_cache_delete(guichet_totaux_cache_key($ekey, $ra, $day));
            if ($gid !== null && (int) $gid > 0) {
                app_cache_delete(guichet_totaux_cache_key_legacy($ekey, $ra, $gid, $day));
            }
        }

        // Si gare inconnue : tenter guser session agent.
        if (($gid === null || (int) $gid <= 0)) {
            $CI =& get_instance();
            if ($CI->session->userdata('agent') && !empty($CI->session->agent->guser)) {
                $sg = (int) $CI->session->agent->guser;
                if ($sg > 0) {
                    app_cache_delete(guichet_totaux_cache_key_legacy($ekey, $ra, $sg, $today));
                    app_cache_delete(guichet_totaux_cache_key_legacy($ekey, $ra, $sg, $yesterday));
                }
            }
        }
    }
}

if (!function_exists('guichet_totaux_cache_invalidate_from_row')) {
    /**
     * Invalide à partir d’un tableau create/update (idcptuser / cptus / idoperabagage / iduseescal).
     *
     * @param array $data
     * @param array $fallbackKeys colonnes alternatives (ex. before update)
     */
    function guichet_totaux_cache_invalidate_from_row(array $data, array $fallbackKeys = array())
    {
        $keys = array('idcptuser', 'cptus', 'idoperabagage', 'iduseescal', 'roleattribut');
        $ra = 0;
        foreach ($keys as $k) {
            if (!empty($data[$k]) && (int) $data[$k] > 0) {
                $ra = (int) $data[$k];
                break;
            }
        }
        if ($ra <= 0) {
            foreach ($keys as $k) {
                if (!empty($fallbackKeys[$k]) && (int) $fallbackKeys[$k] > 0) {
                    $ra = (int) $fallbackKeys[$k];
                    break;
                }
            }
        }
        if ($ra > 0) {
            guichet_totaux_cache_invalidate($ra);
        }
    }
}

if (!function_exists('guichet_statutvente_heal_incoherent')) {
    /**
     * Corrige les tickets « validés chef » encore ouverts au compteur
     * (is_valdtick/is_valedtick = 1 et statutvente/statvente = 0).
     *
     * @param int|string $roleattribut
     * @return array{passager:int,retour:int}
     */
    function guichet_statutvente_heal_incoherent($roleattribut)
    {
        $ra = (int) $roleattribut;
        $out = array('passager' => 0, 'retour' => 0);
        if ($ra <= 0) {
            return $out;
        }

        $CI =& get_instance();
        $CI->db->query(
            "UPDATE passager
             SET statutvente = 1
             WHERE idcptuser = ?
               AND is_valdtick = 1
               AND statutvente = 0
               AND statut_code = 'vendu'",
            array($ra)
        );
        $out['passager'] = (int) $CI->db->affected_rows();

        $CI->db->query(
            "UPDATE non_passager
             SET statvente = 1
             WHERE cptus = ?
               AND is_valedtick = 1
               AND statvente = 0",
            array($ra)
        );
        $out['retour'] = (int) $CI->db->affected_rows();

        if ($out['passager'] > 0 || $out['retour'] > 0) {
            guichet_totaux_cache_invalidate($ra);
        }

        return $out;
    }
}

if (!function_exists('guichet_totaux_fetch_snapshot')) {
    /**
     * Calcule le snapshot SOLDE (sans lire le cache) et le remet en cache.
     *
     * @return array{solde:float,aller:float,retour:float,bagage:float,escale:float,formatted:string}
     */
    function guichet_totaux_fetch_snapshot($ekey, $roleattribut, $gid = 0)
    {
        guichet_totaux_load_cache_helper();
        $CI =& get_instance();
        $ra = (int) $roleattribut;
        $gid = (int) $gid;

        // Filet DB : is_valdtick=1 + statutvente=0 ne doit jamais rester ouvert.
        guichet_statutvente_heal_incoherent($ra);

        if (!isset($CI->m_passager)) {
            $CI->load->model('Passager_model', 'm_passager');
        }
        if (!isset($CI->m_non_passager)) {
            $CI->load->model('Non_passager_model', 'm_non_passager');
        }
        if (!isset($CI->m_bagage)) {
            $CI->load->model('Bagage_model', 'm_bagage');
        }
        if (!isset($CI->m_escalclients)) {
            $CI->load->model('Escalclients_model', 'm_escalclients');
        }

        $cptaller = $CI->m_passager->compteur($ekey, $ra, $gid);
        $cptretour = $CI->m_non_passager->compteur($ekey, $ra, $gid);
        $recettebagages = $CI->m_bagage->compteur($ekey, $ra, $gid);
        $cptalleresc = $CI->m_escalclients->compteur($ekey, $ra, $gid);
        $cptallercd = $CI->m_passager->compteurcd($ekey, $ra, $gid);
        $cptallerescd = $CI->m_escalclients->compteurcd($ekey, $ra, $gid);
        $recettebagagescd = $CI->m_bagage->compteurcd($ekey, $ra, $gid);

        $aller = ($cptaller && isset($cptaller->total)) ? (float) $cptaller->total : 0.0;
        $retour = ($cptretour && isset($cptretour->totalr)) ? (float) $cptretour->totalr : 0.0;
        $bagage = ($recettebagages && isset($recettebagages->bagtotal)) ? (float) $recettebagages->bagtotal : 0.0;
        $escale = ($cptalleresc && isset($cptalleresc->total)) ? (float) $cptalleresc->total : 0.0;
        // Cumul agent non arrêté : tickets aller/retour + ventes escale (table escalclients).
        $solde = $aller + $retour + $escale;

        $payload = array(
            'cptaller' => $cptaller,
            'cptretour' => $cptretour,
            'recettebagages' => $recettebagages,
            'cptalleresc' => $cptalleresc,
            'cptallercd' => $cptallercd,
            'cptallerescd' => $cptallerescd,
            'recettebagagescd' => $recettebagagescd,
        );
        app_cache_set(guichet_totaux_cache_key($ekey, $ra), $payload, 90);

        return array(
            'solde' => $solde,
            'aller' => $aller,
            'retour' => $retour,
            'bagage' => $bagage,
            'escale' => $escale,
            'formatted' => number_format($solde, 0, '', ' '),
            'bagage_formatted' => number_format($bagage, 0, '', ' '),
            'escale_formatted' => number_format($escale, 0, '', ' '),
        );
    }
}

if (!function_exists('ticket_close_flags_normalize_passager')) {
    /**
     * Empêche is_valdtick=1 avec statutvente=0 (fantôme SOLDE).
     * Si on réouvre le compteur (statutvente=0), remet is_valdtick=0 sauf override explicite.
     *
     * @param array $data
     * @return array
     */
    function ticket_close_flags_normalize_passager(array $data)
    {
        if (isset($data['is_valdtick']) && (int) $data['is_valdtick'] === 1) {
            $data['statutvente'] = 1;
        }
        if (isset($data['actif_pas']) && (int) $data['actif_pas'] === 1
            && !array_key_exists('statutvente', $data)
        ) {
            // Annulation / archive : sortir du compteur SOLDE.
            $data['statutvente'] = 1;
        }
        if (array_key_exists('statutvente', $data)
            && (int) $data['statutvente'] === 0
            && !array_key_exists('is_valdtick', $data)
        ) {
            $data['is_valdtick'] = 0;
        }

        return $data;
    }
}

if (!function_exists('ticket_close_flags_normalize_retour')) {
    /**
     * Même garde-fou pour non_passager (is_valedtick / statvente).
     *
     * @param array $data
     * @return array
     */
    function ticket_close_flags_normalize_retour(array $data)
    {
        if (isset($data['is_valedtick']) && (int) $data['is_valedtick'] === 1) {
            $data['statvente'] = 1;
        }
        if (array_key_exists('statvente', $data)
            && (int) $data['statvente'] === 0
            && !array_key_exists('is_valedtick', $data)
        ) {
            $data['is_valedtick'] = 0;
        }

        return $data;
    }
}
