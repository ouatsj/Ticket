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
}
