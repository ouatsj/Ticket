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

if (!function_exists('ticket_est_gratuit')) {
    /**
     * True si le montant encaissé est nul (0, "0", "0.00", 0.0, …).
     * Remplace les tests fragiles === '0.00' des vues ticket.
     */
    function ticket_est_gratuit($prixvente)
    {
        if ($prixvente === null || $prixvente === '') {
            return false;
        }
        return abs((float) $prixvente) < 0.005;
    }
}

if (!function_exists('ticket_libelle_prix')) {
    /**
     * Libellé d'impression : TICKET GRATUIT si 0 F, sinon montant FCFA.
     *
     * @param mixed $prixvente
     * @param int|float $multiplicateur 2 pour aller-retour (prix × 2)
     * @param string $suffix Ex. FCFA
     */
    function ticket_libelle_prix($prixvente, $multiplicateur = 1, $suffix = 'FCFA')
    {
        if (ticket_est_gratuit($prixvente)) {
            return 'TICKET GRATUIT';
        }
        $m = (float) $multiplicateur;
        if ($m < 1) {
            $m = 1;
        }
        return number_format((float) $prixvente * $m, 0, '', ' ') . $suffix;
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

if (!function_exists('ticket_destination_label')) {
    /**
     * Libellé destination ticket : escale (nom_dest_vente) si présente, sinon terminus.
     *
     * @param object|array|null $row
     * @param string            $fallback
     * @return string
     */
    function ticket_destination_label($row, $fallback = '')
    {
        if (is_array($row)) {
            $row = (object) $row;
        }
        if (!$row || !is_object($row)) {
            return (string) $fallback;
        }

        if (!empty($row->nom_dest_vente)) {
            return (string) $row->nom_dest_vente;
        }
        if (!empty($row->nom_gadest)) {
            return (string) $row->nom_gadest;
        }
        if (!empty($row->arrivee_escale)) {
            return (string) $row->arrivee_escale;
        }

        return (string) $fallback;
    }
}

if (!function_exists('ticket_axe_label')) {
    /**
     * Libellé d'axe (ex. OUAGA-BOUSSE) : remplace le terminus par l'escale vendue si présente.
     *
     * @param object|array|null $row
     * @param string            $fallback
     * @return string
     */
    function ticket_axe_label($row, $fallback = '')
    {
        if (is_array($row)) {
            $row = (object) $row;
        }
        if (!$row || !is_object($row)) {
            return (string) $fallback;
        }

        $destVente = isset($row->nom_dest_vente) ? trim((string) $row->nom_dest_vente) : '';
        if ($destVente !== '') {
            // Préférer le préfixe de nom_ligne (ex. OUAGA) pour coller à l'affichage habituel.
            if (!empty($row->nom_ligne) && strpos((string) $row->nom_ligne, '-') !== false) {
                $parts = explode('-', (string) $row->nom_ligne, 2);
                return trim($parts[0]) . '-' . $destVente;
            }
            if (!empty($row->nom_gaep)) {
                return trim((string) $row->nom_gaep) . '-' . $destVente;
            }
            return $destVente;
        }

        if (!empty($row->nom_ligne)) {
            return (string) $row->nom_ligne;
        }

        $depart = !empty($row->nom_gaep) ? trim((string) $row->nom_gaep) : '';
        $dest = ticket_destination_label($row, '');
        if ($depart !== '' && $dest !== '') {
            return $depart . '-' . $dest;
        }

        return (string) $fallback;
    }
}

if (!function_exists('ticket_sg_label')) {
    /**
     * Nom de sous-gare pour ticket : getgar si présent, sinon la ligne passager.
     *
     * @param object|null $ressougare
     * @param object|null $item
     * @return string
     */
    function ticket_sg_label($ressougare, $item = null)
    {
        if (is_object($ressougare) && !empty($ressougare->nomsousgare)) {
            return (string) $ressougare->nomsousgare;
        }
        if (is_object($item) && !empty($item->nomsousgare)) {
            return (string) $item->nomsousgare;
        }
        return '';
    }
}

if (!function_exists('ticket_print_ctx')) {
    /**
     * Heure / sous-gare / n° bus pour une vue ticket. Ne lève jamais sur getgar null.
     *
     * @param object|null $item
     * @param object|null $bus_stop
     * @return array{ok:bool,heures:string,sg_label:string,day:string,nge:string,nbus:string,dtoday:string}
     */
    function ticket_print_ctx($item, $bus_stop = null)
    {
        $out = array(
            'ok' => false,
            'heures' => '',
            'sg_label' => '',
            'day' => '',
            'nge' => '',
            'nbus' => '',
            'dtoday' => '',
        );
        if (!$item || !is_object($item)) {
            return $out;
        }
        $out['ok'] = true;
        $out['heures'] = isset($item->heure) ? (string) $item->heure : '';
        $out['sg_label'] = isset($item->nomsousgare) ? (string) $item->nomsousgare : '';

        $CI =& get_instance();
        $ressougare = null;
        if (isset($CI->m_entreprises) && isset($CI->m_gare_depart)
            && isset($CI->session->company->ekey)
            && !empty($item->departclient_idgare)
            && !empty($item->ident_ligne)
            && !empty($item->id_ligneheure)
        ) {
            $ent = $CI->m_entreprises->get_key($CI->session->company->ekey);
            if ($ent) {
                $gare_ref = '';
                if (is_object($bus_stop) && !empty($bus_stop->idengare)) {
                    $gare_ref = $bus_stop->idengare;
                } elseif (!empty($item->code_gaexp)) {
                    $gare_ref = $item->code_gaexp;
                }
                if ($gare_ref !== '') {
                    $ressougare = $CI->m_gare_depart->getgar(
                        $ent->id_entreprise,
                        $gare_ref,
                        $item->departclient_idgare,
                        $item->ident_ligne,
                        $item->id_ligneheure
                    );
                }
            }
        }

        if (is_object($ressougare) && !empty($ressougare->possitiongare) && $out['heures'] !== '') {
            $g = explode(':', $out['heures']);
            if (isset($g[0], $g[1])) {
                $base = ((int) $g[0] * 60) + (int) $g[1];
                $delta = isset($ressougare->minutetemps) ? (int) $ressougare->minutetemps : 0;
                if ($ressougare->possitiongare === 'Avant') {
                    $gt = $base - $delta;
                } else {
                    $gt = $base + $delta;
                }
                if ($gt < 0) {
                    $gt += 24 * 60;
                }
                $heur = ($gt / 60);
                $secondes = (int) round($gt % 60);
                $out['heures'] = sprintf('%02d:%02d', $heur, $secondes);
            }
        }
        $out['sg_label'] = ticket_sg_label($ressougare, $item);

        if (!empty($item->code_progr)) {
            $out['nge'] = substr((string) $item->code_progr, 6, 6);
        }

        $gid = isset($item->gareidentif) ? (string) $item->gareidentif : '';
        $dep = isset($item->depart_code) ? (string) $item->depart_code : '';
        if ($gid !== '' && $dep !== '') {
            $hay = ($gid === 'OUA12') ? ('O' . substr($dep, 3)) : $dep;
            $d = explode($gid, $hay);
            $out['nbus'] = isset($d[1]) ? (string) $d[1] : '';
        }

        if (!empty($item->date_progr)) {
            $dat = explode('-', (string) $item->date_progr);
            if (count($dat) >= 3) {
                $out['day'] = $dat[2] . '-' . $dat[1] . '-' . $dat[0];
            }
        }

        $dats = (date('H') === '00') ? '01:00:00' : date('H:i:s');
        $key = function_exists('mdate') ? mdate('%Y-%m-%d', now()) : date('Y-m-d');
        $out['dtoday'] = $key . ' à ' . $dats;

        return $out;
    }
}

if (!function_exists('ticket_est_reporte')) {
    /**
     * Ticket issu d’une reprogrammation (non reprogrammable à nouveau).
     *
     * @param object|array|null $item
     * @return bool
     */
    function ticket_est_reporte($item)
    {
        if (is_array($item)) {
            $st = isset($item['statut_reprog']) ? $item['statut_reprog'] : null;
        } elseif (is_object($item)) {
            $st = isset($item->statut_reprog) ? $item->statut_reprog : null;
        } else {
            return false;
        }
        return (string) $st === 'repor';
    }
}

if (!function_exists('ticket_emis_html')) {
    /**
     * Ligne « emis : … » (+ NON REPROGRAMMABLE si reporté).
     *
     * @param object|null $item
     * @param string $dtoday
     * @return string HTML <tr>…
     */
    function ticket_emis_html($item, $dtoday)
    {
        $suffix = ticket_est_reporte($item) ? ' NON REPROGRAMMABLE' : '';
        return '<tr><td style="font-size: 15px;">emis : '
            . htmlspecialchars((string) $dtoday, ENT_QUOTES, 'UTF-8')
            . $suffix . '</td></tr>';
    }
}

if (!function_exists('ticket_stub_non_reprog_html')) {
    /**
     * Ligne stub « NON REPROGRAMMABLE » (vide si ticket encore reprogrammable).
     *
     * @param object|null $item
     * @return string
     */
    function ticket_stub_non_reprog_html($item)
    {
        if (!ticket_est_reporte($item)) {
            return '';
        }
        return '<tr><td>NON REPROGRAMMABLE</td></tr>';
    }
}

if (!function_exists('ticket_heure_hhmm')) {
    /**
     * Formate une heure DB (H:i:s ou H:i) en HH:MM pour l'impression.
     *
     * @param mixed $heure
     * @return string
     */
    function ticket_heure_hhmm($heure)
    {
        if ($heure === null || $heure === '') {
            return '';
        }
        $parts = explode(':', (string) $heure);
        if (count($parts) < 2) {
            return (string) $heure;
        }
        return sprintf('%02d:%02d', (int) $parts[0], (int) $parts[1]);
    }
}

if (!function_exists('ticket_heure_depart_affiche')) {
    /**
     * Heure de départ affichée sur ticket (avec décalage sous-gare si connu).
     * Si aucune position : heure programme brute — ne jamais laisser vide.
     *
     * @param mixed $heure_raw heures.heure
     * @param object|null $ressougare résultat getgar()
     * @return string HH:MM
     */
    function ticket_heure_depart_affiche($heure_raw, $ressougare = null)
    {
        $fallback = ticket_heure_hhmm($heure_raw);
        if ($fallback === '') {
            return '';
        }
        if (!$ressougare || empty($ressougare->possitiongare)) {
            return $fallback;
        }

        $parts = explode(':', (string) $heure_raw);
        $base = ((int) $parts[0] * 60) + (int) $parts[1];
        $delta = isset($ressougare->minutetemps) ? (int) $ressougare->minutetemps : 0;
        $pos = (string) $ressougare->possitiongare;

        if ($pos === 'Avant') {
            $gt = $base - $delta;
        } else {
            // Maintenant / Apres / autre
            $gt = $base + $delta;
        }
        if ($gt < 0) {
            $gt = 0;
        }
        return sprintf('%02d:%02d', (int) floor($gt / 60), (int) ($gt % 60));
    }
}
