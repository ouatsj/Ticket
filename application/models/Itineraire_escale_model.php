<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Escales tarifées définies sur un itinéraire (ligne parent),
 * sans créer de programme dédié pour l'escale.
 */
class Itineraire_escale_model extends CI_Model
{
    protected $table = 'itineraire_escales';

    public function __construct()
    {
        parent::__construct();
    }

    public function get($cid, $id = FALSE)
    {
        $sql = "SELECT
                    ie.*,
                    parent.nom_ligne AS nom_ligne_parent,
                    parent.gaexp_lg,
                    parent.gadest_lg AS gadest_parent,
                    ge.nom_gaep AS depart_parent,
                    ga_term.nom_gadest AS arrivee_parent,
                    ga.nom_gadest AS arrivee_escale
                FROM itineraire_escales ie
                JOIN lignes parent ON parent.ident_ligne = ie.id_lignes
                JOIN gare_exp ge ON ge.code_gaexp = parent.gaexp_lg
                JOIN gare_dest ga_term ON ga_term.code_gadest = parent.gadest_lg
                JOIN gare_dest ga ON ga.code_gadest = ie.code_gadest
                JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.id_entreprise = ?";

        if ($id === FALSE) {
            $sql .= " ORDER BY ie.id_lignes, ie.ordre_escale, ie.id_escale";
            return $this->db->query($sql, array($cid))->result();
        }

        $sql .= " AND ie.id_escale = ? LIMIT 1";
        return $this->db->query($sql, array($cid, $id))->row();
    }

    /**
     * Configurations Escale TPE uniquement (2 prix TPE renseignés).
     * Indépendant d’Escales tarifées (prix_escale).
     */
    public function get_tpe($cid)
    {
        if (!$this->db->field_exists('prix_escale_origine', $this->table)) {
            return array();
        }
        $has_tpe_dest = $this->db->field_exists('prix_escale_tpe', $this->table);
        $sql = "SELECT
                    ie.*,
                    parent.nom_ligne AS nom_ligne_parent,
                    parent.gaexp_lg,
                    parent.gadest_lg AS gadest_parent,
                    ge.nom_gaep AS depart_parent,
                    ga_term.nom_gadest AS arrivee_parent,
                    ga.nom_gadest AS arrivee_escale
                FROM itineraire_escales ie
                JOIN lignes parent ON parent.ident_ligne = ie.id_lignes
                JOIN gare_exp ge ON ge.code_gaexp = parent.gaexp_lg
                JOIN gare_dest ga_term ON ga_term.code_gadest = parent.gadest_lg
                JOIN gare_dest ga ON ga.code_gadest = ie.code_gadest
                JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.id_entreprise = ?
                  AND ie.prix_escale_origine IS NOT NULL"
            . ($has_tpe_dest ? ' AND ie.prix_escale_tpe IS NOT NULL' : '')
            . " ORDER BY ie.id_lignes, ie.ordre_escale, ie.id_escale";
        return $this->db->query($sql, array($cid))->result();
    }

    /**
     * Réinitialise toutes les configs Escale TPE (vide l’onglet TPE).
     * N’efface pas prix_escale (Escales tarifées / vente classique intactes).
     */
    public function reset_tpe_configs($cid)
    {
        if (!$this->db->field_exists('prix_escale_origine', $this->table)) {
            return 0;
        }
        $set = 'ie.prix_escale_origine = NULL';
        if ($this->db->field_exists('prix_escale_tpe', $this->table)) {
            $set .= ', ie.prix_escale_tpe = NULL';
        }
        $this->db->query(
            "UPDATE itineraire_escales ie
             JOIN lignes parent ON parent.ident_ligne = ie.id_lignes
             JOIN gare_exp ge ON ge.code_gaexp = parent.gaexp_lg
             JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
             JOIN entreprise e ON c.id_entrep = e.id_entreprise
             SET {$set}
             WHERE e.id_entreprise = ?",
            array($cid)
        );
        return (int) $this->db->affected_rows();
    }

    /**
     * Retire une ligne de l’onglet Escale TPE (prix TPE → NULL).
     * Conserve prix_escale (Escales tarifées) ; supprime les liaisons TPE liées.
     */
    public function clear_tpe_config($id_escale)
    {
        $id_escale = (int) $id_escale;
        if ($id_escale <= 0) {
            return false;
        }
        if (!$this->db->field_exists('prix_escale_origine', $this->table)) {
            return false;
        }
        $payload = array('prix_escale_origine' => null);
        if ($this->db->field_exists('prix_escale_tpe', $this->table)) {
            $payload['prix_escale_tpe'] = null;
        }
        $ok = $this->db->where('id_escale', $id_escale)->update($this->table, $payload);
        if ($this->db->table_exists('itineraire_escales_tpe_liaisons')) {
            $this->db->group_start()
                ->where('id_escale_depart', $id_escale)
                ->or_where('id_escale_arrivee', $id_escale)
                ->group_end()
                ->delete('itineraire_escales_tpe_liaisons');
        }
        return (bool) $ok;
    }

    /**
     * Crée la table liaisons TPE si absente (idempotent).
     */
    public function ensure_tpe_liaisons_table()
    {
        if ($this->db->table_exists('itineraire_escales_tpe_liaisons')) {
            return true;
        }
        $this->db->query(
            "CREATE TABLE IF NOT EXISTS itineraire_escales_tpe_liaisons (
                id_liaison INT UNSIGNED NOT NULL AUTO_INCREMENT,
                id_lignes VARCHAR(64) NOT NULL,
                id_escale_depart INT UNSIGNED NOT NULL,
                id_escale_arrivee INT UNSIGNED NOT NULL,
                prix_liaison DECIMAL(12,2) NOT NULL DEFAULT 0,
                actif_liaison TINYINT(1) NOT NULL DEFAULT 1,
                PRIMARY KEY (id_liaison),
                UNIQUE KEY uq_tpe_liaison (id_lignes, id_escale_depart, id_escale_arrivee),
                KEY idx_tpe_liaison_depart (id_escale_depart),
                KEY idx_tpe_liaison_arrivee (id_escale_arrivee)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
        return $this->db->table_exists('itineraire_escales_tpe_liaisons');
    }

    /**
     * Liaisons escale→escale configurées (Escale TPE).
     */
    public function get_tpe_liaisons($cid)
    {
        if (!$this->ensure_tpe_liaisons_table()) {
            return array();
        }
        $sql = "SELECT
                    l.*,
                    parent.nom_ligne AS nom_ligne_parent,
                    ed.nom_escale AS nom_escale_depart,
                    ed.code_gadest AS code_depart,
                    ea.nom_escale AS nom_escale_arrivee,
                    ea.code_gadest AS code_arrivee,
                    gad.nom_gadest AS arrivee_nom_depart,
                    gaa.nom_gadest AS arrivee_nom_arrivee
                FROM itineraire_escales_tpe_liaisons l
                JOIN lignes parent ON parent.ident_ligne = l.id_lignes
                JOIN itineraire_escales ed ON ed.id_escale = l.id_escale_depart
                JOIN itineraire_escales ea ON ea.id_escale = l.id_escale_arrivee
                LEFT JOIN gare_dest gad ON gad.code_gadest = ed.code_gadest
                LEFT JOIN gare_dest gaa ON gaa.code_gadest = ea.code_gadest
                JOIN gare_exp ge ON ge.code_gaexp = parent.gaexp_lg
                JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.id_entreprise = ?
                  AND l.actif_liaison = 1
                ORDER BY parent.nom_ligne, ed.ordre_escale, ea.ordre_escale, l.id_liaison";
        return $this->db->query($sql, array($cid))->result();
    }

    /**
     * Enregistre / met à jour une liaison escale→escale TPE.
     *
     * @return int|false id_liaison
     */
    public function save_tpe_liaison($id_lignes, $id_escale_depart, $id_escale_arrivee, $prix)
    {
        if (!$this->ensure_tpe_liaisons_table()) {
            return false;
        }
        $id_lignes = trim((string) $id_lignes);
        $id_escale_depart = (int) $id_escale_depart;
        $id_escale_arrivee = (int) $id_escale_arrivee;
        $prix = (float) $prix;
        if ($id_lignes === '' || $id_escale_depart < 1 || $id_escale_arrivee < 1
            || $id_escale_depart === $id_escale_arrivee || $prix < 0
        ) {
            return false;
        }

        $existing = $this->db->query(
            "SELECT id_liaison FROM itineraire_escales_tpe_liaisons
             WHERE id_lignes = ? AND id_escale_depart = ? AND id_escale_arrivee = ?
             LIMIT 1",
            array($id_lignes, $id_escale_depart, $id_escale_arrivee)
        )->row();

        if ($existing) {
            $this->db->where('id_liaison', (int) $existing->id_liaison)->update(
                'itineraire_escales_tpe_liaisons',
                array(
                    'prix_liaison' => $prix,
                    'actif_liaison' => 1,
                )
            );
            return (int) $existing->id_liaison;
        }

        $this->db->insert('itineraire_escales_tpe_liaisons', array(
            'id_lignes' => $id_lignes,
            'id_escale_depart' => $id_escale_depart,
            'id_escale_arrivee' => $id_escale_arrivee,
            'prix_liaison' => $prix,
            'actif_liaison' => 1,
        ));
        return (int) $this->db->insert_id();
    }

    public function update_tpe_liaison($id_liaison, $prix)
    {
        if (!$this->ensure_tpe_liaisons_table()) {
            return false;
        }
        return $this->db->where('id_liaison', (int) $id_liaison)->update(
            'itineraire_escales_tpe_liaisons',
            array('prix_liaison' => (float) $prix)
        );
    }

    public function delete_tpe_liaison($id_liaison)
    {
        if (!$this->ensure_tpe_liaisons_table()) {
            return false;
        }
        return $this->db->where('id_liaison', (int) $id_liaison)
            ->delete('itineraire_escales_tpe_liaisons');
    }

    /**
     * Prix liaison TPE escale→escale (null si non configuré).
     */
    public function get_prix_liaison_tpe($id_escale_depart, $id_escale_arrivee)
    {
        if (!$this->db->table_exists('itineraire_escales_tpe_liaisons')) {
            return null;
        }
        $row = $this->db->query(
            "SELECT prix_liaison FROM itineraire_escales_tpe_liaisons
             WHERE id_escale_depart = ?
               AND id_escale_arrivee = ?
               AND actif_liaison = 1
             LIMIT 1",
            array((int) $id_escale_depart, (int) $id_escale_arrivee)
        )->row();
        if ($row && $row->prix_liaison !== null && $row->prix_liaison !== '') {
            return (float) $row->prix_liaison;
        }
        return null;
    }


    /**
     * Escales actives du trajet parent gaexp → gadest.
     */
    public function get_by_od($gaexp, $gadest, $actifs_only = TRUE)
    {
        $sql = "SELECT ie.*, ga.nom_gadest AS arrivee_escale, parent.ident_ligne, parent.nom_ligne
                FROM itineraire_escales ie
                JOIN lignes parent ON parent.ident_ligne = ie.id_lignes
                JOIN gare_dest ga ON ga.code_gadest = ie.code_gadest
                WHERE parent.gaexp_lg = ?
                  AND parent.gadest_lg = ?";
        if ($actifs_only) {
            $sql .= " AND ie.actif_escale = 1";
        }
        $sql .= " ORDER BY ie.ordre_escale, ie.id_escale";
        return $this->db->query($sql, array($gaexp, $gadest))->result();
    }

    public function get_by_parent($parent_ligne, $actifs_only = TRUE)
    {
        $sql = "SELECT ie.*, ga.nom_gadest AS arrivee_escale
                FROM itineraire_escales ie
                JOIN gare_dest ga ON ga.code_gadest = ie.code_gadest
                WHERE ie.id_lignes = ?";
        if ($actifs_only) {
            $sql .= " AND ie.actif_escale = 1";
        }
        $sql .= " ORDER BY ie.ordre_escale, ie.id_escale";
        return $this->db->query($sql, array($parent_ligne))->result();
    }

    public function create(array $data)
    {
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function update($id, array $data)
    {
        return $this->db->where('id_escale', $id)->update($this->table, $data);
    }

    public function delete($id_escale)
    {
        return $this->db->where('id_escale', (int) $id_escale)->delete($this->table);
    }

    public function next_ordre($parent)
    {
        $row = $this->db->query(
            "SELECT COALESCE(MAX(ordre_escale), 0) AS m FROM itineraire_escales WHERE id_lignes = ?",
            array($parent)
        )->row();
        return (int) $row->m + 1;
    }

    /**
     * Assure qu'un hub (gare_dest de la composition) existe comme escale sur le parent.
     * Sans prix TPE OD — sert uniquement aux liaisons escale→hub.
     *
     * @param string $id_lignes
     * @param string $code_gadest
     * @param string $nom_escale
     * @return int id_escale (0 si échec)
     */
    public function ensure_hub_escale($id_lignes, $code_gadest, $nom_escale = '')
    {
        $id_lignes = trim((string) $id_lignes);
        $code_gadest = trim((string) $code_gadest);
        $nom_escale = trim((string) $nom_escale);
        if ($id_lignes === '' || $code_gadest === '') {
            return 0;
        }
        $row = $this->db->query(
            "SELECT id_escale FROM itineraire_escales
             WHERE id_lignes = ? AND code_gadest = ?
             LIMIT 1",
            array($id_lignes, $code_gadest)
        )->row();
        if ($row) {
            $id = (int) $row->id_escale;
            if ($id > 0) {
                $this->db->where('id_escale', $id)->update($this->table, array('actif_escale' => 1));
            }
            return $id;
        }
        if ($nom_escale === '') {
            $g = $this->db->query(
                "SELECT nom_gadest FROM gare_dest WHERE code_gadest = ? LIMIT 1",
                array($code_gadest)
            )->row();
            $nom_escale = ($g && !empty($g->nom_gadest)) ? trim((string) $g->nom_gadest) : $code_gadest;
        }
        $payload = array(
            'id_lignes' => $id_lignes,
            'code_gadest' => $code_gadest,
            'nom_escale' => $nom_escale,
            'ordre_escale' => $this->next_ordre($id_lignes),
            'actif_escale' => 1,
            'prix_escale' => 0,
        );
        return (int) $this->create($payload);
    }

    public function exists($parent, $code_gadest, $exclude_id = NULL)
    {
        $sql = "SELECT id_escale FROM itineraire_escales WHERE id_lignes = ? AND code_gadest = ?";
        $params = array($parent, $code_gadest);
        if ($exclude_id) {
            $sql .= " AND id_escale <> ?";
            $params[] = $exclude_id;
        }
        $sql .= " LIMIT 1";
        return (bool) $this->db->query($sql, $params)->row();
    }

    /**
     * Points de départ de vente pour une gare d'affectation :
     * - origine / escales des lignes au départ de la gare ;
     * - terminus des lignes qui aboutissent à cette gare (ex. HAMELE) ;
     * - escales portant le code gare_dest lié au même lieu physique.
     */
    public function points_depart_vente($id_entreprise, $code_gaexp)
    {
        $id_entreprise = (int) $id_entreprise;
        $code_gaexp = trim((string) $code_gaexp);
        if ($id_entreprise <= 0 || $code_gaexp === '') {
            return array();
        }

        $gare = $this->db->query(
            "SELECT ge.code_gaexp, ge.garesid, ge.nom_gaep
             FROM gare_exp ge
             JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
             JOIN entreprise e ON c.id_entrep = e.id_entreprise
             WHERE e.id_entreprise = ?
               AND ge.code_gaexp = ?
             LIMIT 1",
            array($id_entreprise, $code_gaexp)
        )->row();
        if (!$gare) {
            return array();
        }

        $garesid = trim((string) $gare->garesid);
        $nom_gare = trim((string) $gare->nom_gaep);
        $dest_codes = array();
        $dest_rows = $this->db->query(
            "SELECT gd.code_gadest
             FROM gare_dest gd
             JOIN compagnies c ON gd.id_compaga = c.cle_compagnie
             JOIN entreprise e ON c.id_entrep = e.id_entreprise
             WHERE e.id_entreprise = ?
               AND (
                    gd.idgaresdest = ?
                    OR LOWER(TRIM(gd.nom_gadest)) = LOWER(?)
               )",
            array($id_entreprise, $garesid !== '' ? $garesid : $code_gaexp, $nom_gare)
        )->result();
        foreach ($dest_rows as $d) {
            $code = trim((string) $d->code_gadest);
            if ($code !== '') {
                $dest_codes[$code] = $code;
            }
        }

        $out = array();
        $seen = array();
        $push = function ($row) use (&$out, &$seen) {
            $val = isset($row->value) ? (string) $row->value : '';
            if ($val === '' || isset($seen[$val])) {
                return;
            }
            $seen[$val] = true;
            $out[] = $row;
        };

        $parents = $this->db->query(
            "SELECT DISTINCT parent.ident_ligne, parent.nom_ligne, parent.gaexp_lg, parent.gadest_lg,
                    ge.nom_gaep AS nom_depart, ga.nom_gadest AS nom_terminus
             FROM lignes parent
             JOIN gare_exp ge ON ge.code_gaexp = parent.gaexp_lg
             JOIN gare_dest ga ON ga.code_gadest = parent.gadest_lg
             JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
             JOIN entreprise e ON c.id_entrep = e.id_entreprise
             WHERE e.id_entreprise = ?
               AND parent.gaexp_lg = ?
               AND COALESCE(parent.actif_lg, 1) = 1
               AND EXISTS (
                    SELECT 1 FROM itineraire_escales ie
                    WHERE ie.id_lignes = parent.ident_ligne AND ie.actif_escale = 1
               )
             ORDER BY parent.nom_ligne, parent.ident_ligne",
            array($id_entreprise, $code_gaexp)
        )->result();
        foreach ($parents as $p) {
            $push((object) array(
                'kind' => 'origin',
                'value' => 'origin~' . $p->ident_ligne,
                'id_escale' => 0,
                'id_lignes' => $p->ident_ligne,
                'nom_ligne' => $p->nom_ligne,
                'ordre_escale' => 0,
                'label' => $p->nom_depart . ' (origine · ' . $p->nom_ligne . ')',
                'nom_point' => $p->nom_depart,
            ));
        }

        $escales = $this->db->query(
            "SELECT ie.id_escale, ie.id_lignes, ie.code_gadest, ie.nom_escale, ie.prix_escale, ie.ordre_escale,
                    parent.nom_ligne, ga.nom_gadest AS arrivee_escale
             FROM itineraire_escales ie
             JOIN lignes parent ON parent.ident_ligne = ie.id_lignes
             JOIN gare_dest ga ON ga.code_gadest = ie.code_gadest
             JOIN gare_exp ge ON ge.code_gaexp = parent.gaexp_lg
             JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
             JOIN entreprise e ON c.id_entrep = e.id_entreprise
             WHERE e.id_entreprise = ?
               AND parent.gaexp_lg = ?
               AND ie.actif_escale = 1
               AND COALESCE(parent.actif_lg, 1) = 1
             ORDER BY parent.nom_ligne, ie.ordre_escale, ie.id_escale",
            array($id_entreprise, $code_gaexp)
        )->result();
        foreach ($escales as $esc) {
            $nom = trim((string) $esc->nom_escale);
            if ($nom === '') {
                $nom = trim((string) $esc->arrivee_escale);
            }
            $push((object) array(
                'kind' => 'escale',
                'value' => 'escale~' . (int) $esc->id_escale,
                'id_escale' => (int) $esc->id_escale,
                'id_lignes' => $esc->id_lignes,
                'nom_ligne' => $esc->nom_ligne,
                'ordre_escale' => (int) $esc->ordre_escale,
                'label' => $nom . ' (' . $esc->nom_ligne . ')',
                'nom_point' => $nom,
            ));
        }

        if (!empty($dest_codes)) {
            $in = "'" . implode("','", array_map(array($this->db, 'escape_str'), array_values($dest_codes))) . "'";
            $term_parents = $this->db->query(
                "SELECT DISTINCT parent.ident_ligne, parent.nom_ligne, parent.gaexp_lg, parent.gadest_lg,
                        ge.nom_gaep AS nom_depart, ga.nom_gadest AS nom_terminus
                 FROM lignes parent
                 JOIN gare_exp ge ON ge.code_gaexp = parent.gaexp_lg
                 JOIN gare_dest ga ON ga.code_gadest = parent.gadest_lg
                 JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
                 JOIN entreprise e ON c.id_entrep = e.id_entreprise
                 WHERE e.id_entreprise = ?
                   AND parent.gadest_lg IN ({$in})
                   AND COALESCE(parent.actif_lg, 1) = 1
                 ORDER BY parent.nom_ligne, parent.ident_ligne",
                array($id_entreprise)
            )->result();
            foreach ($term_parents as $p) {
                $push((object) array(
                    'kind' => 'terminus',
                    'value' => 'terminus~' . $p->ident_ligne,
                    'id_escale' => 0,
                    'id_lignes' => $p->ident_ligne,
                    'nom_ligne' => $p->nom_ligne,
                    'ordre_escale' => 999,
                    'label' => $p->nom_terminus . ' (terminus · ' . $p->nom_ligne . ')',
                    'nom_point' => $p->nom_terminus,
                ));
            }

            $escales_local = $this->db->query(
                "SELECT ie.id_escale, ie.id_lignes, ie.code_gadest, ie.nom_escale, ie.prix_escale, ie.ordre_escale,
                        parent.nom_ligne, ga.nom_gadest AS arrivee_escale
                 FROM itineraire_escales ie
                 JOIN lignes parent ON parent.ident_ligne = ie.id_lignes
                 JOIN gare_dest ga ON ga.code_gadest = ie.code_gadest
                 JOIN gare_exp ge ON ge.code_gaexp = parent.gaexp_lg
                 JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
                 JOIN entreprise e ON c.id_entrep = e.id_entreprise
                 WHERE e.id_entreprise = ?
                   AND ie.code_gadest IN ({$in})
                   AND ie.actif_escale = 1
                   AND COALESCE(parent.actif_lg, 1) = 1
                 ORDER BY parent.nom_ligne, ie.ordre_escale, ie.id_escale",
                array($id_entreprise)
            )->result();
            foreach ($escales_local as $esc) {
                $nom = trim((string) $esc->nom_escale);
                if ($nom === '') {
                    $nom = trim((string) $esc->arrivee_escale);
                }
                $push((object) array(
                    'kind' => 'escale',
                    'value' => 'escale~' . (int) $esc->id_escale,
                    'id_escale' => (int) $esc->id_escale,
                    'id_lignes' => $esc->id_lignes,
                    'nom_ligne' => $esc->nom_ligne,
                    'ordre_escale' => (int) $esc->ordre_escale,
                    'label' => $nom . ' (' . $esc->nom_ligne . ')',
                    'nom_point' => $nom,
                ));
            }
        }

        return $out;
    }

    /**
     * Itinéraires (lignes) liés à une gare d'affectation :
     * départ, terminus, ou escale intermédiaire.
     *
     * @return object[]
     */
    public function itineraires_pour_gare($id_entreprise, $code_gaexp)
    {
        $id_entreprise = (int) $id_entreprise;
        $code_gaexp = trim((string) $code_gaexp);
        if ($id_entreprise <= 0 || $code_gaexp === '') {
            return array();
        }

        $gare = $this->db->query(
            "SELECT ge.code_gaexp, ge.garesid, ge.nom_gaep
             FROM gare_exp ge
             JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
             JOIN entreprise e ON c.id_entrep = e.id_entreprise
             WHERE e.id_entreprise = ?
               AND ge.code_gaexp = ?
             LIMIT 1",
            array($id_entreprise, $code_gaexp)
        )->row();
        if (!$gare) {
            return array();
        }

        $garesid = trim((string) $gare->garesid);
        $nom_gare = trim((string) $gare->nom_gaep);
        $dest_codes = array();
        $dest_rows = $this->db->query(
            "SELECT gd.code_gadest
             FROM gare_dest gd
             JOIN compagnies c ON gd.id_compaga = c.cle_compagnie
             JOIN entreprise e ON c.id_entrep = e.id_entreprise
             WHERE e.id_entreprise = ?
               AND (
                    gd.idgaresdest = ?
                    OR LOWER(TRIM(gd.nom_gadest)) = LOWER(?)
               )",
            array($id_entreprise, $garesid !== '' ? $garesid : $code_gaexp, $nom_gare)
        )->result();
        foreach ($dest_rows as $d) {
            $code = trim((string) $d->code_gadest);
            if ($code !== '') {
                $dest_codes[$code] = $code;
            }
        }

        $seen = array();
        $out = array();
        $push = function ($row) use (&$out, &$seen) {
            $id = isset($row->ident_ligne) ? (string) $row->ident_ligne : '';
            if ($id === '' || isset($seen[$id])) {
                return;
            }
            $seen[$id] = true;
            $out[] = $row;
        };

        // Lignes au départ de la gare
        $rows = $this->db->query(
            "SELECT parent.ident_ligne, parent.nom_ligne, parent.gaexp_lg, parent.gadest_lg,
                    ge.nom_gaep AS nom_depart, ga.nom_gadest AS nom_terminus,
                    c.cle_compagnie, c.nom_compagnie,
                    'depart' AS lien_gare
             FROM lignes parent
             JOIN gare_exp ge ON ge.code_gaexp = parent.gaexp_lg
             JOIN gare_dest ga ON ga.code_gadest = parent.gadest_lg
             JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
             JOIN entreprise e ON c.id_entrep = e.id_entreprise
             WHERE e.id_entreprise = ?
               AND parent.gaexp_lg = ?
               AND COALESCE(parent.actif_lg, 1) = 1
             ORDER BY parent.nom_ligne",
            array($id_entreprise, $code_gaexp)
        )->result();
        foreach ($rows as $r) {
            $push($r);
        }

        if (!empty($dest_codes)) {
            $in = "'" . implode("','", array_map(array($this->db, 'escape_str'), array_values($dest_codes))) . "'";

            // Lignes dont le terminus est cette gare
            $rows = $this->db->query(
                "SELECT parent.ident_ligne, parent.nom_ligne, parent.gaexp_lg, parent.gadest_lg,
                        ge.nom_gaep AS nom_depart, ga.nom_gadest AS nom_terminus,
                        c.cle_compagnie, c.nom_compagnie,
                        'terminus' AS lien_gare
                 FROM lignes parent
                 JOIN gare_exp ge ON ge.code_gaexp = parent.gaexp_lg
                 JOIN gare_dest ga ON ga.code_gadest = parent.gadest_lg
                 JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
                 JOIN entreprise e ON c.id_entrep = e.id_entreprise
                 WHERE e.id_entreprise = ?
                   AND parent.gadest_lg IN ({$in})
                   AND COALESCE(parent.actif_lg, 1) = 1
                 ORDER BY parent.nom_ligne",
                array($id_entreprise)
            )->result();
            foreach ($rows as $r) {
                $push($r);
            }

            // Lignes passant par une escale = cette gare
            $rows = $this->db->query(
                "SELECT DISTINCT parent.ident_ligne, parent.nom_ligne, parent.gaexp_lg, parent.gadest_lg,
                        ge.nom_gaep AS nom_depart, ga.nom_gadest AS nom_terminus,
                        c.cle_compagnie, c.nom_compagnie,
                        'escale' AS lien_gare
                 FROM itineraire_escales ie
                 JOIN lignes parent ON parent.ident_ligne = ie.id_lignes
                 JOIN gare_exp ge ON ge.code_gaexp = parent.gaexp_lg
                 JOIN gare_dest ga ON ga.code_gadest = parent.gadest_lg
                 JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
                 JOIN entreprise e ON c.id_entrep = e.id_entreprise
                 WHERE e.id_entreprise = ?
                   AND ie.code_gadest IN ({$in})
                   AND ie.actif_escale = 1
                   AND COALESCE(parent.actif_lg, 1) = 1
                 ORDER BY parent.nom_ligne",
                array($id_entreprise)
            )->result();
            foreach ($rows as $r) {
                $push($r);
            }
        }

        usort($out, function ($a, $b) {
            return strcasecmp((string) $a->nom_ligne, (string) $b->nom_ligne);
        });

        return $out;
    }

    /**
     * Points de départ possibles sur un itinéraire :
     * origine + escales tarifées + terminus (extrême).
     *
     * @return object[]
     */
    public function points_depart_itineraire($ident_ligne)
    {
        $ident_ligne = trim((string) $ident_ligne);
        if ($ident_ligne === '') {
            return array();
        }

        $parent = $this->db->query(
            "SELECT parent.ident_ligne, parent.nom_ligne, parent.gaexp_lg, parent.gadest_lg,
                    ge.nom_gaep AS nom_depart, ga.nom_gadest AS nom_terminus
             FROM lignes parent
             JOIN gare_exp ge ON ge.code_gaexp = parent.gaexp_lg
             JOIN gare_dest ga ON ga.code_gadest = parent.gadest_lg
             WHERE parent.ident_ligne = ?
               AND COALESCE(parent.actif_lg, 1) = 1
             LIMIT 1",
            array($ident_ligne)
        )->row();
        if (!$parent) {
            return array();
        }

        $out = array();
        $out[] = (object) array(
            'kind' => 'origin',
            'value' => 'origin~' . $parent->ident_ligne,
            'id_escale' => 0,
            'id_lignes' => $parent->ident_ligne,
            'nom_ligne' => $parent->nom_ligne,
            'ordre_escale' => 0,
            'label' => $parent->nom_depart . ' (origine)',
            'nom_point' => $parent->nom_depart,
        );

        $escales = $this->db->query(
            "SELECT ie.id_escale, ie.id_lignes, ie.code_gadest, ie.nom_escale, ie.prix_escale, ie.ordre_escale,
                    ga.nom_gadest AS arrivee_escale
             FROM itineraire_escales ie
             LEFT JOIN gare_dest ga ON ga.code_gadest = ie.code_gadest
             WHERE ie.id_lignes = ?
               AND ie.actif_escale = 1
             ORDER BY ie.ordre_escale, ie.id_escale",
            array($ident_ligne)
        )->result();

        foreach ($escales as $esc) {
            $nom = trim((string) $esc->nom_escale);
            if ($nom === '') {
                $nom = trim((string) $esc->arrivee_escale);
            }
            $out[] = (object) array(
                'kind' => 'escale',
                'value' => 'escale~' . (int) $esc->id_escale,
                'id_escale' => (int) $esc->id_escale,
                'id_lignes' => $esc->id_lignes,
                'nom_ligne' => $parent->nom_ligne,
                'ordre_escale' => (int) $esc->ordre_escale,
                'label' => $nom . ' (escale)',
                'nom_point' => $nom,
            );
        }

        $out[] = (object) array(
            'kind' => 'terminus',
            'value' => 'terminus~' . $parent->ident_ligne,
            'id_escale' => 0,
            'id_lignes' => $parent->ident_ligne,
            'nom_ligne' => $parent->nom_ligne,
            'ordre_escale' => 999,
            'label' => $parent->nom_terminus . ' (extrême / terminus)',
            'nom_point' => $parent->nom_terminus,
        );

        return $out;
    }

    /**
     * Destinations vendables depuis origin~ligne, escale~id ou terminus~ligne.
     */
    public function destinations_vente($depart_value)
    {
        $depart_value = trim((string) $depart_value);
        if ($depart_value === '') {
            return array();
        }
        $depart_value = str_replace('|', '~', $depart_value);
        if (strpos($depart_value, '~') === false) {
            return array();
        }

        list($kind, $ref) = explode('~', $depart_value, 2);
        $kind = trim($kind);
        $ref = trim($ref);
        if ($ref === '') {
            return array();
        }

        $nom_depart = '';
        $id_lignes = '';
        $exclure_id_escale = 0;
        $prix_depart_tpe_dest = null;
        $prix_depart_origine = null;
        $depart_est_origine = ($kind === 'origin');
        $depart_est_terminus = ($kind === 'terminus');

        $parent = null;
        if ($kind === 'origin' || $kind === 'terminus') {
            $parent = $this->db->query(
                "SELECT parent.ident_ligne, parent.nom_ligne, parent.gaexp_lg, parent.gadest_lg,
                        ge.nom_gaep AS nom_depart, ga.nom_gadest AS nom_terminus
                 FROM lignes parent
                 JOIN gare_exp ge ON ge.code_gaexp = parent.gaexp_lg
                 JOIN gare_dest ga ON ga.code_gadest = parent.gadest_lg
                 WHERE parent.ident_ligne = ?
                 LIMIT 1",
                array($ref)
            )->row();
            if (!$parent) {
                return array();
            }
            $id_lignes = $parent->ident_ligne;
            $nom_depart = $depart_est_terminus
                ? (string) $parent->nom_terminus
                : (string) $parent->nom_depart;
        } elseif ($kind === 'escale') {
            $has_origine_col = $this->db->field_exists('prix_escale_origine', 'itineraire_escales');
            $has_tpe_dest_col = $this->db->field_exists('prix_escale_tpe', 'itineraire_escales');
            $esc = $this->db->query(
                "SELECT ie.id_escale, ie.id_lignes, ie.ordre_escale, ie.nom_escale,
                        ie.code_gadest,
                        ga.nom_gadest AS arrivee_escale"
                . ($has_origine_col ? ', ie.prix_escale_origine' : '')
                . ($has_tpe_dest_col ? ', ie.prix_escale_tpe' : '')
                . " FROM itineraire_escales ie
                 LEFT JOIN gare_dest ga ON ga.code_gadest = ie.code_gadest
                 WHERE ie.id_escale = ?
                   AND ie.actif_escale = 1
                 LIMIT 1",
                array((int) $ref)
            )->row();
            if (!$esc) {
                return array();
            }
            $parent = $this->db->query(
                "SELECT parent.ident_ligne, parent.nom_ligne, parent.gaexp_lg, parent.gadest_lg,
                        ge.nom_gaep AS nom_depart, ga.nom_gadest AS nom_terminus
                 FROM lignes parent
                 JOIN gare_exp ge ON ge.code_gaexp = parent.gaexp_lg
                 JOIN gare_dest ga ON ga.code_gadest = parent.gadest_lg
                 WHERE parent.ident_ligne = ?
                 LIMIT 1",
                array($esc->id_lignes)
            )->row();
            if (!$parent) {
                return array();
            }
            $id_lignes = $parent->ident_ligne;
            $nom_depart = trim((string) $esc->nom_escale);
            if ($nom_depart === '') {
                $nom_depart = trim((string) $esc->arrivee_escale);
            }
            $exclure_id_escale = (int) $esc->id_escale;
            $prix_depart_tpe_dest = null;
            if ($has_tpe_dest_col && isset($esc->prix_escale_tpe)
                && $esc->prix_escale_tpe !== null && $esc->prix_escale_tpe !== ''
            ) {
                $prix_depart_tpe_dest = (float) $esc->prix_escale_tpe;
            }
            $prix_depart_origine = null;
            if ($has_origine_col && isset($esc->prix_escale_origine)
                && $esc->prix_escale_origine !== null && $esc->prix_escale_origine !== ''
            ) {
                $prix_depart_origine = (float) $esc->prix_escale_origine;
            }
        } else {
            return array();
        }

        $out = array();
        $has_origine_col = $this->db->field_exists('prix_escale_origine', 'itineraire_escales');
        $has_tpe_dest_col = $this->db->field_exists('prix_escale_tpe', 'itineraire_escales');

        /**
         * Profil vente escale : AUCUN calcul.
         * Prix lus exclusivement dans Escale TPE :
         * - prix_escale_origine → escale ↔ origine
         * - prix_escale_tpe     → escale ↔ destination/terminus
         * - itineraire_escales_tpe_liaisons → escale → escale
         * Destination absente de TPE = non proposée.
         */

        // Origine du parent (depuis une escale uniquement, si prix TPE origine configuré)
        if ($kind === 'escale' && !empty($parent->nom_depart)
            && $prix_depart_origine !== null
        ) {
            $out[] = (object) array(
                'value' => 'gaexp~' . $id_lignes,
                'id_escale' => 0,
                'id_lignes' => $id_lignes,
                'code_gadest' => $parent->gaexp_lg,
                'nom_dest' => $parent->nom_depart,
                'prix_escale' => (float) $prix_depart_origine,
                'ordre_escale' => 0,
                'label' => $nom_depart . ' - ' . $parent->nom_depart . ' (origine)',
                'nom_depart' => $nom_depart,
                'kind' => 'gaexp',
            );
        }

        $sql_esc = "SELECT ie.id_escale, ie.id_lignes, ie.code_gadest, ie.nom_escale, ie.ordre_escale,
                           ga.nom_gadest AS arrivee_escale"
            . ($has_origine_col ? ', ie.prix_escale_origine' : '')
            . ($has_tpe_dest_col ? ', ie.prix_escale_tpe' : '')
            . " FROM itineraire_escales ie
                    LEFT JOIN gare_dest ga ON ga.code_gadest = ie.code_gadest
                    WHERE ie.id_lignes = ?
                      AND ie.actif_escale = 1";
        $params_esc = array($id_lignes);
        if ($exclure_id_escale > 0) {
            $sql_esc .= " AND ie.id_escale <> ?";
            $params_esc[] = $exclure_id_escale;
        }
        $sql_esc .= " ORDER BY ie.ordre_escale, ie.id_escale";
        $rows = $this->db->query($sql_esc, $params_esc)->result();

        foreach ($rows as $row) {
            $nom_dest = trim((string) $row->nom_escale);
            if ($nom_dest === '') {
                $nom_dest = trim((string) $row->arrivee_escale);
            }
            $prix = null;
            $label_suffix = ' (escale)';

            if ($kind === 'escale' && $exclure_id_escale > 0) {
                // Uniquement liaison TPE escale → escale
                $prix_liaison = $this->get_prix_liaison_tpe($exclure_id_escale, (int) $row->id_escale);
                if ($prix_liaison === null) {
                    continue;
                }
                $prix = (float) $prix_liaison;
                $label_suffix = ' (escale→escale)';
            } elseif ($kind === 'origin') {
                // Origine → escale : prix_escale_origine de l'escale destination
                if (!$has_origine_col || !isset($row->prix_escale_origine)
                    || $row->prix_escale_origine === null || $row->prix_escale_origine === ''
                ) {
                    continue;
                }
                $prix = (float) $row->prix_escale_origine;
            } elseif ($kind === 'terminus') {
                // Terminus → escale : prix_escale_tpe de l'escale (même segment)
                if (!$has_tpe_dest_col || !isset($row->prix_escale_tpe)
                    || $row->prix_escale_tpe === null || $row->prix_escale_tpe === ''
                ) {
                    continue;
                }
                $prix = (float) $row->prix_escale_tpe;
            } else {
                continue;
            }

            $out[] = (object) array(
                'value' => 'escale~' . (int) $row->id_escale,
                'id_escale' => (int) $row->id_escale,
                'id_lignes' => $row->id_lignes,
                'code_gadest' => $row->code_gadest,
                'nom_dest' => $nom_dest,
                'prix_escale' => $prix,
                'ordre_escale' => (int) $row->ordre_escale,
                'label' => $nom_depart . ' - ' . $nom_dest . $label_suffix,
                'nom_depart' => $nom_depart,
                'kind' => 'escale',
            );
        }

        // Terminus du parent (depuis une escale uniquement, si prix TPE destination configuré)
        if ($kind === 'escale' && !empty($parent->nom_terminus)
            && $prix_depart_tpe_dest !== null
        ) {
            $out[] = (object) array(
                'value' => 'terminus~' . $id_lignes,
                'id_escale' => 0,
                'id_lignes' => $id_lignes,
                'code_gadest' => $parent->gadest_lg,
                'nom_dest' => $parent->nom_terminus,
                'prix_escale' => (float) $prix_depart_tpe_dest,
                'ordre_escale' => 999,
                'label' => $nom_depart . ' - ' . $parent->nom_terminus . ' (extrême)',
                'nom_depart' => $nom_depart,
                'kind' => 'terminus',
            );
        }

        $uniq = array();
        $deduped = array();
        foreach ($out as $row) {
            $k = isset($row->value) ? (string) $row->value : '';
            if ($k === '' || isset($uniq[$k])) {
                continue;
            }
            $uniq[$k] = true;
            $deduped[] = $row;
        }

        return $deduped;
    }

    /**
     * Prix Escales tarifées = segment escale → destination du parent.
     * Pour le sens inverse (ex. Boromo→Ouaga alors que l'affectation est sur Ouaga–Bobo),
     * lit le prix Boromo sur la ligne parent inverse (Bobo–Ouaga).
     *
     * @param object $parent ligne courante (gaexp, gadest, nom_ligne, ident_ligne)
     * @param string $code_gadest_escale
     * @param string $nom_escale
     * @return float|null
     */
    protected function _prix_escale_destination_sur_ligne_inverse($parent, $code_gadest_escale, $nom_escale)
    {
        $inverse = $this->_trouver_ligne_parent_inverse($parent);
        if (!$inverse) {
            return null;
        }

        $code_gadest_escale = trim((string) $code_gadest_escale);
        $nom_escale = trim((string) $nom_escale);

        if ($code_gadest_escale !== '') {
            $row = $this->db->query(
                "SELECT prix_escale FROM itineraire_escales
                 WHERE id_lignes = ?
                   AND code_gadest = ?
                   AND actif_escale = 1
                 LIMIT 1",
                array($inverse->ident_ligne, $code_gadest_escale)
            )->row();
            if ($row && $row->prix_escale !== null && $row->prix_escale !== '') {
                return (float) $row->prix_escale;
            }
        }

        if ($nom_escale !== '') {
            $row = $this->db->query(
                "SELECT ie.prix_escale
                 FROM itineraire_escales ie
                 LEFT JOIN gare_dest ga ON ga.code_gadest = ie.code_gadest
                 WHERE ie.id_lignes = ?
                   AND ie.actif_escale = 1
                   AND (
                        UPPER(TRIM(ie.nom_escale)) = UPPER(TRIM(?))
                     OR UPPER(TRIM(COALESCE(ga.nom_gadest, ''))) = UPPER(TRIM(?))
                   )
                 ORDER BY ie.ordre_escale ASC, ie.id_escale ASC
                 LIMIT 1",
                array($inverse->ident_ligne, $nom_escale, $nom_escale)
            )->row();
            if ($row && $row->prix_escale !== null && $row->prix_escale !== '') {
                return (float) $row->prix_escale;
            }
        }

        return null;
    }

    /**
     * Ligne parent inverse par nom (Ouaga-Bobo → Bobo-Ouaga).
     *
     * @param object $parent
     * @return object|null ident_ligne, nom_ligne, gaexp_lg, gadest_lg
     */
    protected function _trouver_ligne_parent_inverse($parent)
    {
        if (!$parent || empty($parent->ident_ligne)) {
            return null;
        }

        $nom = trim((string) (isset($parent->nom_ligne) ? $parent->nom_ligne : ''));
        $nomRetour = '';
        if ($nom !== '' && strpos($nom, '-') !== false) {
            $parts = explode('-', $nom);
            if (count($parts) >= 2) {
                $nomRetour = trim($parts[count($parts) - 1]) . '-' . trim($parts[0]);
            }
        }
        $nomRetourBase = $nomRetour;
        if ($nomRetourBase !== '') {
            $tmp = preg_replace('/_(VIP|CMT|ORD|EXPRESS|STD|CMTSD|VIPSD)$/i', '', $nomRetourBase);
            if (is_string($tmp) && $tmp !== '') {
                $nomRetourBase = $tmp;
            }
        }

        if ($nomRetourBase !== '') {
            $row = $this->db->query(
                "SELECT lg.ident_ligne, lg.nom_ligne, lg.gaexp_lg, lg.gadest_lg
                 FROM lignes lg
                 WHERE COALESCE(lg.actif_lg, 1) = 1
                   AND UPPER(TRIM(lg.nom_ligne)) = UPPER(TRIM(?))
                 ORDER BY
                   CASE WHEN lg.nom_ligne = ? THEN 0 ELSE 1 END ASC,
                   lg.ident_ligne ASC
                 LIMIT 1",
                array($nomRetourBase, $nomRetour)
            )->row();
            if ($row) {
                return $row;
            }
            // Tolère suffixes compagnie sur le nom inverse.
            $row = $this->db->query(
                "SELECT lg.ident_ligne, lg.nom_ligne, lg.gaexp_lg, lg.gadest_lg
                 FROM lignes lg
                 WHERE COALESCE(lg.actif_lg, 1) = 1
                   AND UPPER(TRIM(lg.nom_ligne)) LIKE UPPER(TRIM(?))
                 ORDER BY lg.ident_ligne ASC
                 LIMIT 1",
                array($nomRetourBase . '%')
            )->row();
            if ($row) {
                return $row;
            }
        }

        // Secours : miroir gaexp/gadest via noms de gares.
        $ga = trim((string) (isset($parent->gaexp_lg) ? $parent->gaexp_lg : ''));
        $gd = trim((string) (isset($parent->gadest_lg) ? $parent->gadest_lg : ''));
        if ($ga === '' || $gd === '') {
            return null;
        }

        return $this->db->query(
            "SELECT lg.ident_ligne, lg.nom_ligne, lg.gaexp_lg, lg.gadest_lg
             FROM lignes lg
             JOIN gare_exp exA ON exA.code_gaexp = ?
             JOIN gare_dest gaA ON gaA.code_gadest = ?
             JOIN gare_exp exR ON exR.code_gaexp = lg.gaexp_lg
             JOIN gare_dest gaR ON gaR.code_gadest = lg.gadest_lg
             WHERE COALESCE(lg.actif_lg, 1) = 1
               AND UPPER(TRIM(exR.nom_gaep)) = UPPER(TRIM(gaA.nom_gadest))
               AND UPPER(TRIM(gaR.nom_gadest)) = UPPER(TRIM(exA.nom_gaep))
             ORDER BY lg.ident_ligne ASC
             LIMIT 1",
            array($ga, $gd)
        )->row();
    }

    /**
     * Prix escale → destination exclusif profil vente escale (TPE).
     * Strict : uniquement prix_escale_tpe (pas de fallback Escales tarifées).
     *
     * @param object $row
     * @return float|null
     */
    protected function _prix_tpe_destination($row)
    {
        if (isset($row->prix_escale_tpe) && $row->prix_escale_tpe !== null && $row->prix_escale_tpe !== '') {
            return (float) $row->prix_escale_tpe;
        }
        return null;
    }

    /**
     * Prix d'un segment (départ origine / terminus) — hors affectation escale Venteescal.
     *
     * @param string $depart_kind origin|escale|terminus
     * @param float|null $prix_adulte
     * @param float|null $prix_depart_escale
     * @param float $prix_dest_escale
     * @return float
     */
    protected function _prix_segment_escale($depart_kind, $prix_adulte, $prix_depart_escale, $prix_dest_escale)
    {
        $prix_dest_escale = (float) $prix_dest_escale;
        if ($depart_kind === 'terminus' && $prix_adulte !== null) {
            $calc = (float) $prix_adulte - $prix_dest_escale;
            return $calc > 0 ? $calc : $prix_dest_escale;
        }
        if ($depart_kind === 'escale' && $prix_depart_escale !== null) {
            $calc = abs($prix_dest_escale - (float) $prix_depart_escale);
            return $calc > 0 ? $calc : 0.0;
        }
        // Départ origine : tarif configuré jusqu'à l'escale (catalogue classique).
        return $prix_dest_escale;
    }

    /**
     * Prix vers le terminus depuis origin/escale (hors règle escale→destination parent).
     *
     * @param string $depart_kind
     * @param float|null $prix_adulte
     * @param float|null $prix_depart_escale
     * @return float
     */
    protected function _prix_segment_vers_terminus($depart_kind, $prix_adulte, $prix_depart_escale)
    {
        if ($depart_kind === 'escale' && $prix_adulte !== null && $prix_depart_escale !== null) {
            $calc = (float) $prix_adulte - (float) $prix_depart_escale;
            return $calc > 0 ? $calc : 0.0;
        }
        if ($prix_adulte !== null) {
            return (float) $prix_adulte;
        }
        if ($prix_depart_escale !== null) {
            return (float) $prix_depart_escale;
        }
        return 0.0;
    }

    /**
     * Tarif adulte (id_tarifs=1) sur un horaire actif de la ligne parent.
     */
    protected function _prix_adulte_ligne($id_lignes)
    {
        $row = $this->db->query(
            "SELECT tf.prix
             FROM tarification tf
             JOIN ligne_heure lh ON tf.ligne_heure_id = lh.id_ligneheure
             JOIN heures h ON lh.heure_identif = h.id_heure
             WHERE lh.ligne_id = ?
               AND tf.typetarif_id = 1
               AND COALESCE(lh.actif_lh, 1) = 1
               AND COALESCE(h.h_active, 1) = 1
             ORDER BY lh.id_ligneheure ASC
             LIMIT 1",
            array($id_lignes)
        )->row();
        if ($row && $row->prix !== null && $row->prix !== '') {
            return (float) $row->prix;
        }
        return null;
    }
}