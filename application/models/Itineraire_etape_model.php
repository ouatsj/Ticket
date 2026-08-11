<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Composition transit : une ligne conteneur + N lignes/itinéraires ordonnés.
 * Remplace le modèle « segments » (itineraires créés sous une ligne).
 */
class Itineraire_etape_model extends CI_Model
{
    protected $table = 'itineraire_etapes';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Liste admin des étapes (forme compatible ancienne UI).
     */
    public function get($cid, $etape_id = FALSE)
    {
        $sql = "SELECT
                    et.id_etape AS id_tabitinligne,
                    et.id_etape AS id_itineraire,
                    et.id_lignes,
                    et.ident_ligne_etape AS code_itineraires,
                    et.ordre_etape,
                    et.actif_etape AS actifint,
                    et.actif_etape AS actiftine,
                    lg.nom_ligne AS nom_itineraires,
                    ge.nom_gaep AS depart_itine,
                    ga.nom_gadest AS arrive_itine,
                    ge.code_gaexp,
                    ga.code_gadest,
                    ge.nom_gaep,
                    ga.nom_gadest,
                    ga.id_compaga,
                    parent.nom_ligne,
                    parent.gaexp_lg,
                    parent.gadest_lg
                FROM itineraire_etapes et
                JOIN lignes lg ON lg.ident_ligne = et.ident_ligne_etape
                JOIN lignes parent ON parent.ident_ligne = et.id_lignes
                JOIN gare_exp ge ON ge.code_gaexp = lg.gaexp_lg
                JOIN gare_dest ga ON ga.code_gadest = lg.gadest_lg
                JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.id_entreprise = ?
                ";

        if ($etape_id === FALSE) {
            $sql .= " ORDER BY et.id_lignes, et.ordre_etape, et.id_etape";
            return $this->db->query($sql, array($cid))->result();
        }

        $sql .= " AND et.id_etape = ? ORDER BY et.ordre_etape LIMIT 1";
        return $this->db->query($sql, array($cid, $etape_id))->row();
    }

    /**
     * Étapes actives d'une ligne conteneur (vente / verifitine).
     * $parent_ligne = ident_ligne de l'axe (ex. BAN3-BOU20).
     */
    public function get_by_parent($ekey, $parent_ligne)
    {
        $sql = "SELECT
                    et.id_etape AS id_tabitinligne,
                    et.id_etape AS id_itineraire,
                    et.id_lignes,
                    et.ident_ligne_etape AS code_itineraires,
                    et.ordre_etape,
                    et.actif_etape AS actifint,
                    et.actif_etape AS actiftine,
                    lg.nom_ligne AS nom_itineraires,
                    ge.nom_gaep AS depart_itine,
                    ga.nom_gadest AS arrive_itine,
                    ge.code_gaexp,
                    ga.code_gadest,
                    ge.nom_gaep,
                    ga.nom_gadest,
                    ga.id_compaga,
                    parent.nom_ligne,
                    parent.ident_ligne,
                    parent.gaexp_lg,
                    parent.gadest_lg
                FROM itineraire_etapes et
                JOIN lignes lg ON lg.ident_ligne = et.ident_ligne_etape
                JOIN lignes parent ON parent.ident_ligne = et.id_lignes
                JOIN gare_exp ge ON ge.code_gaexp = lg.gaexp_lg
                JOIN gare_dest ga ON ga.code_gadest = lg.gadest_lg
                JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = ?
                  AND et.id_lignes = ?
                  AND et.actif_etape = 1
                ORDER BY et.ordre_etape ASC, et.id_etape ASC";

        return $this->db->query($sql, array($ekey, $parent_ligne))->result();
    }

    /**
     * Filtre Confirmation : étapes dont code ∈ liste.
     */
    public function get_by_parent_codes($ekey, $parent_ligne, $codes_csv)
    {
        $codes = array_filter(array_map('trim', explode(',', str_replace("'", '', $codes_csv))));
        if (empty($codes)) {
            return array();
        }

        $placeholders = implode(',', array_fill(0, count($codes), '?'));
        $sql = "SELECT
                    et.id_etape AS id_tabitinligne,
                    et.id_etape AS id_itineraire,
                    et.id_lignes,
                    et.ident_ligne_etape AS code_itineraires,
                    et.ordre_etape,
                    et.actif_etape AS actifint,
                    et.actif_etape AS actiftine,
                    lg.nom_ligne AS nom_itineraires,
                    ge.nom_gaep AS depart_itine,
                    ga.nom_gadest AS arrive_itine,
                    ga.id_compaga,
                    parent.nom_ligne
                FROM itineraire_etapes et
                JOIN lignes lg ON lg.ident_ligne = et.ident_ligne_etape
                JOIN lignes parent ON parent.ident_ligne = et.id_lignes
                JOIN gare_exp ge ON ge.code_gaexp = lg.gaexp_lg
                JOIN gare_dest ga ON ga.code_gadest = lg.gadest_lg
                JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = ?
                  AND et.id_lignes = ?
                  AND et.ident_ligne_etape IN ($placeholders)
                ORDER BY et.ordre_etape ASC";

        $params = array_merge(array($ekey, $parent_ligne), $codes);
        return $this->db->query($sql, $params)->result();
    }

    public function create(array $data)
    {
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function update($id_etape, array $data)
    {
        return $this->db->where('id_etape', $id_etape)->update($this->table, $data);
    }

    public function delete_by_parent($parent_ligne)
    {
        return $this->db->where('id_lignes', $parent_ligne)->delete($this->table);
    }

    public function next_ordre($parent_ligne)
    {
        $row = $this->db->query(
            "SELECT COALESCE(MAX(ordre_etape), 0) AS m FROM itineraire_etapes WHERE id_lignes = ?",
            array($parent_ligne)
        )->row();
        return (int) $row->m + 1;
    }

    /**
     * Remplace toute la composition d'une ligne conteneur.
     * $etapes = array d'ident_ligne (ordre = index+1), max 4.
     */
    public function replace_composition($parent_ligne, array $etapes)
    {
        $etapes = array_values(array_filter(array_unique($etapes)));
        if (count($etapes) < 2 || count($etapes) > 4) {
            return FALSE;
        }
        if (in_array($parent_ligne, $etapes, true)) {
            return FALSE;
        }

        $this->db->trans_start();
        $this->delete_by_parent($parent_ligne);
        $ordre = 1;
        foreach ($etapes as $ident) {
            $this->create(array(
                'id_lignes' => $parent_ligne,
                'ident_ligne_etape' => $ident,
                'ordre_etape' => $ordre,
                'actif_etape' => 1,
            ));
            $ordre++;
        }
        $this->db->trans_complete();
        return $this->db->trans_status();
    }
}
