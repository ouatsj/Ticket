<?php defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('ticket_impression_prix')) {
    /**
     * Prix affiché sur le ticket : prix réellement encaissé (prixvente), pas le tarif catalogue.
     */
    function ticket_impression_prix($row, $fallback = 0)
    {
        if (!$row || !is_object($row)) {
            return $fallback;
        }
        if (isset($row->prixvente) && $row->prixvente !== null && $row->prixvente !== '') {
            return (float) $row->prixvente;
        }
        if (isset($row->prix) && $row->prix !== null && $row->prix !== '') {
            return (float) $row->prix;
        }
        return $fallback;
    }
}

if (!function_exists('ticket_prix_catalogue_rows')) {
    /**
     * Tarifs catalogue actifs pour une ligne_heure + typetarif + gare de départ.
     * Même règles que Tarifications_model::pri (actif_taf=1, datefin valide).
     *
     * @return array
     */
    function ticket_prix_catalogue_rows($ekey, $ligne_heure_id, $typetarif, $gare_depart)
    {
        if ($ekey === '' || $ligne_heure_id === '' || $ligne_heure_id === null || $gare_depart === '' || $gare_depart === null) {
            return array();
        }

        $CI =& get_instance();
        $key = function_exists('mdate') ? mdate('%Y-%m-%d', now()) : date('Y-m-d');
        $ekey = $CI->db->escape_str($ekey);
        $th = $CI->db->escape_str($ligne_heure_id);
        $tf = $CI->db->escape_str($typetarif);
        $gid = $CI->db->escape_str($gare_depart);

        $sql = "SELECT tf.prix, tf.typeclient_id, tf.typetarif_id, tf.id_garedepart
            FROM tarification tf
            JOIN type_client tc ON tf.typeclient_id = tc.idtyp
            JOIN tarifs t ON tf.typetarif_id = t.id_tarifs
            JOIN gare_exp ex ON tf.id_garedepart = ex.code_gaexp
            JOIN ligne_heure lh ON tf.ligne_heure_id = lh.id_ligneheure
            JOIN heures h ON lh.heure_identif = h.id_heure
            JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
            JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
            JOIN entreprise e ON c.id_entrep = e.id_entreprise
            WHERE e.ekey = '{$ekey}'
              AND tf.ligne_heure_id = '{$th}'
              AND ex.code_gaexp = '{$gid}'
              AND t.datefin >= '{$key}'
              AND h.h_active = 1
              AND tf.actif_taf = 1
              AND tf.typetarif_id = '{$tf}'
            ORDER BY CASE WHEN tf.typeclient_id = 1 THEN 0 ELSE 1 END, tf.prix ASC";

        $q = $CI->db->query($sql);
        return $q ? $q->result() : array();
    }
}

if (!function_exists('ticket_prix_gare_ligne')) {
    /**
     * Gare d'expédition catalogue de la ligne (gaexp_lg), fallback si gare programme absente.
     */
    function ticket_prix_gare_ligne($ligne_heure_id)
    {
        if ($ligne_heure_id === '' || $ligne_heure_id === null) {
            return null;
        }
        $CI =& get_instance();
        $th = $CI->db->escape_str($ligne_heure_id);
        $row = $CI->db->query(
            "SELECT lg.gaexp_lg
             FROM ligne_heure lh
             JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
             WHERE lh.id_ligneheure = '{$th}'
             LIMIT 1"
        )->row();

        return ($row && !empty($row->gaexp_lg)) ? $row->gaexp_lg : null;
    }
}

if (!function_exists('ticket_prix_depuis_programme')) {
    /**
     * Force le prix vente selon le programme + gare du programme (pas la gare de session).
     * - Si le prix POST correspond déjà à un tarif catalogue valide → on le conserve
     *   (réduction élève/étudiant/etc. toujours possible).
     * - Sinon → on force le tarif Adulte (typeclient 1) ou le premier tarif trouvé.
     * - Si aucun tarif → on garde le prix POST (ne casse pas les cas limites).
     *
     * @param string      $code_pro
     * @param mixed       $prix_poste
     * @param string|null $gare_depart  override optionnel (sinon programme.gareidentif puis gaexp_lg)
     * @return float|string
     */
    function ticket_prix_depuis_programme($code_pro, $prix_poste, $gare_depart = null)
    {
        if ($code_pro === null || $code_pro === '') {
            return $prix_poste;
        }

        $CI =& get_instance();
        $ekey = null;
        if (isset($CI->session) && isset($CI->session->company) && !empty($CI->session->company->ekey)) {
            $ekey = $CI->session->company->ekey;
        }
        if ($ekey === null) {
            return $prix_poste;
        }

        $cp = $CI->db->escape_str($code_pro);
        $prog = $CI->db->query(
            "SELECT pr.code_progr, pr.id_heur, pr.typetarif, pr.gareidentif, lg.gaexp_lg
             FROM programme pr
             LEFT JOIN ligne_heure lh ON lh.id_ligneheure = pr.id_heur
             LEFT JOIN lignes lg ON lg.ident_ligne = lh.ligne_id
             WHERE pr.code_progr = '{$cp}'
             LIMIT 1"
        )->row();

        if (!$prog || empty($prog->id_heur)) {
            return $prix_poste;
        }

        $tf = ($prog->typetarif !== null && $prog->typetarif !== '') ? $prog->typetarif : 1;
        $gares = array();
        if ($gare_depart !== null && $gare_depart !== '') {
            $gares[] = $gare_depart;
        }
        if (!empty($prog->gareidentif)) {
            $gares[] = $prog->gareidentif;
        }
        if (!empty($prog->gaexp_lg)) {
            $gares[] = $prog->gaexp_lg;
        }
        $gares = array_values(array_unique($gares));

        $rows = array();
        foreach ($gares as $gid) {
            $rows = ticket_prix_catalogue_rows($ekey, $prog->id_heur, $tf, $gid);
            if (!empty($rows)) {
                break;
            }
        }

        if (empty($rows)) {
            return $prix_poste;
        }

        $prix_poste_f = round((float) $prix_poste, 2);
        $valides = array();
        $adulte = null;
        foreach ($rows as $row) {
            $p = round((float) $row->prix, 2);
            $valides[$p] = true;
            if ((int) $row->typeclient_id === 1 && $adulte === null) {
                $adulte = $p;
            }
        }

        if ($prix_poste !== null && $prix_poste !== '' && isset($valides[$prix_poste_f])) {
            return $prix_poste_f;
        }

        if ($adulte !== null) {
            return $adulte;
        }

        return round((float) $rows[0]->prix, 2);
    }
}

if (!function_exists('ticket_impression_prix_row')) {
    function ticket_impression_prix_row($row)
    {
        if (!$row || !is_object($row)) {
            return $row;
        }
        if (isset($row->prixvente) && $row->prixvente !== null && $row->prixvente !== '') {
            $row->prix = (float) $row->prixvente;
        }
        return $row;
    }
}

if (!function_exists('ticket_impression_prix_rows')) {
    function ticket_impression_prix_rows($rows)
    {
        if (!$rows) {
            return $rows;
        }
        foreach ($rows as $row) {
            ticket_impression_prix_row($row);
        }
        return $rows;
    }
}
