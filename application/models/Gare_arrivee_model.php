<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Gare_arrivee_model extends CI_Model
    {
        protected $table = 'gare_dest';
        
        public function __construct()
        {
            parent::__construct();
        }
        

        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
            
                
        public function update($code_gadest, array $data)
        {
            return $this->db->where('code_gadest', $code_gadest)
            ->update($this->table, $data);
        }

        public function del($id)
        {
            return $this->db->where('code_gadest', $id)->delete($this->table);
        }
    
        public function getad($cid, $ga_id = FALSE)
        {
            if ($ga_id === FALSE) {
                return $this->db->query(
                    "SELECT * FROM gare_dest ga
                    JOIN ville v ON ga.id_villega = v.id_ville
                    JOIN pays p ON v.id_pay = p.id_pays
                    JOIN compagnies c ON ga.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'
                    ORDER BY ga.nom_gadest")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM gare_dest ga
                    JOIN ville v ON ga.id_villega = v.id_ville
                    JOIN pays p ON v.id_pay = p.id_pays
                    JOIN compagnies c ON ga.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'
                    AND ga.code_gadest = '$ga_id'
                    ORDER BY ga.nom_gadest")->row();
        }
		
        public function g($ga_id)
        {
            return $this->db->query(
                "SELECT gd.nom_gaep FROM gare_exp gd
                WHERE gd.code_gaexp = '$ga_id'")->row();
        }
		/*public function get($cid, $g, $ga_id = FALSE)
        {
			
            if ($ga_id === FALSE) {
                return $this->db->query(
                    "SELECT * FROM gare_dest ga
                    JOIN ville v ON ga.id_villega = v.id_ville
                    JOIN compagnies c ON ga.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'
					AND ga.nom_gadest NOT IN (SELECT nom_gaep FROM gare_exp WHERE code_gaexp = '$g')
                    ORDER BY ga.nom_gadest")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM gare_dest ga
                    JOIN ville v ON ga.id_villega = v.id_ville
                    JOIN compagnies c ON ga.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'
					AND ga.nom_gadest NOT IN (SELECT nom_gaep FROM gare_exp WHERE code_gaexp = '$g')
                    AND ga.code_gadest = '$ga_id'
                    ORDER BY ga.nom_gadest")->row();
        }*/

        public function get($cid, $g, $ga_id = FALSE)
        {
            if ($ga_id === FALSE) {
                return $this->db->query(
                    "SELECT * FROM gare_dest ga
                    JOIN ville v ON ga.id_villega = v.id_ville
                    JOIN pays p ON v.id_pay = p.id_pays
                    JOIN compagnies c ON ga.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'
                    AND ga.nom_gadest !='OUAGAESCAL'
                    AND ga.nom_gadest NOT IN (SELECT nom_gaep FROM gare_exp WHERE code_gaexp ='$g')
                    ORDER BY ga.nom_gadest")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM gare_dest ga
                    JOIN ville v ON ga.id_villega = v.id_ville
                    JOIN pays p ON v.id_pay = p.id_pays
                    JOIN compagnies c ON ga.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'
                    AND ga.nom_gadest !='OUAGAESCAL'
                    AND ga.code_gadest = '$ga_id'
                    AND ga.nom_gadest NOT IN (SELECT nom_gaep FROM gare_exp WHERE code_gaexp ='$g')
                    ORDER BY ga.nom_gadest")->row();
        }
        

        public function gprincipale($cid, $prc, $h)
        {
            
            return $this->db->query(
                "SELECT * FROM statutheuregare s
                JOIN heures h ON s.idheure = h.id_heure
                JOIN statutgare sg ON s.idstatgare = sg.idstatutgare
                JOIN gare_dest ga ON s.idgarearrive = ga.code_gadest
                JOIN ville v ON ga.id_villega = v.id_ville
                JOIN compagnies c ON ga.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND ga.code_gadest ='$prc'
                AND h.heure = '$h'")->result();
        }
    }
    /** Gare_arrivee_model.php **/
    /** application/models/Gare_arrivee_model.php **/
