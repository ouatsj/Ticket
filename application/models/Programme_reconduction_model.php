<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Sortie d'un départ + reconduction des numéros de sièges restants
 * vers un nouveau départ à la gare de correspondance :
 * même catégorie de bus, même depart_code que le principal,
 * horaire = départ de la gare de correspondance (pas l'heure du principal).
 */
class Programme_reconduction_model extends CI_Model
{
    protected $table_sortie = 'programme_sortie';
    protected $table_reco = 'programme_reconduction';
    protected $table_siege = 'programme_reconduction_siege';
    protected $table_sortie_siege = 'programme_sortie_siege';

    public function __construct()
    {
        parent::__construct();
        $this->ensure_tables();
        if (!isset($this->m_programme)) {
            $this->load->model('Programme_model', 'm_programme');
        }
    }

    public function roles_autorises()
    {
        return array('1', '2', '5', '8', '15');
    }

    public function agent_autorise($userole)
    {
        return in_array((string) $userole, $this->roles_autorises(), true);
    }

    public function ensure_tables()
    {
        static $done = false;
        if ($done) {
            return;
        }
        $done = true;
        $this->db->query(
            "CREATE TABLE IF NOT EXISTS {$this->table_sortie} (
              id_sortie INT UNSIGNED NOT NULL AUTO_INCREMENT,
              code_progr_source VARCHAR(128) NOT NULL,
              ekey VARCHAR(64) NOT NULL DEFAULT '',
              gareidentif VARCHAR(64) NOT NULL DEFAULT '',
              gadest_lg VARCHAR(64) DEFAULT NULL,
              ligne_id VARCHAR(128) DEFAULT NULL,
              date_progr DATE DEFAULT NULL,
              categori VARCHAR(64) DEFAULT NULL,
              intervalle1 INT NOT NULL DEFAULT 0,
              intervalle2 INT NOT NULL DEFAULT 0,
              declared_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
              declared_by VARCHAR(128) DEFAULT NULL,
              source_ferme TINYINT(1) NOT NULL DEFAULT 0,
              ferme_at TIMESTAMP NULL DEFAULT NULL,
              PRIMARY KEY (id_sortie),
              UNIQUE KEY uq_source (code_progr_source),
              KEY idx_gadest (gadest_lg),
              KEY idx_ekey_gare (ekey, gareidentif),
              KEY idx_date (date_progr)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
        $this->db->query(
            "CREATE TABLE IF NOT EXISTS {$this->table_reco} (
              id_reconduction INT UNSIGNED NOT NULL AUTO_INCREMENT,
              code_progr_source VARCHAR(128) NOT NULL,
              code_progr_cible VARCHAR(128) NOT NULL,
              gare_cible VARCHAR(64) NOT NULL DEFAULT '',
              ekey VARCHAR(64) NOT NULL DEFAULT '',
              created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
              created_by VARCHAR(128) DEFAULT NULL,
              PRIMARY KEY (id_reconduction),
              UNIQUE KEY uq_cible (code_progr_cible),
              KEY idx_source (code_progr_source)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
        $this->db->query(
            "CREATE TABLE IF NOT EXISTS {$this->table_siege} (
              id INT UNSIGNED NOT NULL AUTO_INCREMENT,
              code_progr_source VARCHAR(128) NOT NULL,
              code_progr_cible VARCHAR(128) NOT NULL,
              siege_num INT NOT NULL,
              PRIMARY KEY (id),
              UNIQUE KEY uq_source_siege (code_progr_source, siege_num),
              KEY idx_cible (code_progr_cible)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
        $this->db->query(
            "CREATE TABLE IF NOT EXISTS {$this->table_sortie_siege} (
              id INT UNSIGNED NOT NULL AUTO_INCREMENT,
              code_progr_source VARCHAR(128) NOT NULL,
              siege_num INT NOT NULL,
              PRIMARY KEY (id),
              UNIQUE KEY uq_sortie_siege (code_progr_source, siege_num),
              KEY idx_sortie_source (code_progr_source)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
    }

    public function prog_detail($ekey, $code_progr)
    {
        $code = trim((string) $code_progr);
        if ($code === '') {
            return null;
        }
        return $this->db->query(
            "SELECT pr.*, lh.id_ligneheure, lh.ligne_id, h.heure, h.id_heure,
                    lg.nom_ligne, lg.gaexp_lg, lg.gadest_lg, lg.ident_ligne,
                    ga.nom_gadest, ga.id_compaga, ga.id_villega,
                    ge.nom_gaep, ca.nom_compagnie AS nom_compagnie_arrivee
             FROM programme pr
             JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
             JOIN heures h ON lh.heure_identif = h.id_heure
             JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
             JOIN gare_exp ge ON lg.gaexp_lg = ge.code_gaexp
             JOIN gare_dest ga ON lg.gadest_lg = ga.code_gadest
             JOIN compagnies ca ON ga.id_compaga = ca.cle_compagnie
             JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
             JOIN entreprise e ON c.id_entrep = e.id_entreprise
             WHERE e.ekey = ?
               AND pr.code_progr = ?
             LIMIT 1",
            array($ekey, $code)
        )->row();
    }

    public function get_sortie($code_progr)
    {
        $code = trim((string) $code_progr);
        if ($code === '') {
            return null;
        }
        return $this->db->query(
            "SELECT * FROM {$this->table_sortie} WHERE code_progr_source = ? LIMIT 1",
            array($code)
        )->row();
    }

    public function get_reco_by_cible($code_progr)
    {
        $code = trim((string) $code_progr);
        if ($code === '') {
            return null;
        }
        return $this->db->query(
            "SELECT * FROM {$this->table_reco} WHERE code_progr_cible = ? LIMIT 1",
            array($code)
        )->row();
    }

    public function source_est_ferme($code_progr)
    {
        $s = $this->get_sortie($code_progr);
        return $s && (int) $s->source_ferme === 1;
    }

    /**
     * Sièges encore libres sur le départ source (non vendus, non déjà reconduits).
     * Après déclaration : uniquement ceux cochés à la sortie (pool aval).
     * @return int[]
     */
    public function sieges_restants($code_progr)
    {
        $libres = $this->sieges_libres_source($code_progr);
        $publies = $this->sieges_publies($code_progr);
        if (empty($publies)) {
            return $libres;
        }
        $set = array();
        foreach ($publies as $n) {
            $set[(int) $n] = true;
        }
        $out = array();
        foreach ($libres as $n) {
            if (!empty($set[(int) $n])) {
                $out[] = (int) $n;
            }
        }
        return $out;
    }

    /**
     * Tous les numéros encore libres côté source (ignore le sous-ensemble publié).
     * @return int[]
     */
    public function sieges_libres_source($code_progr)
    {
        $pr = $this->db->query(
            "SELECT intervalle1, intervalle2 FROM programme WHERE code_progr = ? LIMIT 1",
            array($code_progr)
        )->row();
        if (!$pr) {
            return array();
        }
        $d = (int) $pr->intervalle1;
        $f = (int) $pr->intervalle2;
        if ($f < $d) {
            return array();
        }
        $all = range($d, $f);
        $occupes = $this->sieges_occupes($code_progr);
        $pris = $this->sieges_deja_reconduits($code_progr);
        $out = array();
        foreach ($all as $n) {
            if (!in_array((int) $n, $occupes, true) && !in_array((int) $n, $pris, true)) {
                $out[] = (int) $n;
            }
        }
        return $out;
    }

    /**
     * @return int[]
     */
    public function sieges_occupes($code_progr)
    {
        $rows = $this->db->query(
            "SELECT num_siege_categorie AS n FROM passager
             WHERE code_pro = ?
               AND num_siege_categorie IS NOT NULL
               AND actif_pas = 0",
            array($code_progr)
        )->result();
        $out = array();
        foreach ($rows as $r) {
            $n = (int) $r->n;
            if ($n > 0) {
                $out[] = $n;
            }
        }
        return $out;
    }

    /**
     * Sièges cochés à la déclaration de sortie (pool proposé aux gares aval).
     * @return int[]
     */
    public function sieges_publies($code_progr)
    {
        $rows = $this->db->query(
            "SELECT siege_num FROM {$this->table_sortie_siege}
             WHERE code_progr_source = ?
             ORDER BY siege_num ASC",
            array($code_progr)
        )->result();
        $out = array();
        foreach ($rows as $r) {
            $out[] = (int) $r->siege_num;
        }
        return $out;
    }

    /**
     * @return int[]
     */
    public function sieges_deja_reconduits($code_progr_source)
    {
        $rows = $this->db->query(
            "SELECT siege_num FROM {$this->table_siege} WHERE code_progr_source = ?",
            array($code_progr_source)
        )->result();
        $out = array();
        foreach ($rows as $r) {
            $out[] = (int) $r->siege_num;
        }
        return $out;
    }

    /**
     * Sièges alloués à un départ aval.
     * @return int[]
     */
    public function sieges_cibles($code_progr_cible)
    {
        $rows = $this->db->query(
            "SELECT siege_num FROM {$this->table_siege} WHERE code_progr_cible = ? ORDER BY siege_num ASC",
            array($code_progr_cible)
        )->result();
        $out = array();
        foreach ($rows as $r) {
            $out[] = (int) $r->siege_num;
        }
        return $out;
    }

    /**
     * Index badges pour la liste programmes.
     * @param string[] $codes
     * @return array code => meta
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
        $out = array();

        $sorties = $this->db->query(
            "SELECT * FROM {$this->table_sortie} WHERE code_progr_source IN ($ph)",
            $codes
        )->result();
        foreach ($sorties as $s) {
            $restants = $this->sieges_restants($s->code_progr_source);
            $out[$s->code_progr_source] = array(
                'role' => 'source',
                'sorti' => true,
                'ferme' => (int) $s->source_ferme === 1,
                'nb_restants' => count($restants),
            );
        }

        $recos = $this->db->query(
            "SELECT r.*, s.gareidentif AS gare_source, s.gadest_lg
             FROM {$this->table_reco} r
             LEFT JOIN {$this->table_sortie} s ON s.code_progr_source = r.code_progr_source
             WHERE r.code_progr_cible IN ($ph)",
            $codes
        )->result();
        foreach ($recos as $r) {
            $sieges = $this->sieges_cibles($r->code_progr_cible);
            $out[$r->code_progr_cible] = array(
                'role' => 'cible',
                'source' => $r->code_progr_source,
                'gare_source' => isset($r->gare_source) ? $r->gare_source : '',
                'nb_sieges' => count($sieges),
            );
        }
        return $out;
    }

    public function badge_html(array $entry)
    {
        $role = isset($entry['role']) ? $entry['role'] : '';
        if ($role === 'source') {
            $nb = isset($entry['nb_restants']) ? (int) $entry['nb_restants'] : 0;
            if (!empty($entry['ferme'])) {
                return ' <span class="badge badge-danger" title="Départ fermé : une gare aval a déjà vendu">Sorti · fermé</span>';
            }
            return ' <span class="badge badge-warning" title="Sortie déclarée, sièges restants publiés">'
                . 'Sorti · ' . $nb . ' place' . ($nb > 1 ? 's' : '') . '</span>';
        }
        if ($role === 'cible') {
            $src = isset($entry['source']) ? $entry['source'] : '';
            $nb = isset($entry['nb_sieges']) ? (int) $entry['nb_sieges'] : 0;
            return ' <span class="badge badge-info" title="Sièges reconduits depuis ' . htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . '">'
                . 'Reconduit · ' . $nb . ' siège' . ($nb > 1 ? 's' : '') . '</span>';
        }
        return '';
    }

    /**
     * Offres visibles pour une gare aval (même destination, pas la gare source).
     * @return array
     */
    public function offres_pour_gare($ekey, $gareidentif)
    {
        $gare = trim((string) $gareidentif);
        $ekey = trim((string) $ekey);
        if ($gare === '' || $ekey === '') {
            return array();
        }
        $today = mdate('%Y-%m-%d', now('UTC'));
        $rows = $this->db->query(
            "SELECT s.*, pr.depart_code, h.heure, lg.nom_ligne, ga.nom_gadest, ge.nom_gaep
             FROM {$this->table_sortie} s
             JOIN programme pr ON pr.code_progr = s.code_progr_source
             JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
             JOIN heures h ON lh.heure_identif = h.id_heure
             JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
             JOIN gare_dest ga ON lg.gadest_lg = ga.code_gadest
             JOIN gare_exp ge ON s.gareidentif = ge.code_gaexp
             WHERE s.ekey = ?
               AND s.gareidentif != ?
               AND s.date_progr >= DATE_SUB(?, INTERVAL 1 DAY)
               AND EXISTS (
                    SELECT 1 FROM lignes l2
                    JOIN gare_dest ga2 ON l2.gadest_lg = ga2.code_gadest
                    JOIN gare_dest ga1 ON ga1.code_gadest = s.gadest_lg
                    WHERE l2.gaexp_lg = ?
                      AND ga2.nom_gadest != 'OUAGAESCAL'
                      AND ga2.id_compaga = ga1.id_compaga
                      AND (l2.gadest_lg = s.gadest_lg
                           OR ga2.nom_gadest = ga1.nom_gadest
                           OR ga2.id_villega = ga1.id_villega)
               )
             ORDER BY s.date_progr ASC, h.heure ASC",
            array($ekey, $gare, $today, $gare)
        )->result();

        $out = array();
        foreach ($rows as $r) {
            $restants = $this->sieges_restants($r->code_progr_source);
            if (empty($restants)) {
                continue;
            }
            $r->sieges_restants = $restants;
            $r->nb_restants = count($restants);
            $r->sieges_occupes = $this->sieges_occupes($r->code_progr_source);
            $hor = $this->horaire_aval_correspondance(
                $ekey,
                $r->code_progr_source,
                $gare,
                isset($r->gadest_lg) ? $r->gadest_lg : ''
            );
            $r->heure_principale = isset($r->heure) ? $r->heure : '';
            $r->heure_correspondance = isset($hor['heure']) ? $hor['heure'] : '';
            $r->date_correspondance = isset($hor['date_progr']) ? $hor['date_progr'] : '';
            $r->id_ligneheure_correspondance = isset($hor['id_heur']) ? (int) $hor['id_heur'] : 0;
            $r->depart_code_principal = isset($hor['depart_code']) ? $hor['depart_code'] : (isset($r->depart_code) ? $r->depart_code : '');
            $out[] = $r;
        }
        return $out;
    }

    /**
     * Horaire et depart_code du principal pour créer le départ aval
     * à l'heure de la gare de correspondance (programme suite), pas à l'heure du principal.
     *
     * @return array{depart_code:string,heure:string,date_progr:string,id_heur:int}
     */
    public function horaire_aval_correspondance($ekey, $code_progr_source, $gare_cible, $gadest_lg)
    {
        $out = array(
            'depart_code' => '',
            'heure' => '',
            'date_progr' => '',
            'id_heur' => 0,
        );
        $source = $this->prog_detail($ekey, $code_progr_source);
        if ($source && !empty($source->depart_code)) {
            $out['depart_code'] = $source->depart_code;
        }
        if (!isset($this->m_programme_correspondance)) {
            $this->load->model('Programme_correspondance_model', 'm_programme_correspondance');
        }
        $lien = $this->m_programme_correspondance->get_by_any_code($code_progr_source);
        if ($lien && !empty($lien->code_progr_principal)) {
            $prin = $this->prog_detail($ekey, $lien->code_progr_principal);
            if ($prin && !empty($prin->depart_code)) {
                $out['depart_code'] = $prin->depart_code;
            }
        }
        $suite = null;
        if ($lien && !empty($lien->code_progr_suite)) {
            $suite = $this->prog_detail($ekey, $lien->code_progr_suite);
        }
        if (!$suite) {
            return $out;
        }
        $out['heure'] = isset($suite->heure) ? (string) $suite->heure : '';
        $out['date_progr'] = isset($suite->date_progr) ? (string) $suite->date_progr : '';
        $gareSuite = isset($suite->gareidentif) ? (string) $suite->gareidentif : '';
        if ($gareSuite === (string) $gare_cible && !empty($suite->id_ligneheure)) {
            $out['id_heur'] = (int) $suite->id_ligneheure;
            return $out;
        }

        $hh = substr(trim($out['heure']), 0, 5);
        $heures = $this->heures_compatibles($ekey, $gare_cible, $gadest_lg);
        if ($hh !== '') {
            foreach ($heures as $h) {
                if (substr((string) $h->heure, 0, 5) === $hh) {
                    $out['id_heur'] = (int) $h->id_ligneheure;
                    return $out;
                }
            }
        }
        if (!empty($heures)) {
            $ligneId = $heures[0]->ident_ligne;
            $idHeur = $this->m_programme_correspondance->resolve_id_heur_derive($ligneId, $out['heure']);
            if ($idHeur > 0) {
                $matched = $this->db->query(
                    "SELECT LEFT(CAST(h.heure AS CHAR), 5) AS hh
                     FROM ligne_heure lh
                     JOIN heures h ON lh.heure_identif = h.id_heure
                     WHERE lh.id_ligneheure = ?
                     LIMIT 1",
                    array($idHeur)
                )->row();
                if ($matched && $hh !== '' && $matched->hh === $hh) {
                    $out['id_heur'] = $idHeur;
                    return $out;
                }
            }
            $idHeureRef = isset($suite->id_heure) ? (int) $suite->id_heure : 0;
            if ($idHeureRef > 0) {
                $forced = $this->_assurer_ligne_heure_locale($ligneId, $idHeureRef);
                if ($forced > 0) {
                    $out['id_heur'] = $forced;
                }
            }
        }
        return $out;
    }

    /**
     * Garantit un ligne_heure local à l'heure (id_heure) de la correspondance.
     */
    protected function _assurer_ligne_heure_locale($ligne_id, $id_heure)
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
     * Heures / lignes de la gare aval vers la même destination ET la même compagnie (CBT≠CMT≠VIP).
     */
    public function heures_compatibles($ekey, $gareidentif, $gadest_lg)
    {
        return $this->db->query(
            "SELECT lh.id_ligneheure, lg.ident_ligne, lg.nom_ligne, h.heure,
                    lg.gadest_lg, ga.nom_gadest, ga.id_compaga,
                    ca.nom_compagnie AS nom_compagnie_arrivee
             FROM ligne_heure lh
             JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
             JOIN heures h ON lh.heure_identif = h.id_heure
             JOIN gare_dest ga ON lg.gadest_lg = ga.code_gadest
             JOIN compagnies ca ON ga.id_compaga = ca.cle_compagnie
             JOIN gare_exp ge ON lg.gaexp_lg = ge.code_gaexp
             JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
             JOIN entreprise e ON c.id_entrep = e.id_entreprise
             JOIN gare_dest ga_src ON ga_src.code_gadest = ?
             WHERE e.ekey = ?
               AND lg.gaexp_lg = ?
               AND ga.id_compaga = ga_src.id_compaga
               AND (
                    lg.gadest_lg = ?
                    OR ga.nom_gadest = ga_src.nom_gadest
                    OR ga.id_villega = ga_src.id_villega
               )
               AND ga.nom_gadest != 'OUAGAESCAL'
               AND h.h_active = 1
               AND lh.actif_lh = 1
             ORDER BY (lg.gadest_lg = ga_src.code_gadest) DESC, h.heure ASC",
            array($gadest_lg, $ekey, $gareidentif, $gadest_lg)
        )->result();
    }

    /**
     * Recolle un départ reconduit sur la ligne de la même compagnie que la source (CBT reste CBT).
     */
    public function realigner_compagnie_cibles($gareidentif)
    {
        $gare = trim((string) $gareidentif);
        if ($gare === '') {
            return;
        }
        $rows = $this->db->query(
            "SELECT r.code_progr_cible, r.code_progr_source, pr_c.id_heur AS id_heur_cible,
                    h_c.heure AS heure_cible, ga_s.id_compaga AS id_comp_src,
                    ga_c.id_compaga AS id_comp_cible, ga_s.id_villega AS ville_src,
                    ga_s.nom_gadest AS nom_src, ga_s.code_gadest AS dest_src
             FROM {$this->table_reco} r
             JOIN programme pr_s ON pr_s.code_progr = r.code_progr_source
             JOIN ligne_heure lh_s ON pr_s.id_heur = lh_s.id_ligneheure
             JOIN lignes lg_s ON lh_s.ligne_id = lg_s.ident_ligne
             JOIN gare_dest ga_s ON lg_s.gadest_lg = ga_s.code_gadest
             JOIN programme pr_c ON pr_c.code_progr = r.code_progr_cible
             JOIN ligne_heure lh_c ON pr_c.id_heur = lh_c.id_ligneheure
             JOIN heures h_c ON lh_c.heure_identif = h_c.id_heure
             JOIN lignes lg_c ON lh_c.ligne_id = lg_c.ident_ligne
             JOIN gare_dest ga_c ON lg_c.gadest_lg = ga_c.code_gadest
             WHERE r.gare_cible = ?
               AND ga_s.id_compaga <> ga_c.id_compaga",
            array($gare)
        )->result();
        foreach ($rows as $r) {
            $hh = substr(trim((string) $r->heure_cible), 0, 5);
            if ($hh === '') {
                continue;
            }
            $alt = $this->db->query(
                "SELECT lh.id_ligneheure, h.heure
                 FROM ligne_heure lh
                 JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                 JOIN heures h ON lh.heure_identif = h.id_heure
                 JOIN gare_dest ga ON lg.gadest_lg = ga.code_gadest
                 WHERE lg.gaexp_lg = ?
                   AND ga.id_compaga = ?
                   AND ga.nom_gadest != 'OUAGAESCAL'
                   AND LEFT(CAST(h.heure AS CHAR), 5) = ?
                   AND (
                        lg.gadest_lg = ?
                        OR ga.nom_gadest = ?
                        OR ga.id_villega = ?
                   )
                   AND lh.actif_lh = 1
                   AND h.h_active = 1
                 ORDER BY (lg.gadest_lg = ?) DESC
                 LIMIT 1",
                array(
                    $gare,
                    $r->id_comp_src,
                    $hh,
                    $r->dest_src,
                    $r->nom_src,
                    (int) $r->ville_src,
                    $r->dest_src,
                )
            )->row();
            if (!$alt) {
                continue;
            }
            $prCible = $this->db->query(
                "SELECT date_progr FROM programme WHERE code_progr = ? LIMIT 1",
                array($r->code_progr_cible)
            )->row();
            $dateC = $prCible ? $prCible->date_progr : '';
            $this->db->where('code_progr', $r->code_progr_cible)->update('programme', array(
                'id_heur' => (int) $alt->id_ligneheure,
                'dateheure_prog' => $dateC . '-' . $alt->heure,
            ));
        }
    }

    /**
     * Aperçu avant déclaration : plan de sièges (libres à cocher, vendus en lecture).
     * @return array
     */
    public function apercu_sortie($ekey, $code_progr)
    {
        $detail = $this->prog_detail($ekey, $code_progr);
        if (!$detail) {
            return array('ok' => false, 'error' => 'programme_introuvable');
        }
        if ($this->get_sortie($code_progr)) {
            return array('ok' => false, 'error' => 'deja_sorti');
        }
        $d = (int) $detail->intervalle1;
        $f = (int) $detail->intervalle2;
        $libres = $this->sieges_libres_source($code_progr);
        $occupes = $this->sieges_occupes($code_progr);
        $heure = isset($detail->heure) ? substr((string) $detail->heure, 0, 5) : '';
        return array(
            'ok' => true,
            'code_progr' => $detail->code_progr,
            'nom_ligne' => isset($detail->nom_ligne) ? $detail->nom_ligne : '',
            'heure' => $heure,
            'date_progr' => isset($detail->date_progr) ? $detail->date_progr : '',
            'nom_gaep' => isset($detail->nom_gaep) ? $detail->nom_gaep : '',
            'nom_gadest' => isset($detail->nom_gadest) ? $detail->nom_gadest : '',
            'intervalle1' => $d,
            'intervalle2' => $f,
            'sieges_restants' => $libres,
            'sieges_occupes' => $occupes,
            'nb_restants' => count($libres),
            'nb_occupes' => count($occupes),
        );
    }

    /**
     * @param int[]|null $sieges Sièges à publier (null = tous les libres)
     * @return array{ok:bool,error?:string,sortie?:object,nb_restants?:int,sieges?:int[]}
     */
    public function declarer_sortie($ekey, $code_progr, $declared_by = null, $sieges = null)
    {
        $detail = $this->prog_detail($ekey, $code_progr);
        if (!$detail) {
            return array('ok' => false, 'error' => 'programme_introuvable');
        }
        if ($this->get_sortie($code_progr)) {
            $restants = $this->sieges_restants($code_progr);
            $sortie = $this->get_sortie($code_progr);
            return array(
                'ok' => true,
                'deja' => true,
                'sortie' => $sortie,
                'nb_restants' => count($restants),
                'sieges' => $restants,
            );
        }
        $libres = $this->sieges_libres_source($code_progr);
        $occupes = $this->sieges_occupes($code_progr);
        $libreSet = array();
        foreach ($libres as $n) {
            $libreSet[(int) $n] = true;
        }
        $occSet = array();
        foreach ($occupes as $n) {
            $occSet[(int) $n] = true;
        }
        if (is_array($sieges)) {
            $sieges = $this->_normaliser_sieges($sieges);
            if (empty($sieges)) {
                return array('ok' => false, 'error' => 'aucun_siege');
            }
            foreach ($sieges as $n) {
                if (empty($libreSet[(int) $n]) && empty($occSet[(int) $n])) {
                    return array('ok' => false, 'error' => 'siege_indisponible');
                }
            }
        } else {
            $sieges = $libres;
        }

        $this->db->trans_begin();
        foreach ($sieges as $n) {
            if (empty($occSet[(int) $n])) {
                continue;
            }
            $this->db->query(
                "UPDATE passager
                 SET num_siege_categorie = NULL
                 WHERE code_pro = ?
                   AND num_siege_categorie = ?
                   AND actif_pas = 0",
                array($detail->code_progr, (int) $n)
            );
        }
        $ok = $this->db->insert($this->table_sortie, array(
            'code_progr_source' => $detail->code_progr,
            'ekey' => $ekey,
            'gareidentif' => $detail->gareidentif,
            'gadest_lg' => $detail->gadest_lg,
            'ligne_id' => $detail->ligne_id,
            'date_progr' => $detail->date_progr,
            'categori' => $detail->categori,
            'intervalle1' => (int) $detail->intervalle1,
            'intervalle2' => (int) $detail->intervalle2,
            'declared_by' => $declared_by,
            'source_ferme' => 0,
        ));
        if (!$ok) {
            $this->db->trans_rollback();
            return array('ok' => false, 'error' => 'echec_sortie');
        }
        foreach ($sieges as $n) {
            $this->db->insert($this->table_sortie_siege, array(
                'code_progr_source' => $detail->code_progr,
                'siege_num' => (int) $n,
            ));
            if (!$this->db->affected_rows()) {
                $this->db->trans_rollback();
                return array('ok' => false, 'error' => 'echec_sortie');
            }
        }
        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return array('ok' => false, 'error' => 'echec_sortie');
        }
        $this->db->trans_commit();

        return array(
            'ok' => true,
            'sortie' => $this->get_sortie($code_progr),
            'nb_restants' => count($sieges),
            'sieges' => $sieges,
        );
    }

    /**
     * Crée le départ aval avec les numéros choisis.
     * @param int[] $sieges
     * @return array
     */
    public function creer_depart_aval($ekey, $code_progr_source, $gare_cible, $id_ligneheure, array $sieges, array $options = array())
    {
        $source = $this->prog_detail($ekey, $code_progr_source);
        if (!$source) {
            return array('ok' => false, 'error' => 'programme_introuvable');
        }
        $sortie = $this->get_sortie($code_progr_source);
        if (!$sortie) {
            return array('ok' => false, 'error' => 'sortie_non_declaree');
        }
        $gare_cible = trim((string) $gare_cible);
        if ($gare_cible === '' || $gare_cible === (string) $source->gareidentif) {
            return array('ok' => false, 'error' => 'gare_cible_invalide');
        }

        $sieges = $this->_normaliser_sieges($sieges);
        if (empty($sieges)) {
            return array('ok' => false, 'error' => 'aucun_siege');
        }
        $disponibles = $this->sieges_restants($code_progr_source);
        $dispSet = array();
        foreach ($disponibles as $n) {
            $dispSet[(int) $n] = true;
        }
        foreach ($sieges as $n) {
            if (empty($dispSet[(int) $n])) {
                return array('ok' => false, 'error' => 'siege_indisponible');
            }
        }

        $idHeur = (int) $id_ligneheure;
        $hor = $this->horaire_aval_correspondance(
            $ekey,
            $code_progr_source,
            $gare_cible,
            $source->gadest_lg
        );
        if (!empty($hor['id_heur'])) {
            $idHeur = (int) $hor['id_heur'];
        } elseif (!empty($hor['heure'])) {
            return array('ok' => false, 'error' => 'heure_correspondance_introuvable');
        }
        if ($idHeur <= 0) {
            return array('ok' => false, 'error' => 'heure_incompatible');
        }
        $departCode = !empty($hor['depart_code']) ? $hor['depart_code'] : $source->depart_code;
        if (trim((string) $departCode) === '') {
            return array('ok' => false, 'error' => 'depart_code_manquant');
        }

        $heureOk = $this->db->query(
            "SELECT lh.id_ligneheure, h.heure, lg.gadest_lg, lg.gaexp_lg, lg.nom_ligne
             FROM ligne_heure lh
             JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
             JOIN heures h ON lh.heure_identif = h.id_heure
             JOIN gare_dest ga ON lg.gadest_lg = ga.code_gadest
             JOIN gare_dest ga_src ON ga_src.code_gadest = ?
             WHERE lh.id_ligneheure = ?
               AND lg.gaexp_lg = ?
               AND ga.id_compaga = ga_src.id_compaga
               AND (
                    lg.gadest_lg = ?
                    OR ga.nom_gadest = ga_src.nom_gadest
                    OR ga.id_villega = ga_src.id_villega
               )
               AND lh.actif_lh = 1
             LIMIT 1",
            array($source->gadest_lg, $idHeur, $gare_cible, $source->gadest_lg)
        )->row();
        if (!$heureOk) {
            return array('ok' => false, 'error' => 'heure_incompatible');
        }

        $today = mdate('%Y-%m-%d', now('UTC'));
        $dateProg = !empty($options['date_progr']) ? trim((string) $options['date_progr']) : '';
        if (!empty($hor['date_progr']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $hor['date_progr'])) {
            $dateProg = $hor['date_progr'];
        } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateProg)) {
            $dateProg = $source->date_progr;
        }
        if ($dateProg < $source->date_progr) {
            return array('ok' => false, 'error' => 'date_invalide');
        }
        $typetarif = !empty($options['typetarif']) ? $options['typetarif'] : $source->typetarif;
        $createdBy = isset($options['created_by']) ? $options['created_by'] : null;
        $gareStore = isset($heureOk->gaexp_lg) ? trim((string) $heureOk->gaexp_lg) : $gare_cible;

        $compter = $this->db->query(
            "SELECT COUNT(code_progr) AS id FROM programme WHERE createdatepr = ? AND gareidentif = ?",
            array($today, $gareStore)
        )->row();
        $gd4 = ($gareStore === 'OUA12') ? 'WUA12' : $gareStore;
        $seq = (int) $compter->id + 1;
        $pcd = mdate('%y%m%d', now('UTC')) . $gd4 . $seq;
        while ($this->db->query("SELECT 1 FROM programme WHERE code_progr = ? LIMIT 1", array($pcd))->row()) {
            $seq++;
            $pcd = mdate('%y%m%d', now('UTC')) . $gd4 . $seq;
        }
        $pc = $departCode;
        $minS = (int) $source->intervalle1;
        $maxS = (int) $source->intervalle2;
        if ($maxS < $minS) {
            $minS = min($sieges);
            $maxS = max($sieges);
        }

        $this->db->trans_begin();

        $arrayprog = array(
            'code_progr' => $pcd,
            'depart_code' => $pc,
            'id_heur' => $idHeur,
            'gareidentif' => $gareStore,
            'typetarif' => $typetarif,
            'categori' => $source->categori,
            'intervalle1' => $minS,
            'intervalle2' => $maxS,
            'dateheure_prog' => $dateProg . '-' . $heureOk->heure,
            'date_progr' => $dateProg,
            'createdatepr' => $today,
            'createdpg_at' => now('UTC'),
            'statut_prog' => 'actif',
            'actif_prog' => 0,
        );
        $this->m_programme->create($arrayprog);
        $this->db->query(
            "UPDATE programme SET idsousgare_prog = NULL, statut_prog = 'actif', actif_prog = 0
             WHERE code_progr = ?",
            array($pcd)
        );
        $this->db->where('code_progr', $pcd)->delete('programme_sousgare');
        $exists = $this->db->query(
            "SELECT code_progr FROM programme WHERE code_progr = ? LIMIT 1",
            array($pcd)
        )->row();
        if (!$exists) {
            $this->db->trans_rollback();
            return array('ok' => false, 'error' => 'echec_creation_depart');
        }

        $this->db->insert($this->table_reco, array(
            'code_progr_source' => $code_progr_source,
            'code_progr_cible' => $pcd,
            'gare_cible' => $gareStore,
            'ekey' => $ekey,
            'created_by' => $createdBy,
        ));
        if (!$this->db->affected_rows()) {
            $this->db->trans_rollback();
            return array('ok' => false, 'error' => 'echec_lien');
        }

        foreach ($sieges as $n) {
            $this->db->insert($this->table_siege, array(
                'code_progr_source' => $code_progr_source,
                'code_progr_cible' => $pcd,
                'siege_num' => (int) $n,
            ));
            if (!$this->db->affected_rows()) {
                $this->db->trans_rollback();
                return array('ok' => false, 'error' => 'siege_pris');
            }
        }

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return array('ok' => false, 'error' => 'echec_transaction');
        }
        $this->db->trans_commit();

        return array(
            'ok' => true,
            'code_progr' => $pcd,
            'depart_code' => $pc,
            'sieges' => $sieges,
            'depart' => $this->prog_detail($ekey, $pcd),
        );
    }

    /**
     * Première vente réelle sur un départ aval → ferme le départ source.
     */
    public function apres_vente($code_progr)
    {
        $reco = $this->get_reco_by_cible($code_progr);
        if (!$reco) {
            return;
        }
        $this->db->where('code_progr_source', $reco->code_progr_source)
            ->where('source_ferme', 0)
            ->update($this->table_sortie, array(
                'source_ferme' => 1,
                'ferme_at' => date('Y-m-d H:i:s'),
            ));
    }

    /**
     * Le siège peut-il être vendu sur ce programme (reconduction) ?
     */
    public function siege_vendable($code_progr, $num_siege)
    {
        $code = trim((string) $code_progr);
        $num = (int) $num_siege;
        if ($code === '' || $num <= 0) {
            return true;
        }

        if ($this->source_est_ferme($code)) {
            return false;
        }

        $reco = $this->get_reco_by_cible($code);
        if ($reco) {
            $alloues = $this->sieges_cibles($code);
            if (!in_array($num, $alloues, true)) {
                return false;
            }
            $prisSource = $this->db->query(
                "SELECT 1 FROM passager
                 WHERE code_pro = ? AND num_siege_categorie = ? AND actif_pas = 0
                 LIMIT 1",
                array($reco->code_progr_source, $num)
            )->row();
            if ($prisSource) {
                return false;
            }
            return true;
        }

        $sortie = $this->get_sortie($code);
        if ($sortie) {
            $pris = $this->sieges_deja_reconduits($code);
            if (in_array($num, $pris, true)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Extra SQL AND pour cdprog. Retourne false si aucune vente (liste vide).
     * @return string|false|null  null = pas concerné, false = aucun siège, string = AND ...
     */
    public function cdprog_extra_and($code_progr, $aliasSiege = 'sc.siege_num')
    {
        $code = trim((string) $code_progr);
        if ($code === '') {
            return null;
        }
        $codeEsc = $this->db->escape_str($code);

        if ($this->source_est_ferme($code)) {
            return false;
        }

        $reco = $this->get_reco_by_cible($code);
        if ($reco) {
            $srcEsc = $this->db->escape_str($reco->code_progr_source);
            return " AND {$aliasSiege} IN (
                        SELECT rs.siege_num FROM {$this->table_siege} rs
                        WHERE rs.code_progr_cible = '{$codeEsc}'
                    )
                    AND {$aliasSiege} NOT IN (
                        SELECT p.num_siege_categorie FROM passager p
                        WHERE p.code_pro = '{$srcEsc}'
                          AND p.num_siege_categorie IS NOT NULL
                          AND p.actif_pas = 0
                    )";
        }

        $sortie = $this->get_sortie($code);
        if ($sortie) {
            return " AND {$aliasSiege} NOT IN (
                        SELECT rs.siege_num FROM {$this->table_siege} rs
                        WHERE rs.code_progr_source = '{$codeEsc}'
                    )";
        }
        return null;
    }

    protected function _normaliser_sieges(array $sieges)
    {
        $out = array();
        foreach ($sieges as $n) {
            $n = (int) $n;
            if ($n > 0) {
                $out[$n] = $n;
            }
        }
        $vals = array_values($out);
        sort($vals, SORT_NUMERIC);
        return $vals;
    }
}
