<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Tampon_siege_model extends CI_Model
    {
        protected $table = 'tampon_siege';
        
        public function __construct()
        {
            parent::__construct();
        }
        
    
        public function get($p, $n)
        {
            
            return $this->db->query(
                "SELECT * FROM tampon_siege t WHERE t.codepro = '$p' AND t.numsieg = '$n'")->result();
        }

        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
            
                
        public function update($idtamp, array $data)
        {
            return $this->db->where('idtamp', $idtamp)
            ->update($this->table, $data);
        }

        public function del($id)
        {
            return $this->db->where('idtamp', $id)->delete($this->table);
        }
    
    }
    /** Tampon_siege_model.php **/
    /** application/models/Tampon_siege_model.php **/
