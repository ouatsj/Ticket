<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Type_document_model extends CI_Model
    {
        protected $table = 'type_document';
        
        public function __construct()
        {
            parent::__construct();
        }
        
        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
            
                
        public function update($id_ty, array $data)
        {
            return $this->db->where('typedoc', $id_ty)
            ->update($this->table, $data);
        }

        public function del($id)
        {
            return $this->db->where('typedoc', $id)->delete($this->table);
        }
        
        
        public function get($td = FALSE)
        {
            $key = mdate("%Y-%m-%d", now());

            if ($td === FALSE) {
                return $this->db->query(
                    "SELECT * FROM type_document td")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM type_document td
                    WHERE td.typedoc = '$td'")->row();
        }
        

    }
    /** Type_document_model.php **/
    /** application/models/Type_document_model.php **/
