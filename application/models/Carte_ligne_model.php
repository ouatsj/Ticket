<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Carte_ligne_model extends CI_Model
    {
        protected $table = 'affectlignecarte';
        
        public function __construct()
        {
            parent::__construct();
        }
        
        public function verifcartlg($idcartlg) 
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
            return $this->db->query(
                "SELECT * FROM affectlignecarte alc
                JOIN carte_passager cv ON alc.idcarte = cv.id_carte
                JOIN lignes l ON alc.idlignecart = l.ident_ligne
                WHERE cv.num_carte ='$idcart'
                AND cv.actif_validite = 0
                AND cv.date_expire >= '$today'")->row();
        }

        
        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
            
          
        public function update($lgcarte, array $data)
        {
            return $this->db->where('lgcarte', $lgcarte)
            ->update($this->table, $data);
        }

        public function del($id)
        {
            return $this->db->where('lgcarte', $id)->delete($this->table);
        }  
        
    }
    /** Carte_ligne_model.php **/
    /** application/models/Carte_ligne_model.php **/
