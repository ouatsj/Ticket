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
                    ga.nom_gadest AS arrivee_escale
                FROM itineraire_escales ie
                JOIN lignes parent ON parent.ident_ligne = ie.id_lignes
                JOIN gare_exp ge ON ge.code_gaexp = parent.gaexp_lg
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

    public function next_ordre($parent)
    {
        $row = $this->db->query(
            "SELECT COALESCE(MAX(ordre_escale), 0) AS m FROM itineraire_escales WHERE id_lignes = ?",
            array($parent)
        )->row();
        return (int) $row->m + 1;
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
        $prix_depart_escale = null;
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
            $esc = $this->db->query(
                "SELECT ie.id_escale, ie.id_lignes, ie.ordre_escale, ie.nom_escale, ie.prix_escale,
                        ga.nom_gadest AS arrivee_escale
                 FROM itineraire_escales ie
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
            $prix_depart_escale = (float) $esc->prix_escale;
        } else {
            return array();
        }

        $out = array();
        $prix_adulte = $this->_prix_adulte_ligne($id_lignes);

        // Ordre d'affichage : origine → escales → extrême (terminus).
        if (!$depart_est_origine && !empty($parent->nom_depart)) {
            if ($depart_est_terminus) {
                $prix_origine = $prix_adulte !== null ? (float) $prix_adulte : 0.0;
            } else {
                $prix_origine = $prix_depart_escale !== null ? (float) $prix_depart_escale : 0.0;
            }
            $out[] = (object) array(
                'value' => 'gaexp~' . $id_lignes,
                'id_escale' => 0,
                'id_lignes' => $id_lignes,
                'code_gadest' => $parent->gaexp_lg,
                'nom_dest' => $parent->nom_depart,
                'prix_escale' => $prix_origine,
                'ordre_escale' => 0,
                'label' => $nom_depart . ' - ' . $parent->nom_depart . ' (origine)',
                'nom_depart' => $nom_depart,
                'kind' => 'gaexp',
            );
        }

        $sql_esc = "SELECT ie.id_escale, ie.id_lignes, ie.code_gadest, ie.nom_escale, ie.prix_escale, ie.ordre_escale,
                           ga.nom_gadest AS arrivee_escale
                    FROM itineraire_escales ie
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
            $prix = (float) $row->prix_escale;
            if ($depart_est_terminus && $prix_adulte !== null) {
                $calc = (float) $prix_adulte - (float) $row->prix_escale;
                $prix = $calc > 0 ? $calc : (float) $row->prix_escale;
            }
            $out[] = (object) array(
                'value' => 'escale~' . (int) $row->id_escale,
                'id_escale' => (int) $row->id_escale,
                'id_lignes' => $row->id_lignes,
                'code_gadest' => $row->code_gadest,
                'nom_dest' => $nom_dest,
                'prix_escale' => $prix,
                'ordre_escale' => (int) $row->ordre_escale,
                'label' => $nom_depart . ' - ' . $nom_dest . ' (escale)',
                'nom_depart' => $nom_depart,
                'kind' => 'escale',
            );
        }

        if (!$depart_est_terminus && !empty($parent->nom_terminus)) {
            $prix_terminus = $prix_adulte;
            if ($prix_terminus === null && $prix_depart_escale !== null) {
                $prix_terminus = (float) $prix_depart_escale;
            }
            if ($prix_terminus === null) {
                $prix_terminus = 0.0;
            }
            $out[] = (object) array(
                'value' => 'terminus~' . $id_lignes,
                'id_escale' => 0,
                'id_lignes' => $id_lignes,
                'code_gadest' => $parent->gadest_lg,
                'nom_dest' => $parent->nom_terminus,
                'prix_escale' => (float) $prix_terminus,
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
