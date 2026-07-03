<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Personnels_model extends CI_Model
    {
        protected $table = 'personnels';
        
        public function __construct()
        {
            parent::__construct();
        }
    
        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
            
                
        public function update($matricule, array $data)
        {
            return $this->db->where('matricule', $matricule)
            ->update($this->table, $data);
        }

        public function del($id)
        {
        return $this->db->where('matricule', $id)->delete($this->table);
        }

        public function get($cid, $perso_mat = FALSE)
        {
            if ($perso_mat === FALSE) {
                return $this->db->query(
                    "SELECT * FROM personnels per
                    JOIN compagnies c ON per.compagnie_perso = c.cle_compagnie
                    JOIN type_personnel tp ON per.type_perso = tp.idtyperso
                    AND per.actif_perso = 1
					ORDER BY per.nomprenom_perso ASC")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM personnels per
                    JOIN compagnies c ON per.compagnie_perso = c.cle_compagnie
                    JOIN type_personnel tp ON per.type_perso = tp.idtyperso
                    WHERE per.matricule = '$perso_mat'
                    AND per.actif_perso = 1
					ORDER BY per.nomprenom_perso ASC")->row();
        }

        public function getp($cid, $perso_mat = FALSE)
        {
            if ($perso_mat === FALSE) {
                return $this->db->query(
                    "SELECT * FROM personnels per
                    JOIN compagnies c ON per.compagnie_perso = c.cle_compagnie
                    JOIN type_personnel tp ON per.type_perso = tp.idtyperso
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
					ORDER BY per.nomprenom_perso ASC")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM personnels per
                    JOIN compagnies c ON per.compagnie_perso = c.cle_compagnie
                    JOIN type_personnel tp ON per.type_perso = tp.idtyperso
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND per.matricule = '$perso_mat'
					ORDER BY per.nomprenom_perso ASC")->row();
        }
        
        public function lookfor($username)
        {
            return $this->db->get_where('personnels', array('matricule' => $username))
                ->row();
        }
        
        public function getch($cid)
        {
                return $this->db->query(
                    "SELECT * FROM personnels per
                    JOIN compagnies c ON per.compagnie_perso = c.cle_compagnie
                    JOIN type_personnel tp ON per.type_perso = tp.idtyperso
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND tp.type_personnel = 'Chauffeur'
                    AND per.actif_perso = 1
					ORDER BY per.nomprenom_perso ASC")->result();
        }

        public function getconv($cid)
        {
                return $this->db->query(
                    "SELECT * FROM personnels per
                    JOIN compagnies c ON per.compagnie_perso = c.cle_compagnie
                    JOIN type_personnel tp ON per.type_perso = tp.idtyperso
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND tp.type_personnel = 'Convoyeur'
                    AND per.actif_perso = 1
					ORDER BY per.nomprenom_perso ASC")->result();
        }

        public function getchs($cid, $ch)
        {
                return $this->db->query(
                    "SELECT * FROM personnels per
                    JOIN compagnies c ON per.compagnie_perso = c.cle_compagnie
                    JOIN type_personnel tp ON per.type_perso = tp.idtyperso
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND tp.type_personnel = '$ch'
                    AND per.actif_perso = 1
                    ORDER BY per.nomprenom_perso ASC")->result();
        }

        public function getconvs($cid, $cv)
        {
                return $this->db->query(
                    "SELECT * FROM personnels per
                    JOIN compagnies c ON per.compagnie_perso = c.cle_compagnie
                    JOIN type_personnel tp ON per.type_perso = tp.idtyperso
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND tp.type_personnel = '$cv'
                    AND per.actif_perso = 1
                    ORDER BY per.nomprenom_perso ASC")->result();
        }

        public function getinfo($perso_mat)
        {
            
                return $this->db->query(
                    "SELECT * FROM personnels per
                    JOIN compagnies c ON per.compagnie_perso = c.cle_compagnie
                    JOIN type_personnel tp ON per.type_perso = tp.idtyperso
                    WHERE per.matricule = '$perso_mat'")->row();
        }

        public function infop()
        {
            
            return $this->db->query(
                "SELECT * FROM personnels per
                JOIN compagnies c ON per.compagnie_perso = c.cle_compagnie
                JOIN type_personnel tp ON per.type_perso = tp.idtyperso
                WHERE per.persoactif = 0
                ORDER BY per.nomprenom_perso ASC")->result();
        }

        public function infopr($cid, $id)
        {
            
            return $this->db->query(
                "SELECT * FROM personnels per
                JOIN compagnies c ON per.compagnie_perso = c.cle_compagnie
                JOIN type_personnel tp ON per.type_perso = tp.idtyperso
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND per.matricule = '$id'
                AND per.persoactif = 0")->row();
        }
    }
    /** Personnels_model.php **/
    /** application/models/Personnels_model.php **/