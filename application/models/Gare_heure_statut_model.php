<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Gare_heure_statut_model extends CI_Model
    {
        protected $table = 'statutheuregare';
        
        public function __construct()
        {
            parent::__construct();
        }
        
       
        public function get($cid, $st_id = FALSE)
        {
            if ($st_id === FALSE) {
                return $this->db->query(
                    "SELECT * FROM statutheuregare s
                    JOIN heures h ON s.idheure = h.id_heure
                    JOIN statutgare sg ON s.idstatgare = sg.idstatutgare
                    JOIN gare_dest ga ON s.idgarearrive = ga.code_gadest
                    JOIN ville v ON ga.id_villega = v.id_ville
                    JOIN compagnies c ON ga.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM statutheuregare s
                    JOIN heures h ON s.idheure = h.id_heure
                    JOIN statutgare sg ON s.idstatgare = sg.idstatutgare
                    JOIN gare_dest ga s.idgarearrive = ga.code_gadest
                    JOIN ville v ON ga.id_villega = v.id_ville
                    JOIN compagnies c ON ga.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid' 
                    AND s.idsthg = '$st_id'")->row();
        }

        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
            
                
        public function update($idsthg, array $data)
        {
            return $this->db->where('idsthg', $idsthg)
            ->update($this->table, $data);
        }

        public function del($id)
        {
        return $this->db->where('idsthg', $id)->delete($this->table);
        }
    }
    /** Gare_heure_statut_model.php **/
    /** application/models/Gare_heure_statut_model.php **/