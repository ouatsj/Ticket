<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Tamponcodetr_model extends CI_Model
    {
        protected $table = 'tamponcodetr';
        
        public function __construct()
        {
            parent::__construct();
        }
        
        public function create(array $data)
        {
            // Compat : certains chemins de vente passent la clé 'tamponcodtr'
            // (colonne de la table tamponcode) alors que cette table utilise
            // 'codtampon'. On remappe pour éviter l'échec d'insertion.
            if (isset($data['tamponcodtr']) && !isset($data['codtampon'])) {
                $data['codtampon'] = $data['tamponcodtr'];
                unset($data['tamponcodtr']);
            }

            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
            
                
        public function update($tamponcodt, array $data)
        {
            return $this->db->where('codtampon', $tamponcodt)
            ->update($this->table, $data);
        }

        public function del($id)
        {
            return $this->db->where('codtampon', $id)->delete($this->table);
        }
    }
    /** Tamponcodetr_model.php **/
    /** application/models/Tamponcodetr_model.php **/
