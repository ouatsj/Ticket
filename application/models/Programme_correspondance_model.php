<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Lien ops : départ direct (principal) ↔ départ correspondance (suite)
 * + départ dérivé Banfora→hub créé à la gare de départ, sièges partagés avec suite.
 */
class Programme_correspondance_model extends CI_Model
{
    protected $table = 'programme_correspondance';

    /** Marge min (minutes) pour proposer une suite après le départ principal. */
    protected $marge_min = 30;

    /** Écart max (jours) suite après le principal : 0 = même jour, 1 = lendemain. */
    protected $suite_jours_max = 1;

    public function __construct()
    {
        parent::__construct();
        if (!isset($this->m_programme)) {
            $this->load->model('Programme_model', 'm_programme');
        }
        if (!isset($this->m_itineraire_etape)) {
            $this->load->model('Itineraire_etape_model', 'm_itineraire_etape');
        }
    }

    public function get_by_principal($code_progr)
    {
        $code = trim((string) $code_progr);
        if ($code === '') {
            return null;
        }
        return $this->db->query(
            "SELECT * FROM {$this->table} WHERE code_progr_principal = ? LIMIT 1",
            array($code)
        )->row();
    }

    public function get_by_any_code($code_progr)
    {
        $code = trim((string) $code_progr);
        if ($code === '') {
            return null;
        }
        return $this->db->query(
            "SELECT * FROM {$this->table}
             WHERE code_progr_principal = ?
                OR code_progr_suite = ?
                OR code_progr_derive = ?
             LIMIT 1",
            array($code, $code, $code)
        )->row();
    }

    /**
     * Index des liens pour une liste de code_progr (affichage liste gare).
     * Clé = code_progr → role (principal|suite|derive) + libellés liés.
     *
     * @param string[] $codes
     * @return array
     */
    public function index_for_codes(array $codes)
    {
        $clean = array();
        foreach ($codes as $c) {
            $c = trim((string) $c);
            if ($c !== '') {
                $clean[$c] = true;
            }
        }
        $codes = array_keys($clean);
        if (empty($codes)) {
            return array();
        }

        $ph = implode(',', array_fill(0, count($codes), '?'));
        $rows = $this->db->query(
            "SELECT * FROM {$this->table}
             WHERE code_progr_principal IN ($ph)
                OR code_progr_suite IN ($ph)
                OR code_progr_derive IN ($ph)",
            array_merge($codes, $codes, $codes)
        )->result();

        if (empty($rows)) {
            return array();
        }

        $allCodes = array();
        foreach ($rows as $lien) {
            foreach (array('code_progr_principal', 'code_progr_suite', 'code_progr_derive') as $f) {
                if (!empty($lien->$f)) {
                    $allCodes[$lien->$f] = true;
                }
            }
        }
        $details = $this->prog_details_map(array_keys($allCodes));

        $out = array();
        foreach ($rows as $lien) {
            $meta = array(
                'lien' => $lien,
                'principal' => isset($details[$lien->code_progr_principal]) ? $details[$lien->code_progr_principal] : null,
                'suite' => isset($details[$lien->code_progr_suite]) ? $details[$lien->code_progr_suite] : null,
                'derive' => (!empty($lien->code_progr_derive) && isset($details[$lien->code_progr_derive]))
                    ? $details[$lien->code_progr_derive] : null,
            );
            if (!empty($lien->code_progr_principal)) {
                $out[$lien->code_progr_principal] = array_merge($meta, array('role' => 'principal'));
            }
            if (!empty($lien->code_progr_suite)) {
                $out[$lien->code_progr_suite] = array_merge($meta, array('role' => 'suite'));
            }
            if (!empty($lien->code_progr_derive)) {
                $out[$lien->code_progr_derive] = array_merge($meta, array('role' => 'derive'));
            }
        }
        return $out;
    }

    /**
     * @param string[] $codes
     * @return array code_progr => object{code_progr,nom_ligne,heure,gareidentif,date_progr}
     */
    public function prog_details_map(array $codes)
    {
        $clean = array();
        foreach ($codes as $c) {
            $c = trim((string) $c);
            if ($c !== '') {
                $clean[$c] = true;
            }
        }
        $codes = array_keys($clean);
        if (empty($codes)) {
            return array();
        }
        $ph = implode(',', array_fill(0, count($codes), '?'));
        $rows = $this->db->query(
            "SELECT pr.code_progr, pr.gareidentif, pr.date_progr, pr.depart_code,
                    h.heure, lg.nom_ligne, lg.ident_ligne AS ligne_id
             FROM programme pr
             JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
             JOIN heures h ON lh.heure_identif = h.id_heure
             JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
             WHERE pr.code_progr IN ($ph)",
            $codes
        )->result();
        $map = array();
        foreach ($rows as $r) {
            $map[$r->code_progr] = $r;
        }
        return $map;
    }

    /**
     * Libellé court pour badge UI.
     */
    public function badge_html(array $entry)
    {
        $role = isset($entry['role']) ? $entry['role'] : '';
        $suite = isset($entry['suite']) ? $entry['suite'] : null;
        $principal = isset($entry['principal']) ? $entry['principal'] : null;
        $derive = isset($entry['derive']) ? $entry['derive'] : null;
        $h = function ($p) {
            if (!$p) {
                return '';
            }
            $heure = isset($p->heure) ? substr((string) $p->heure, 0, 5) : '';
            $nom = isset($p->nom_ligne) ? $p->nom_ligne : '';
            return trim($nom . ' ' . $heure);
        };

        if ($role === 'principal') {
            $label = 'Correspondance : ' . $h($suite);
            if ($derive) {
                $label .= ' · hub ' . $h($derive);
            }
            return '<br><small class="badge badge-info" title="Lien correspondance">'
                . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</small>';
        }
        if ($role === 'derive') {
            $label = 'Dérivé · miroir sièges occupés ' . $h($suite);
            return '<br><small class="badge badge-primary" title="Sièges = déjà vendus sur le départ de correspondance">'
                . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</small>';
        }
        if ($role === 'suite') {
            $label = 'Utilisé par ' . $h($principal);
            if ($principal && !empty($principal->gareidentif)) {
                $label .= ' (' . $principal->gareidentif . ')';
            }
            return '<br><small class="badge badge-warning" title="Départ utilisé en correspondance par une autre gare">'
                . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</small>';
        }
        return '';
    }

    /**
     * Codes programmes qui partagent le même stock de sièges (dérivé ↔ suite).
     * @return string[]
     */
    public function codes_sieges_partages($code_progr)
    {
        $lien = $this->get_by_any_code($code_progr);
        if (!$lien) {
            return array(trim((string) $code_progr));
        }
        $codes = array();
        if (!empty($lien->code_progr_suite)) {
            $codes[] = (string) $lien->code_progr_suite;
        }
        if (!empty($lien->code_progr_derive)) {
            $codes[] = (string) $lien->code_progr_derive;
        }
        $codes = array_values(array_unique(array_filter($codes)));
        return !empty($codes) ? $codes : array(trim((string) $code_progr));
    }

    /**
     * Si $code_progr est le départ dérivé d'un lien : infos miroir sièges.
     * Dispo dérivé = occupés(suite) \ occupés(dérivé).
     *
     * @return array{suite:string,derive:string}|null
     */
    public function miroir_derive_info($code_progr)
    {
        $code = trim((string) $code_progr);
        if ($code === '') {
            return null;
        }
        $lien = $this->get_by_any_code($code);
        if (!$lien
            || empty($lien->code_progr_derive)
            || empty($lien->code_progr_suite)
            || (string) $lien->code_progr_derive !== $code) {
            return null;
        }
        return array(
            'suite' => (string) $lien->code_progr_suite,
            'derive' => (string) $lien->code_progr_derive,
        );
    }

    /**
     * Détail programme + ligne + heure.
     */
    public function prog_detail($ekey, $code_progr)
    {
        return $this->db->query(
            "SELECT pr.*, lh.id_ligneheure, lh.ligne_id, h.heure, h.id_heure,
                    lg.nom_ligne, lg.gaexp_lg, lg.gadest_lg
             FROM programme pr
             JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
             JOIN heures h ON lh.heure_identif = h.id_heure
             JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
             JOIN gare_exp ge ON lg.gaexp_lg = ge.code_gaexp
             JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
             JOIN entreprise e ON c.id_entrep = e.id_entreprise
             WHERE e.ekey = ?
               AND pr.code_progr = ?
             LIMIT 1",
            array($ekey, $code_progr)
        )->row();
    }

    /**
     * Ligne dérivée (ex. BAN3-BOB32) : 1re étape déclarative, sinon gaexp_principal → gaexp_suite.
     */
    public function resolve_ligne_derive($ekey, $principal, $suite)
    {
        $etapes = $this->m_itineraire_etape->get_by_parent($ekey, $principal->ligne_id);
        if (!empty($etapes)) {
            foreach ($etapes as $et) {
                $code = isset($et->code_itineraires) ? (string) $et->code_itineraires : '';
                if ($code !== '' && $code !== (string) $principal->ligne_id && $code !== (string) $suite->ligne_id) {
                    // 1re jambe typique BAN→BOB
                    $row = $this->db->query(
                        "SELECT ident_ligne, nom_ligne, gaexp_lg, gadest_lg FROM lignes WHERE ident_ligne = ? LIMIT 1",
                        array($code)
                    )->row();
                    if ($row && $row->gaexp_lg === $principal->gaexp_lg) {
                        return $row;
                    }
                }
            }
            // Fallback : première étape dont gaexp = principal
            foreach ($etapes as $et) {
                $code = isset($et->code_itineraires) ? (string) $et->code_itineraires : '';
                if ($code === '') {
                    continue;
                }
                $row = $this->db->query(
                    "SELECT ident_ligne, nom_ligne, gaexp_lg, gadest_lg FROM lignes WHERE ident_ligne = ? LIMIT 1",
                    array($code)
                )->row();
                if ($row && $row->gaexp_lg === $principal->gaexp_lg && $row->ident_ligne !== $principal->ligne_id) {
                    return $row;
                }
            }
        }

        // Fallback géo : ligne même gare départ → gare exp de la suite (ville hub)
        $hub = (string) $suite->gaexp_lg;
        $row = $this->db->query(
            "SELECT lg.ident_ligne, lg.nom_ligne, lg.gaexp_lg, lg.gadest_lg
             FROM lignes lg
             JOIN gare_dest gd ON gd.code_gadest = lg.gadest_lg
             JOIN gare_exp ge ON ge.code_gaexp = ?
             WHERE lg.gaexp_lg = ?
               AND gd.id_villega = ge.id_villegd
             ORDER BY lg.ident_ligne ASC
             LIMIT 1",
            array($hub, $principal->gaexp_lg)
        )->row();
        if ($row) {
            return $row;
        }

        // Dernier recours : gadest commence comme hub (BOB32 vs BOB1)
        $prefix = preg_replace('/[0-9]+$/', '', $hub);
        if ($prefix === '') {
            $prefix = $hub;
        }
        return $this->db->query(
            "SELECT ident_ligne, nom_ligne, gaexp_lg, gadest_lg FROM lignes
             WHERE gaexp_lg = ?
               AND gadest_lg LIKE ?
             ORDER BY ident_ligne ASC
             LIMIT 1",
            array($principal->gaexp_lg, $prefix . '%')
        )->row();
    }

    /**
     * id_ligneheure pour la ligne dérivée à la même heure que le principal (sinon première heure active).
     */
    public function resolve_id_heur_derive($ligne_id, $heure_principal)
    {
        $row = $this->db->query(
            "SELECT lh.id_ligneheure
             FROM ligne_heure lh
             JOIN heures h ON lh.heure_identif = h.id_heure
             WHERE lh.ligne_id = ?
               AND lh.actif_lh = 1
               AND h.h_active = 1
               AND h.heure = ?
             LIMIT 1",
            array($ligne_id, $heure_principal)
        )->row();
        if ($row) {
            return (int) $row->id_ligneheure;
        }
        $row = $this->db->query(
            "SELECT lh.id_ligneheure
             FROM ligne_heure lh
             JOIN heures h ON lh.heure_identif = h.id_heure
             WHERE lh.ligne_id = ?
               AND lh.actif_lh = 1
               AND h.h_active = 1
             ORDER BY h.heure ASC
             LIMIT 1",
            array($ligne_id)
        )->row();
        return $row ? (int) $row->id_ligneheure : 0;
    }

    protected function heure_to_minutes($h)
    {
        $h = trim((string) $h);
        if ($h === '') {
            return 0;
        }
        $parts = explode(':', $h);
        $hh = isset($parts[0]) ? (int) $parts[0] : 0;
        $mm = isset($parts[1]) ? (int) $parts[1] : 0;
        return ($hh * 60) + $mm;
    }

    /**
     * Minutes absolues (depuis epoch) pour comparer date+heure entre jours.
     * @param string $date Y-m-d
     * @param string $heure H:i[:s]
     * @return int
     */
    protected function datetime_to_minutes($date, $heure)
    {
        $day = strtotime(trim((string) $date) . ' 00:00:00');
        if ($day === false) {
            return $this->heure_to_minutes($heure);
        }
        return (int) floor($day / 60) + $this->heure_to_minutes($heure);
    }

    /**
     * @param string $date Y-m-d
     * @param int $days
     * @return string Y-m-d
     */
    protected function date_plus_jours($date, $days)
    {
        $t = strtotime(trim((string) $date) . ' 00:00:00');
        if ($t === false) {
            return (string) $date;
        }
        return date('Y-m-d', strtotime('+' . (int) $days . ' day', $t));
    }

    /**
     * Dates suite autorisées : J .. J+suite_jours_max.
     * @param string $date_principal
     * @return string[]
     */
    protected function dates_suite_autorisees($date_principal)
    {
        $out = array();
        $max = max(0, (int) $this->suite_jours_max);
        for ($i = 0; $i <= $max; $i++) {
            $out[] = $this->date_plus_jours($date_principal, $i);
        }
        return $out;
    }

    /**
     * Suite dans [J, J+max] et datetime >= principal + marge.
     * @return string|null code erreur ou null si OK
     */
    protected function valider_dates_suite($principal, $suite)
    {
        $tP = strtotime(trim((string) $principal->date_progr) . ' 00:00:00');
        $tS = strtotime(trim((string) $suite->date_progr) . ' 00:00:00');
        if ($tP === false || $tS === false) {
            return 'dates_invalides';
        }
        $delta = (int) round(($tS - $tP) / 86400);
        if ($delta < 0 || $delta > (int) $this->suite_jours_max) {
            return 'dates_hors_plage';
        }
        $minOk = $this->datetime_to_minutes($principal->date_progr, $principal->heure) + $this->marge_min;
        if ($this->datetime_to_minutes($suite->date_progr, $suite->heure) < $minOk) {
            return 'marge_horaire';
        }
        return null;
    }

    /**
     * Suites candidates (programmes déjà créés) pour un principal.
     * Inclut le même jour et le lendemain (correspondance nuit).
     * @return array
     */
    public function suggest_suites($ekey, $code_progr_principal)
    {
        $principal = $this->prog_detail($ekey, $code_progr_principal);
        if (!$principal) {
            return array('ok' => false, 'error' => 'programme_principal_introuvable', 'suggestions' => array());
        }

        $exist = $this->get_by_principal($code_progr_principal);
        if ($exist) {
            return array(
                'ok' => true,
                'already_linked' => true,
                'lien' => $exist,
                'principal' => $this->_public_prog($principal),
                'suggestions' => array(),
            );
        }

        $datesOk = $this->dates_suite_autorisees($principal->date_progr);
        $phDates = implode(',', array_fill(0, count($datesOk), '?'));
        $minAbs = $this->datetime_to_minutes($principal->date_progr, $principal->heure) + $this->marge_min;

        // Lignes suite : 2e étape déclarative, sinon mêmes destinations OD via hub
        $suite_lignes = array();
        $etapes = $this->m_itineraire_etape->get_by_parent($ekey, $principal->ligne_id);
        if (!empty($etapes)) {
            foreach ($etapes as $et) {
                $code = isset($et->code_itineraires) ? (string) $et->code_itineraires : '';
                if ($code === '' || $code === (string) $principal->ligne_id) {
                    continue;
                }
                $lgRow = $this->db->query(
                    "SELECT gaexp_lg FROM lignes WHERE ident_ligne = ? LIMIT 1",
                    array($code)
                )->row();
                // Suite = jambe qui part d'une autre gare (hub), pas la 1re jambe locale
                if ($lgRow && (string) $lgRow->gaexp_lg !== (string) $principal->gaexp_lg) {
                    $suite_lignes[$code] = true;
                }
            }
        }
        if (empty($suite_lignes)) {
            // Autre gare exp, même gadest, sur J / J+1
            $paramsLignes = array_merge(array($ekey), $datesOk, array(
                $principal->gadest_lg,
                $principal->gaexp_lg,
                $principal->ligne_id,
            ));
            $rows = $this->db->query(
                "SELECT DISTINCT lg.ident_ligne
                 FROM programme pr
                 JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                 JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                 JOIN gare_exp ge ON lg.gaexp_lg = ge.code_gaexp
                 JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
                 JOIN entreprise e ON c.id_entrep = e.id_entreprise
                 WHERE e.ekey = ?
                   AND pr.date_progr IN ($phDates)
                   AND pr.statut_prog = 'actif'
                   AND pr.actif_prog = 0
                   AND lg.gadest_lg = ?
                   AND lg.gaexp_lg <> ?
                   AND lg.ident_ligne <> ?",
                $paramsLignes
            )->result();
            foreach ($rows as $r) {
                $suite_lignes[$r->ident_ligne] = true;
            }
        }

        if (empty($suite_lignes)) {
            return array(
                'ok' => true,
                'principal' => $this->_public_prog($principal),
                'suggestions' => array(),
                'message' => 'aucune_ligne_suite',
            );
        }

        $ids = array_keys($suite_lignes);
        $ph = implode(',', array_fill(0, count($ids), '?'));
        $params = array_merge(array($ekey), $datesOk, $ids);
        $cands = $this->db->query(
            "SELECT pr.code_progr, pr.gareidentif, pr.date_progr, pr.intervalle1, pr.intervalle2, pr.depart_code,
                    lh.id_ligneheure, lh.ligne_id, h.heure, lg.nom_ligne, lg.gaexp_lg, lg.gadest_lg
             FROM programme pr
             JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
             JOIN heures h ON lh.heure_identif = h.id_heure
             JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
             JOIN gare_exp ge ON lg.gaexp_lg = ge.code_gaexp
             JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
             JOIN entreprise e ON c.id_entrep = e.id_entreprise
             WHERE e.ekey = ?
               AND pr.date_progr IN ($phDates)
               AND pr.statut_prog = 'actif'
               AND pr.actif_prog = 0
               AND lh.ligne_id IN ($ph)
             ORDER BY pr.date_progr ASC, h.heure ASC, pr.code_progr ASC",
            $params
        )->result();

        $suggestions = array();
        foreach ($cands as $c) {
            if ($this->datetime_to_minutes($c->date_progr, $c->heure) < $minAbs) {
                continue;
            }
            $hhmm = substr((string) $c->heure, 0, 5);
            $isLendemain = ((string) $c->date_progr !== (string) $principal->date_progr);
            $label = $c->nom_ligne . ' ' . $c->date_progr . ' ' . $hhmm;
            if ($isLendemain) {
                $label .= ' (lendemain)';
            }
            $suggestions[] = array(
                'code_progr' => $c->code_progr,
                'gareidentif' => $c->gareidentif,
                'ligne_id' => $c->ligne_id,
                'nom_ligne' => $c->nom_ligne,
                'heure' => $c->heure,
                'date_progr' => $c->date_progr,
                'lendemain' => $isLendemain,
                'intervalle1' => (int) $c->intervalle1,
                'intervalle2' => (int) $c->intervalle2,
                'portee_ids' => $this->portee_ids_programme($c->code_progr),
                'label' => $label,
            );
        }

        return array(
            'ok' => true,
            'principal' => $this->_public_prog($principal),
            'suggestions' => $suggestions,
            'suite_jours_max' => (int) $this->suite_jours_max,
        );
    }

    protected function _public_prog($p)
    {
        return array(
            'code_progr' => $p->code_progr,
            'depart_code' => $p->depart_code,
            'ligne_id' => $p->ligne_id,
            'nom_ligne' => $p->nom_ligne,
            'heure' => $p->heure,
            'date_progr' => $p->date_progr,
            'gareidentif' => $p->gareidentif,
            'intervalle1' => (int) $p->intervalle1,
            'intervalle2' => (int) $p->intervalle2,
        );
    }

    /**
     * Applique une portée sous-gare à un programme (legacy idsousgare_prog + programme_sousgare).
     * @param string $code_progr
     * @param string $gareidentif
     * @param array $selected ids sous-gares (vide = toute portée)
     * @return array{ok:bool,error?:string}
     */
    public function appliquer_portee($code_progr, $gareidentif, array $selected)
    {
        $code = trim((string) $code_progr);
        $gare = trim((string) $gareidentif);
        if ($code === '' || $gare === '') {
            return array('ok' => false, 'error' => 'portee_params');
        }
        $selected = $this->m_programme->normaliser_selection_sousgares($selected);
        $totalRow = $this->db->query(
            "SELECT COUNT(*) AS n FROM sousgare WHERE gareprinceid = ?",
            array($gare)
        )->row();
        $total = $totalRow ? (int) $totalRow->n : 0;

        if (!$this->m_programme->portee_selection_autorisee($code, $selected, $total)) {
            return array('ok' => false, 'error' => 'portee_ventes_bloque');
        }

        $idsous = $this->m_programme->idsousgare_prog_depuis_selection($selected, $total);
        $this->m_programme->update($code, array('idsousgare_prog' => $idsous));
        $this->m_programme->sync_portee_sousgares($code, $selected, $total);
        return array('ok' => true);
    }

    /**
     * Ids sous-gares de la portée actuelle d'un programme.
     * Vide = toute portée (gare).
     * @return int[]
     */
    public function portee_ids_programme($code_progr)
    {
        $code = trim((string) $code_progr);
        if ($code === '') {
            return array();
        }
        $rows = $this->db->query(
            "SELECT idsousgare FROM programme_sousgare WHERE code_progr = ? ORDER BY idsousgare ASC",
            array($code)
        )->result();
        if (!empty($rows)) {
            $out = array();
            foreach ($rows as $r) {
                $out[] = (int) $r->idsousgare;
            }
            return $out;
        }
        $pr = $this->db->query(
            "SELECT idsousgare_prog FROM programme WHERE code_progr = ? LIMIT 1",
            array($code)
        )->row();
        if ($pr && $pr->idsousgare_prog !== null && $pr->idsousgare_prog !== '' && (int) $pr->idsousgare_prog > 0) {
            return array((int) $pr->idsousgare_prog);
        }
        return array();
    }

    /**
     * Liste sous-gares d'une gare (pour UI portée lien).
     * @return array
     */
    public function list_sousgares_gare($gareidentif)
    {
        $gare = trim((string) $gareidentif);
        if ($gare === '') {
            return array();
        }
        $rows = $this->db->query(
            "SELECT idsousgare, nomsousgare, gareprinceid
             FROM sousgare
             WHERE gareprinceid = ?
             ORDER BY nomsousgare ASC, idsousgare ASC",
            array($gare)
        )->result();
        $out = array();
        foreach ($rows as $r) {
            $out[] = array(
                'idsousgare' => (int) $r->idsousgare,
                'nomsousgare' => $r->nomsousgare,
                'gareprinceid' => $r->gareprinceid,
            );
        }
        return $out;
    }

    /**
     * Crée le lien + programme dérivé.
     * - categori = principal (même bus Banfora)
     * - intervalles = suite (plage sièges Bobo)
     * - vente dérivé : miroir des sièges déjà occupés sur la suite
     * - options:
     *   - scope_banfora: int[] sous-gares Banfora (vide = toute portée)
     *   - apply_principal: bool (défaut true) — B
     *   - apply_derive: bool (défaut true) — A
     *   - scope_bobo: int[] sous-gares Bobo (vide = toute portée)
     *   - apply_suite: bool (défaut true)
     * @return array
     */
    public function link($ekey, $code_progr_principal, $code_progr_suite, array $options = array())
    {
        $principal = $this->prog_detail($ekey, $code_progr_principal);
        $suite = $this->prog_detail($ekey, $code_progr_suite);
        if (!$principal || !$suite) {
            return array('ok' => false, 'error' => 'programme_introuvable');
        }
        $errDates = $this->valider_dates_suite($principal, $suite);
        if ($errDates !== null) {
            return array('ok' => false, 'error' => $errDates);
        }
        if ($this->get_by_principal($code_progr_principal)) {
            return array('ok' => false, 'error' => 'deja_lie');
        }

        $applyPrincipal = !isset($options['apply_principal']) || $options['apply_principal'];
        $applyDerive = !isset($options['apply_derive']) || $options['apply_derive'];
        $applySuite = !isset($options['apply_suite']) || $options['apply_suite'];
        $scopeBanfora = isset($options['scope_banfora']) && is_array($options['scope_banfora'])
            ? $options['scope_banfora'] : array();
        $scopeBobo = isset($options['scope_bobo']) && is_array($options['scope_bobo'])
            ? $options['scope_bobo'] : array();
        // true si le client a explicitement choisi une portée (même vide = toute portée)
        $hasScopeBanfora = !empty($options['has_scope_banfora']);
        $hasScopeBobo = !empty($options['has_scope_bobo']);

        $ligneDerive = $this->resolve_ligne_derive($ekey, $principal, $suite);
        if (!$ligneDerive) {
            return array('ok' => false, 'error' => 'ligne_derive_introuvable');
        }

        $idHeur = $this->resolve_id_heur_derive($ligneDerive->ident_ligne, $principal->heure);
        if ($idHeur <= 0) {
            return array('ok' => false, 'error' => 'heure_derive_introuvable');
        }

        // Déjà un dérivé lié ? ou programme BAN-BOB même jour/heure existant réutilisable
        $existDerive = $this->db->query(
            "SELECT pr.code_progr FROM programme pr
             JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
             WHERE pr.gareidentif = ?
               AND pr.date_progr = ?
               AND lh.ligne_id = ?
               AND lh.id_ligneheure = ?
               AND pr.statut_prog = 'actif'
               AND pr.actif_prog = 0
             LIMIT 1",
            array($principal->gareidentif, $principal->date_progr, $ligneDerive->ident_ligne, $idHeur)
        )->row();

        $codeDerive = null;
        if ($existDerive) {
            $codeDerive = $existDerive->code_progr;
            // Intervalles = suite (stock Bobo) ; catégorie = principal (bus Banfora)
            $this->m_programme->update($codeDerive, array(
                'intervalle1' => (int) $suite->intervalle1,
                'intervalle2' => (int) $suite->intervalle2,
                'categori' => $principal->categori,
            ));
        } else {
            $today = mdate('%Y-%m-%d', now('UTC'));
            $gd = $principal->gareidentif;
            $compter = $this->db->query(
                "SELECT COUNT(code_progr) AS id FROM programme WHERE createdatepr = ? AND gareidentif = ?",
                array($today, $gd)
            )->row();
            $gd4 = ($gd === 'OUA12') ? 'WUA12' : $gd;
            $pcd = mdate('%y%m%d', now('UTC')) . $gd4 . ((int) $compter->id + 1);

            $heureRow = $this->db->query(
                "SELECT h.heure FROM ligne_heure lh JOIN heures h ON lh.heure_identif = h.id_heure WHERE lh.id_ligneheure = ? LIMIT 1",
                array($idHeur)
            )->row();
            $suheure = $heureRow ? $heureRow->heure : $principal->heure;

            $arrayprog = array(
                'code_progr' => $pcd,
                'depart_code' => $principal->depart_code,
                'id_heur' => $idHeur,
                'gareidentif' => $gd,
                'idsousgare_prog' => null,
                'typetarif' => $principal->typetarif,
                'categori' => $principal->categori,
                'intervalle1' => (int) $suite->intervalle1,
                'intervalle2' => (int) $suite->intervalle2,
                'dateheure_prog' => $principal->date_progr . '-' . $suheure,
                'date_progr' => $principal->date_progr,
                'createdatepr' => $today,
                'createdpg_at' => now('UTC'),
            );
            $ok = $this->m_programme->create($arrayprog);
            if ($ok === null || $ok === FALSE) {
                return array('ok' => false, 'error' => 'echec_creation_derive');
            }
            $codeDerive = $pcd;
        }

        // Portées sous-gares
        $porteeErrors = array();
        if ($hasScopeBanfora) {
            if ($applyDerive && $codeDerive) {
                $r = $this->appliquer_portee($codeDerive, $principal->gareidentif, $scopeBanfora);
                if (empty($r['ok'])) {
                    $porteeErrors[] = 'derive:' . (isset($r['error']) ? $r['error'] : 'fail');
                }
            }
            if ($applyPrincipal) {
                $r = $this->appliquer_portee($principal->code_progr, $principal->gareidentif, $scopeBanfora);
                if (empty($r['ok'])) {
                    $porteeErrors[] = 'principal:' . (isset($r['error']) ? $r['error'] : 'fail');
                }
            }
        } elseif ($codeDerive) {
            // Compat: sans choix UI, recopier la portée actuelle du principal sur le dérivé
            $psg = $this->db->query(
                "SELECT idsousgare FROM programme_sousgare WHERE code_progr = ?",
                array($principal->code_progr)
            )->result();
            $selected = array();
            if (!empty($psg)) {
                foreach ($psg as $r) {
                    $selected[] = (int) $r->idsousgare;
                }
            } elseif (!empty($principal->idsousgare_prog)) {
                $selected[] = (int) $principal->idsousgare_prog;
            }
            $this->appliquer_portee($codeDerive, $principal->gareidentif, $selected);
        }

        if ($hasScopeBobo && $applySuite) {
            $r = $this->appliquer_portee($suite->code_progr, $suite->gareidentif, $scopeBobo);
            if (empty($r['ok'])) {
                $porteeErrors[] = 'suite:' . (isset($r['error']) ? $r['error'] : 'fail');
            }
        }

        $this->db->insert($this->table, array(
            'code_progr_principal' => $principal->code_progr,
            'code_progr_suite' => $suite->code_progr,
            'code_progr_derive' => $codeDerive,
            'ekey' => $ekey,
        ));

        return array(
            'ok' => true,
            'lien' => $this->get_by_principal($principal->code_progr),
            'derive' => $this->prog_detail($ekey, $codeDerive),
            'suite' => $this->_public_prog($suite),
            'principal' => $this->_public_prog($principal),
            'portee_warnings' => $porteeErrors,
        );
    }

    /**
     * Codes du lien à surveiller pour verrouillage (principal + suite + dérivé).
     * @param object $lien
     * @return string[]
     */
    public function codes_ventes_lien($lien)
    {
        if (!$lien) {
            return array();
        }
        $codes = array();
        foreach (array('code_progr_principal', 'code_progr_suite', 'code_progr_derive') as $f) {
            if (!empty($lien->$f)) {
                $codes[] = (string) $lien->$f;
            }
        }
        return array_values(array_unique($codes));
    }

    /**
     * True si au moins une vente active (passager) sur principal, suite ou dérivé.
     * @param object|string $lien_or_principal
     * @return array{verrouille:bool,nb_ventes:int,codes:string[]}
     */
    public function statut_verrouillage($lien_or_principal)
    {
        $lien = is_object($lien_or_principal)
            ? $lien_or_principal
            : $this->get_by_principal($lien_or_principal);
        if (!$lien) {
            return array('verrouille' => false, 'nb_ventes' => 0, 'codes' => array());
        }
        $codes = $this->codes_ventes_lien($lien);
        if (empty($codes)) {
            return array('verrouille' => false, 'nb_ventes' => 0, 'codes' => array());
        }
        $ph = implode(',', array_fill(0, count($codes), '?'));
        $row = $this->db->query(
            "SELECT COUNT(*) AS nb FROM passager p
             WHERE p.code_pro IN ($ph)
               AND p.actif_pas = 0",
            $codes
        )->row();
        $nb = $row ? (int) $row->nb : 0;
        return array(
            'verrouille' => ($nb > 0),
            'nb_ventes' => $nb,
            'codes' => $codes,
        );
    }

    /**
     * Supprime le lien. Ne désactive pas les programmes (principal / suite / dérivé restent).
     * Interdit s'il existe une vente active sur principal, suite ou dérivé.
     */
    public function unlink($code_progr_principal)
    {
        $lien = $this->get_by_principal($code_progr_principal);
        if (!$lien) {
            return array('ok' => false, 'error' => 'lien_introuvable');
        }
        $lock = $this->statut_verrouillage($lien);
        if (!empty($lock['verrouille'])) {
            return array(
                'ok' => false,
                'error' => 'lien_avec_ventes',
                'message' => 'Impossible de supprimer le lien : des ventes existent déjà sur ce départ lié.',
                'nb_ventes' => $lock['nb_ventes'],
            );
        }
        $this->db->where('code_progr_principal', $code_progr_principal)->delete($this->table);
        return array('ok' => true, 'removed' => $lien);
    }
}
