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
        if (!empty($lien->code_progr_principal)) {
            $codes[] = (string) $lien->code_progr_principal;
        }
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
                    lg.nom_ligne, lg.gaexp_lg, lg.gadest_lg,
                    ga.id_compaga, ca.nom_compagnie AS nom_compagnie_arrivee,
                    ca.cle_compagnie AS cle_compagnie_arrivee
             FROM programme pr
             JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
             JOIN heures h ON lh.heure_identif = h.id_heure
             JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
             JOIN gare_exp ge ON lg.gaexp_lg = ge.code_gaexp
             JOIN gare_dest ga ON lg.gadest_lg = ga.code_gadest
             JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
             JOIN compagnies ca ON ga.id_compaga = ca.cle_compagnie
             JOIN entreprise e ON c.id_entrep = e.id_entreprise
             WHERE e.ekey = ?
               AND pr.code_progr = ?
             LIMIT 1",
            array($ekey, $code_progr)
        )->row();
    }

    /**
     * Jeton compagnie d'arrivée (VIP, CMT, CBT, CIT…). Strict : VIP ≠ CMT ≠ CBT.
     */
    protected function _token_compagnie($nom, $id = '')
    {
        $blob = strtoupper(trim((string) $nom . ' ' . $id));
        $blob = preg_replace('/[^A-Z0-9]+/', ' ', $blob);
        foreach (array('VIP', 'CMT', 'CBT', 'CIT') as $tok) {
            if (preg_match('/\b' . $tok . '\b/', $blob)) {
                return $tok;
            }
        }
        $nom = strtoupper(trim((string) $nom));
        if ($nom !== '') {
            return $nom;
        }
        return strtoupper(trim((string) $id));
    }

    /**
     * Compagnie d'arrivée à conserver pour le hub dérivé (principal, sinon suite).
     */
    protected function _compagnie_arrivee_preferee($principal, $suite = null)
    {
        $p = isset($principal->id_compaga) ? trim((string) $principal->id_compaga) : '';
        if ($p !== '') {
            return $p;
        }
        if ($suite && isset($suite->id_compaga)) {
            return trim((string) $suite->id_compaga);
        }
        return '';
    }

    protected function _nom_compagnie_preferee($principal, $suite = null)
    {
        $p = isset($principal->nom_compagnie_arrivee) ? trim((string) $principal->nom_compagnie_arrivee) : '';
        if ($p !== '') {
            return $p;
        }
        if ($suite && isset($suite->nom_compagnie_arrivee)) {
            return trim((string) $suite->nom_compagnie_arrivee);
        }
        return '';
    }

    /**
     * Ligne + compagnie d'arrivée.
     */
    protected function _ligne_avec_compagnie($ident_ligne)
    {
        $code = trim((string) $ident_ligne);
        if ($code === '') {
            return null;
        }
        return $this->db->query(
            "SELECT lg.ident_ligne, lg.nom_ligne, lg.gaexp_lg, lg.gadest_lg,
                    ga.id_compaga, ga.id_villega, ga.nom_gadest,
                    ca.nom_compagnie AS nom_compagnie_arrivee
             FROM lignes lg
             JOIN gare_dest ga ON ga.code_gadest = lg.gadest_lg
             JOIN compagnies ca ON ga.id_compaga = ca.cle_compagnie
             WHERE lg.ident_ligne = ?
             LIMIT 1",
            array($code)
        )->row();
    }

    /**
     * Uniquement une ligne de la même compagnie. Pas de repli CMT/CBT si on est en VIP.
     * @param object[] $rows
     * @return object|null
     */
    protected function _choisir_ligne_compagnie(array $rows, $prefComp, $prefNom = '')
    {
        if (empty($rows)) {
            return null;
        }
        $pref = $this->_token_compagnie($prefNom, $prefComp);
        if ($pref === '') {
            return $rows[0];
        }
        foreach ($rows as $row) {
            if ($this->_ligne_matche_compagnie($row, $prefComp, $prefNom)) {
                return $row;
            }
        }
        return null;
    }

    protected function _ligne_matche_compagnie($row, $prefComp, $prefNom = '')
    {
        if (!$row) {
            return false;
        }
        $prefComp = trim((string) $prefComp);
        $rowComp = isset($row->id_compaga) ? trim((string) $row->id_compaga) : '';
        if ($prefComp !== '' && $rowComp !== '' && $prefComp === $rowComp) {
            return true;
        }
        $pref = $this->_token_compagnie($prefNom, $prefComp);
        if ($pref === '') {
            return true;
        }
        $got = $this->_token_compagnie(
            isset($row->nom_compagnie_arrivee) ? $row->nom_compagnie_arrivee : '',
            $rowComp
        );
        if ($got === '') {
            return false;
        }
        return $got === $pref;
    }

    /**
     * BOBO, BAMAKO_VIP, BAMAKO_VIPSD → même ville métier.
     */
    protected function _nom_ville_normalise($nom)
    {
        $n = strtoupper(trim((string) $nom));
        $n = preg_replace('/_(VIPSD|CMTSD|VIP|CMT|CBT|CIT|SD)+$/', '', $n);
        $n = preg_replace('/[^A-Z]/', '', $n);
        return $n;
    }

    protected function _villes_noms_compatibles($a, $b)
    {
        $na = $this->_nom_ville_normalise($a);
        $nb = $this->_nom_ville_normalise($b);
        if ($na === '' || $nb === '') {
            return false;
        }
        return $na === $nb || strpos($na, $nb) === 0 || strpos($nb, $na) === 0;
    }

    /**
     * Ligne dérivée (ex. BAN3-BOB32) : 1re étape déclarative, sinon gaexp_principal → gaexp_suite.
     * Même compagnie d'arrivée que le principal (VIP reste VIP).
     */
    public function resolve_ligne_derive($ekey, $principal, $suite)
    {
        $pair = $this->resolve_derive_avec_heure($ekey, $principal, $suite);
        return $pair ? $pair['ligne'] : null;
    }

    /**
     * @return array{ligne:object,id_heur:int}|null
     */
    public function resolve_derive_avec_heure($ekey, $principal, $suite)
    {
        $lignes = $this->_candidats_ligne_derive($ekey, $principal, $suite);
        $heure = isset($principal->heure) ? $principal->heure : '';
        foreach ($lignes as $lg) {
            $idHeur = $this->resolve_id_heur_derive($lg->ident_ligne, $heure);
            if ($idHeur > 0) {
                return array('ligne' => $lg, 'id_heur' => $idHeur);
            }
        }
        if (!empty($lignes) && !empty($principal->id_heure)) {
            $idHeur = $this->_assurer_ligne_heure($lignes[0]->ident_ligne, $principal->id_heure);
            if ($idHeur > 0) {
                return array('ligne' => $lignes[0], 'id_heur' => $idHeur);
            }
        }
        return null;
    }

    /**
     * Lignes hub VIP/CMT/CBT selon le principal, sans clone du même OD.
     * Si l'étape catalogue est CBT (ex. OUA1-BOB32) alors que le principal est VIP,
     * on prend le jumeau VIP vers la même ville (ex. OUA1-BOB87).
     * @return object[]
     */
    protected function _candidats_ligne_derive($ekey, $principal, $suite)
    {
        $prefComp = $this->_compagnie_arrivee_preferee($principal, $suite);
        $prefNom = $this->_nom_compagnie_preferee($principal, $suite);
        $destPrincipal = isset($principal->gadest_lg) ? (string) $principal->gadest_lg : '';
        $gaexpPrincipal = isset($principal->gaexp_lg) ? (string) $principal->gaexp_lg : '';
        $excludeLigne = isset($principal->ligne_id) ? (string) $principal->ligne_id : '';
        $seen = array();
        $out = array();

        $ajouter = function ($row) use (&$seen, &$out, $prefComp, $prefNom) {
            if (!$row || empty($row->ident_ligne)) {
                return;
            }
            $id = (string) $row->ident_ligne;
            if (isset($seen[$id])) {
                return;
            }
            if (!$this->_ligne_matche_compagnie($row, $prefComp, $prefNom)) {
                return;
            }
            $seen[$id] = true;
            $out[] = $row;
        };

        $etapes = $this->m_itineraire_etape->get_by_parent($ekey, $principal->ligne_id);
        if (!empty($etapes)) {
            foreach ($etapes as $et) {
                $code = isset($et->code_itineraires) ? (string) $et->code_itineraires : '';
                if ($code === '' || $code === $excludeLigne) {
                    continue;
                }
                $row = $this->_ligne_avec_compagnie($code);
                if (!$row || (string) $row->gaexp_lg !== $gaexpPrincipal) {
                    continue;
                }
                if ($destPrincipal !== '' && (string) $row->gadest_lg === $destPrincipal) {
                    continue;
                }
                $ajouter($row);
                foreach ($this->_jumeaux_ligne_compagnie($row, $prefComp, $prefNom, $excludeLigne, $destPrincipal) as $twin) {
                    $ajouter($twin);
                }
            }
        }

        $hub = isset($suite->gaexp_lg) ? (string) $suite->gaexp_lg : '';
        if ($hub !== '') {
            foreach ($this->_lignes_hub_depuis_exp($gaexpPrincipal, $hub, $excludeLigne, $destPrincipal, $prefComp, $prefNom) as $row) {
                $ajouter($row);
            }
        }

        return $out;
    }

    /**
     * Même origine, même ville d'arrivée, compagnie du principal (VIP→VIP).
     * @return object[]
     */
    protected function _jumeaux_ligne_compagnie($ligneRef, $prefComp, $prefNom, $excludeLigne, $excludeDest)
    {
        if (!$ligneRef) {
            return array();
        }
        $villeId = isset($ligneRef->id_villega) ? (int) $ligneRef->id_villega : 0;
        $nomDest = isset($ligneRef->nom_gadest) ? (string) $ligneRef->nom_gadest : '';
        $rows = $this->db->query(
            "SELECT lg.ident_ligne, lg.nom_ligne, lg.gaexp_lg, lg.gadest_lg,
                    gd.id_compaga, gd.id_villega, gd.nom_gadest,
                    ca.nom_compagnie AS nom_compagnie_arrivee
             FROM lignes lg
             JOIN gare_dest gd ON gd.code_gadest = lg.gadest_lg
             JOIN compagnies ca ON gd.id_compaga = ca.cle_compagnie
             WHERE lg.gaexp_lg = ?
               AND lg.ident_ligne <> ?
               AND lg.gadest_lg <> ?
             ORDER BY lg.ident_ligne ASC",
            array($ligneRef->gaexp_lg, $excludeLigne, $excludeDest)
        )->result();
        $out = array();
        foreach ($rows as $row) {
            if (!$this->_ligne_matche_compagnie($row, $prefComp, $prefNom)) {
                continue;
            }
            $sameVille = $villeId > 0 && isset($row->id_villega) && (int) $row->id_villega === $villeId;
            $sameNom = $this->_villes_noms_compatibles($nomDest, isset($row->nom_gadest) ? $row->nom_gadest : '');
            if ($sameVille || $sameNom) {
                $out[] = $row;
            }
        }
        return $out;
    }

    /**
     * Hub = gare d'expédition de la suite (Bobo), compagnie du principal.
     * @return object[]
     */
    protected function _lignes_hub_depuis_exp($gaexpPrincipal, $hubExp, $excludeLigne, $excludeDest, $prefComp, $prefNom)
    {
        $geo = $this->db->query(
            "SELECT lg.ident_ligne, lg.nom_ligne, lg.gaexp_lg, lg.gadest_lg,
                    gd.id_compaga, gd.id_villega, gd.nom_gadest,
                    ca.nom_compagnie AS nom_compagnie_arrivee,
                    ge.nom_gaep, ge.id_villegd
             FROM lignes lg
             JOIN gare_dest gd ON gd.code_gadest = lg.gadest_lg
             JOIN compagnies ca ON gd.id_compaga = ca.cle_compagnie
             JOIN gare_exp ge ON ge.code_gaexp = ?
             WHERE lg.gaexp_lg = ?
               AND lg.ident_ligne <> ?
               AND lg.gadest_lg <> ?
             ORDER BY lg.ident_ligne ASC",
            array($hubExp, $gaexpPrincipal, $excludeLigne, $excludeDest)
        )->result();

        $prefix = preg_replace('/[0-9]+$/', '', $hubExp);
        if ($prefix === '') {
            $prefix = $hubExp;
        }
        $out = array();
        foreach ($geo as $row) {
            if (!$this->_ligne_matche_compagnie($row, $prefComp, $prefNom)) {
                continue;
            }
            $sameVille = isset($row->id_villega, $row->id_villegd)
                && (int) $row->id_villega === (int) $row->id_villegd;
            $sameNom = $this->_villes_noms_compatibles(
                isset($row->nom_gaep) ? $row->nom_gaep : '',
                isset($row->nom_gadest) ? $row->nom_gadest : ''
            );
            $samePrefix = $prefix !== '' && isset($row->gadest_lg)
                && stripos((string) $row->gadest_lg, $prefix) === 0;
            if ($sameVille || $sameNom || $samePrefix) {
                $out[] = $row;
            }
        }
        return $out;
    }

    /**
     * Si la ligne hub VIP n'a pas encore cet horaire au catalogue, le créer
     * (même heure que le départ principal, même bus).
     */
    protected function _assurer_ligne_heure($ligne_id, $id_heure)
    {
        $ligne_id = trim((string) $ligne_id);
        $id_heure = (int) $id_heure;
        if ($ligne_id === '' || $id_heure <= 0) {
            return 0;
        }
        $row = $this->db->query(
            "SELECT id_ligneheure, actif_lh FROM ligne_heure
             WHERE ligne_id = ? AND heure_identif = ?
             LIMIT 1",
            array($ligne_id, $id_heure)
        )->row();
        if ($row) {
            if ((int) $row->actif_lh !== 1) {
                $this->db->where('id_ligneheure', (int) $row->id_ligneheure)
                    ->update('ligne_heure', array('actif_lh' => 1));
            }
            return (int) $row->id_ligneheure;
        }
        $ok = $this->db->insert('ligne_heure', array(
            'ligne_id' => $ligne_id,
            'heure_identif' => $id_heure,
            'actif_lh' => 1,
            'createlh_at' => now('UTC'),
        ));
        if (!$ok) {
            return 0;
        }
        return (int) $this->db->insert_id();
    }

    /**
     * id_ligneheure pour la ligne dérivée à la même heure que le principal (sinon première heure active).
     */
    public function resolve_id_heur_derive($ligne_id, $heure_principal)
    {
        $ligne_id = trim((string) $ligne_id);
        $hhmm = substr(trim((string) $heure_principal), 0, 5);
        if ($ligne_id === '') {
            return 0;
        }

        if ($hhmm !== '' && preg_match('/^\d{2}:\d{2}$/', $hhmm)) {
            $row = $this->db->query(
                "SELECT lh.id_ligneheure
                 FROM ligne_heure lh
                 JOIN heures h ON lh.heure_identif = h.id_heure
                 WHERE lh.ligne_id = ?
                   AND lh.actif_lh = 1
                   AND h.h_active = 1
                   AND LEFT(CAST(h.heure AS CHAR), 5) = ?
                 LIMIT 1",
                array($ligne_id, $hhmm)
            )->row();
            if ($row) {
                return (int) $row->id_ligneheure;
            }
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
        if ($row) {
            return (int) $row->id_ligneheure;
        }

        // Dernier recours : horaire existant même si flag inactif
        $row = $this->db->query(
            "SELECT lh.id_ligneheure
             FROM ligne_heure lh
             JOIN heures h ON lh.heure_identif = h.id_heure
             WHERE lh.ligne_id = ?
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
     * Lignes catalogue « suite » (hub → même destination, autre gare exp.).
     * @return object[]
     */
    public function resolve_lignes_suite($ekey, $principal)
    {
        if (!$principal) {
            return array();
        }
        $prefComp = $this->_compagnie_arrivee_preferee($principal);
        $prefNom = $this->_nom_compagnie_preferee($principal);
        $seen = array();
        $out = array();

        $etapes = $this->m_itineraire_etape->get_by_parent($ekey, $principal->ligne_id);
        if (!empty($etapes)) {
            foreach ($etapes as $et) {
                $code = isset($et->code_itineraires) ? (string) $et->code_itineraires : '';
                if ($code === '' || $code === (string) $principal->ligne_id) {
                    continue;
                }
                $lgRow = $this->_ligne_avec_compagnie($code);
                if ($lgRow
                    && (string) $lgRow->gaexp_lg !== (string) $principal->gaexp_lg
                    && $this->_ligne_matche_compagnie($lgRow, $prefComp, $prefNom)
                    && !isset($seen[$code])) {
                    $seen[$code] = true;
                    $out[] = $lgRow;
                }
            }
        }

        if (empty($out)) {
            $rows = $this->db->query(
                "SELECT lg.ident_ligne, lg.nom_ligne, lg.gaexp_lg, lg.gadest_lg,
                        ga.id_compaga, ga.id_villega, ga.nom_gadest,
                        ca.nom_compagnie AS nom_compagnie_arrivee
                 FROM lignes lg
                 JOIN gare_dest ga ON ga.code_gadest = lg.gadest_lg
                 JOIN compagnies ca ON ga.id_compaga = ca.cle_compagnie
                 JOIN gare_exp ge ON lg.gaexp_lg = ge.code_gaexp
                 JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
                 JOIN entreprise e ON c.id_entrep = e.id_entreprise
                 WHERE e.ekey = ?
                   AND lg.gadest_lg = ?
                   AND lg.gaexp_lg <> ?
                   AND lg.ident_ligne <> ?
                 ORDER BY lg.ident_ligne ASC",
                array($ekey, $principal->gadest_lg, $principal->gaexp_lg, $principal->ligne_id)
            )->result();
            foreach ($rows as $row) {
                $id = isset($row->ident_ligne) ? (string) $row->ident_ligne : '';
                if ($id === '' || isset($seen[$id])) {
                    continue;
                }
                if (!$this->_ligne_matche_compagnie($row, $prefComp, $prefNom)) {
                    continue;
                }
                $seen[$id] = true;
                $out[] = $row;
            }
        }

        return $out;
    }

    /**
     * Horaires catalogue pour créer le départ suite au hub (J … J+max).
     * @return array
     */
    public function heures_correspondance($ekey, $code_progr_principal)
    {
        $principal = $this->prog_detail($ekey, $code_progr_principal);
        if (!$principal) {
            return array('ok' => false, 'error' => 'programme_principal_introuvable');
        }

        $exist = $this->get_by_principal($code_progr_principal);
        if ($exist) {
            return array(
                'ok' => true,
                'already_linked' => true,
                'lien' => $exist,
                'principal' => $this->_public_prog($principal),
            );
        }

        $lignes = $this->resolve_lignes_suite($ekey, $principal);
        if (empty($lignes)) {
            return array(
                'ok' => true,
                'principal' => $this->_public_prog($principal),
                'message' => 'aucune_ligne_suite',
                'dates_autorisees' => $this->dates_suite_autorisees($principal->date_progr),
                'heures_par_date' => array(),
            );
        }

        $ids = array();
        foreach ($lignes as $lg) {
            if (!empty($lg->ident_ligne)) {
                $ids[] = (string) $lg->ident_ligne;
            }
        }
        $ids = array_values(array_unique($ids));
        $ph = implode(',', array_fill(0, count($ids), '?'));
        $rows = $this->db->query(
            "SELECT lh.id_ligneheure, lh.ligne_id, lg.ident_ligne, lg.nom_ligne, lg.gaexp_lg, lg.gadest_lg,
                    h.heure, ga.id_compaga, ca.nom_compagnie AS nom_compagnie_arrivee
             FROM ligne_heure lh
             JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
             JOIN heures h ON lh.heure_identif = h.id_heure
             JOIN gare_dest ga ON lg.gadest_lg = ga.code_gadest
             JOIN compagnies ca ON ga.id_compaga = ca.cle_compagnie
             WHERE lh.ligne_id IN ($ph)
               AND lh.actif_lh = 1
               AND h.h_active = 1
             ORDER BY h.heure ASC, lg.ident_ligne ASC",
            $ids
        )->result();

        $datesOk = $this->dates_suite_autorisees($principal->date_progr);
        $minAbs = $this->datetime_to_minutes($principal->date_progr, $principal->heure) + $this->marge_min;
        $heuresParDate = array();
        foreach ($datesOk as $d) {
            $heuresParDate[$d] = array();
        }
        $seenSlot = array();

        foreach ($rows as $r) {
            foreach ($datesOk as $date) {
                if ($this->datetime_to_minutes($date, $r->heure) < $minAbs) {
                    continue;
                }
                $slotKey = $date . '|' . (int) $r->id_ligneheure;
                if (isset($seenSlot[$slotKey])) {
                    continue;
                }
                $seenSlot[$slotKey] = true;
                $hhmm = substr((string) $r->heure, 0, 5);
                $isLendemain = ((string) $date !== (string) $principal->date_progr);
                $cie = isset($r->nom_compagnie_arrivee) ? trim((string) $r->nom_compagnie_arrivee) : '';
                $label = $r->nom_ligne . ' ' . $hhmm;
                if ($cie !== '') {
                    $label .= ' · ' . $cie;
                }
                if ($isLendemain) {
                    $label .= ' (lendemain)';
                }
                $heuresParDate[$date][] = array(
                    'id_ligneheure' => (int) $r->id_ligneheure,
                    'ligne_id' => $r->ident_ligne,
                    'nom_ligne' => $r->nom_ligne,
                    'heure' => $r->heure,
                    'gareidentif' => $r->gaexp_lg,
                    'date_progr' => $date,
                    'lendemain' => $isLendemain,
                    'label' => $label,
                );
            }
        }

        $hubGare = isset($lignes[0]->gaexp_lg) ? (string) $lignes[0]->gaexp_lg : '';

        return array(
            'ok' => true,
            'principal' => $this->_public_prog($principal),
            'hub_gare' => $hubGare,
            'dates_autorisees' => $datesOk,
            'heures_par_date' => $heuresParDate,
            'suite_jours_max' => (int) $this->suite_jours_max,
            'marge_min' => (int) $this->marge_min,
        );
    }

    /**
     * @deprecated Utiliser heures_correspondance() — conservé pour compatibilité API.
     * @return array
     */
    public function suggest_suites($ekey, $code_progr_principal)
    {
        return $this->heures_correspondance($ekey, $code_progr_principal);
    }

    /**
     * Détail d'un créneau suite (ligne_heure au hub).
     * @return object|null
     */
    protected function _detail_ligneheure_suite($ekey, $principal, $id_ligneheure)
    {
        $id_ligneheure = (int) $id_ligneheure;
        if ($id_ligneheure <= 0 || !$principal) {
            return null;
        }
        $lignes = $this->resolve_lignes_suite($ekey, $principal);
        if (empty($lignes)) {
            return null;
        }
        $ids = array();
        foreach ($lignes as $lg) {
            if (!empty($lg->ident_ligne)) {
                $ids[] = (string) $lg->ident_ligne;
            }
        }
        $ph = implode(',', array_fill(0, count($ids), '?'));
        $params = array_merge(array($id_ligneheure), $ids);
        return $this->db->query(
            "SELECT lh.id_ligneheure, lh.ligne_id, lg.ident_ligne, lg.nom_ligne, lg.gaexp_lg, lg.gadest_lg,
                    h.heure, ga.id_compaga, ca.nom_compagnie AS nom_compagnie_arrivee
             FROM ligne_heure lh
             JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
             JOIN heures h ON lh.heure_identif = h.id_heure
             JOIN gare_dest ga ON lg.gadest_lg = ga.code_gadest
             JOIN compagnies ca ON ga.id_compaga = ca.cle_compagnie
             WHERE lh.id_ligneheure = ?
               AND lh.ligne_id IN ($ph)
               AND lh.actif_lh = 1
               AND h.h_active = 1
             LIMIT 1",
            $params
        )->row();
    }

    /**
     * @return string
     */
    protected function _nouveau_code_progr($gareidentif)
    {
        $today = mdate('%Y-%m-%d', now('UTC'));
        $gd = trim((string) $gareidentif);
        $compter = $this->db->query(
            "SELECT COUNT(code_progr) AS id FROM programme WHERE createdatepr = ? AND gareidentif = ?",
            array($today, $gd)
        )->row();
        $gd4 = ($gd === 'OUA12') ? 'WUA12' : $gd;
        return mdate('%y%m%d', now('UTC')) . $gd4 . ((int) $compter->id + 1);
    }

    /**
     * Crée un programme aligné sur le principal (même bus, depart_code, intervalles).
     * @return array{ok:bool,code_progr?:string,error?:string}
     */
    protected function _creer_programme_lie($principal, $gareidentif, $id_ligneheure, $date_progr, $heure)
    {
        $gd = trim((string) $gareidentif);
        $date = trim((string) $date_progr);
        $idHeur = (int) $id_ligneheure;
        if ($gd === '' || $date === '' || $idHeur <= 0) {
            return array('ok' => false, 'error' => 'params_manquants');
        }

        $exist = $this->db->query(
            "SELECT code_progr FROM programme
             WHERE gareidentif = ?
               AND date_progr = ?
               AND id_heur = ?
               AND statut_prog = 'actif'
               AND actif_prog = 0
             LIMIT 1",
            array($gd, $date, $idHeur)
        )->row();
        if ($exist) {
            return array('ok' => false, 'error' => 'depart_hub_existe');
        }

        $pcd = $this->_nouveau_code_progr($gd);
        $today = mdate('%Y-%m-%d', now('UTC'));
        $suheure = $heure ? $heure : $principal->heure;
        $arrayprog = array(
            'code_progr' => $pcd,
            'depart_code' => $principal->depart_code,
            'id_heur' => $idHeur,
            'gareidentif' => $gd,
            'idsousgare_prog' => null,
            'typetarif' => $principal->typetarif,
            'categori' => $principal->categori,
            'intervalle1' => (int) $principal->intervalle1,
            'intervalle2' => (int) $principal->intervalle2,
            'dateheure_prog' => $date . '-' . $suheure,
            'date_progr' => $date,
            'createdatepr' => $today,
            'createdpg_at' => now('UTC'),
            'statut_prog' => 'actif',
            'actif_prog' => 0,
        );
        $ok = $this->m_programme->create($arrayprog);
        if ($ok === null || $ok === FALSE) {
            return array('ok' => false, 'error' => 'echec_creation_programme');
        }
        return array('ok' => true, 'code_progr' => $pcd);
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
            'categori' => isset($p->categori) ? $p->categori : null,
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
     * Crée le lien + programme suite (hub) + programme dérivé.
     * - suite et dérivé : categori, depart_code, intervalles = principal
     * - vente dérivé : miroir des sièges déjà occupés sur la suite
     * - pool sièges : principal + suite + dérivé
     * options requises : id_ligneheure, date_progr_suite
     * @return array
     */
    public function link($ekey, $code_progr_principal, array $options = array())
    {
        $principal = $this->prog_detail($ekey, $code_progr_principal);
        if (!$principal) {
            return array('ok' => false, 'error' => 'programme_introuvable');
        }

        $idHeurSuite = isset($options['id_ligneheure']) ? (int) $options['id_ligneheure'] : 0;
        $dateSuite = isset($options['date_progr_suite']) ? trim((string) $options['date_progr_suite']) : '';
        if ($idHeurSuite <= 0 || $dateSuite === '') {
            return array('ok' => false, 'error' => 'params_manquants');
        }
        $datesOk = $this->dates_suite_autorisees($principal->date_progr);
        if (!in_array($dateSuite, $datesOk, true)) {
            return array('ok' => false, 'error' => 'dates_hors_plage');
        }

        if ($this->get_by_principal($code_progr_principal)) {
            return array('ok' => false, 'error' => 'deja_lie');
        }

        $lhSuite = $this->_detail_ligneheure_suite($ekey, $principal, $idHeurSuite);
        if (!$lhSuite) {
            return array('ok' => false, 'error' => 'heure_incompatible');
        }

        $suiteStub = (object) array(
            'date_progr' => $dateSuite,
            'heure' => $lhSuite->heure,
            'gaexp_lg' => $lhSuite->gaexp_lg,
            'gareidentif' => $lhSuite->gaexp_lg,
            'id_compaga' => isset($lhSuite->id_compaga) ? $lhSuite->id_compaga : '',
            'nom_compagnie_arrivee' => isset($lhSuite->nom_compagnie_arrivee) ? $lhSuite->nom_compagnie_arrivee : '',
        );
        $errDates = $this->valider_dates_suite($principal, $suiteStub);
        if ($errDates !== null) {
            return array('ok' => false, 'error' => $errDates);
        }

        $applyPrincipal = !isset($options['apply_principal']) || $options['apply_principal'];
        $applyDerive = !isset($options['apply_derive']) || $options['apply_derive'];
        $applySuite = !isset($options['apply_suite']) || $options['apply_suite'];
        $scopeBanfora = isset($options['scope_banfora']) && is_array($options['scope_banfora'])
            ? $options['scope_banfora'] : array();
        $scopeBobo = isset($options['scope_bobo']) && is_array($options['scope_bobo'])
            ? $options['scope_bobo'] : array();
        $hasScopeBanfora = !empty($options['has_scope_banfora']);
        $hasScopeBobo = !empty($options['has_scope_bobo']);

        $creerSuite = $this->_creer_programme_lie(
            $principal,
            $lhSuite->gaexp_lg,
            $idHeurSuite,
            $dateSuite,
            $lhSuite->heure
        );
        if (empty($creerSuite['ok'])) {
            return array('ok' => false, 'error' => isset($creerSuite['error']) ? $creerSuite['error'] : 'echec_creation_suite');
        }
        $codeSuite = $creerSuite['code_progr'];
        $suite = $this->prog_detail($ekey, $codeSuite);
        if (!$suite) {
            return array('ok' => false, 'error' => 'echec_creation_suite');
        }

        $pair = $this->resolve_derive_avec_heure($ekey, $principal, $suite);
        if (!$pair || empty($pair['ligne'])) {
            return array('ok' => false, 'error' => 'ligne_derive_introuvable');
        }
        $ligneDerive = $pair['ligne'];
        $idHeur = isset($pair['id_heur']) ? (int) $pair['id_heur'] : 0;
        if ($idHeur <= 0) {
            return array('ok' => false, 'error' => 'heure_derive_introuvable');
        }

        $existDerive = $this->db->query(
            "SELECT pr.code_progr FROM programme pr
             JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
             JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
             JOIN gare_dest ga ON lg.gadest_lg = ga.code_gadest
             WHERE pr.gareidentif = ?
               AND pr.date_progr = ?
               AND lh.ligne_id = ?
               AND lh.id_ligneheure = ?
               AND pr.statut_prog = 'actif'
               AND pr.actif_prog = 0
               AND (? = '' OR ga.id_compaga = ?)
             LIMIT 1",
            array(
                $principal->gareidentif,
                $principal->date_progr,
                $ligneDerive->ident_ligne,
                $idHeur,
                $this->_compagnie_arrivee_preferee($principal, $suite),
                $this->_compagnie_arrivee_preferee($principal, $suite),
            )
        )->row();

        $codeDerive = null;
        $int1 = (int) $principal->intervalle1;
        $int2 = (int) $principal->intervalle2;
        if ($existDerive) {
            $codeDerive = $existDerive->code_progr;
            $this->m_programme->update($codeDerive, array(
                'intervalle1' => $int1,
                'intervalle2' => $int2,
                'categori' => $principal->categori,
                'depart_code' => $principal->depart_code,
            ));
        } else {
            $heureDerive = $principal->heure;
            $heureRow = $this->db->query(
                "SELECT h.heure FROM ligne_heure lh JOIN heures h ON lh.heure_identif = h.id_heure WHERE lh.id_ligneheure = ? LIMIT 1",
                array($idHeur)
            )->row();
            if ($heureRow && !empty($heureRow->heure)) {
                $heureDerive = $heureRow->heure;
            }
            $creerDerive = $this->_creer_programme_lie(
                $principal,
                $principal->gareidentif,
                $idHeur,
                $principal->date_progr,
                $heureDerive
            );
            if (empty($creerDerive['ok'])) {
                $this->db->where('code_progr', $codeSuite)->delete('programme');
                return array('ok' => false, 'error' => isset($creerDerive['error']) ? $creerDerive['error'] : 'echec_creation_derive');
            }
            $codeDerive = $creerDerive['code_progr'];
        }

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
