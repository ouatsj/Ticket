<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Client_model extends CI_Model
    {
        protected $table = 'client';
        
        public function __construct()
        {
            parent::__construct();
        }
        
        
        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
            
                
        public function update($id_client, array $data)
        {
            return $this->db->where('id_client', $id_client)
            ->update($this->table, $data);
        }

        public function del($id)
        {
        return $this->db->where('id_client', $id)->delete($this->table);
        }
    

        public function infocl($num)
        {
            return $this->db->query("SELECT cl.id_client, cl.type_client, cl.contact_client, cl.nom_client, cl.prenom_client, cl.num_CNIB, cl.date_delivre, cl.lieu_delivre FROM client cl  
                WHERE cl.contact_client = '$num'
                AND cl.type_client <> 'autre'
                AND cl.type_client <> 'eleve'
                AND cl.type_client <> 'enfant'
                AND cl.type_client <> 'etudiant'
                AND cl.type_client <> 'client'
                AND cl.type_client <> 'autrepersonnel'
                ORDER BY cl.id_client DESC LIMIT 1")->row();
            
        }

        public function infocl2($num)
        {
            return $this->db->query("SELECT cl.id_client, cl.type_client, cl.contact_client, cl.nom_client, cl.prenom_client, cl.num_CNIB, cl.date_delivre, cl.lieu_delivre FROM client cl  
                WHERE cl.contact_client = '$num'
                AND cl.type_client <> 'autre'
                AND cl.type_client <> 'eleve'
                AND cl.type_client <> 'enfant'
                AND cl.type_client <> 'etudiant'
                ORDER BY cl.id_client DESC LIMIT 1")->row();
            
        }

        public function get()
        {
            return $this->db->query("SELECT * FROM client cl  
                WHERE cl.type_client = 'autrepersonnel'
                OR cl.type_client = 'client'
                AND cl.actifclient = 1")->result();
            
        }


        public function getclt($cl)
        {
            return $this->db->query("SELECT * FROM client cl  
            WHERE cl.type_client = '$cl'")->result();
            
        }

            
        
        public function getp()
        {
            $today = mdate("%Y-%m-%d %H:%i:%s", now('UTC'));

            return $this->db->query("SELECT * FROM client cl  
                WHERE cl.type_client = 'autrepersonnel'
                AND cl.actifclient = 1
                AND cl.nom_client != ''
                AND cl.prenom_client != ''
                AND cl.contact_client NOT IN (SELECT personnels.contact_perso FROM personnels)
                AND cl.contact_client NOT IN (SELECT personnels.contact2 FROM personnels)
                ORDER BY cl.nom_client ASC")->result();
            
        }
        public function getpartofact()
        {
            return $this->db->query("SELECT * FROM client cl
                LEFT JOIN partenaires p ON p.idclientpatern = cl.id_client
                WHERE cl.type_client <> 'personnel'
                AND cl.type_client <> 'autre'
                AND cl.type_client <> 'eleve'
                AND cl.type_client <> 'enfant'
                AND cl.type_client <> 'etudiant'
                AND cl.type_client <> 'Adulte'
                AND cl.type_client <> 'autrepersonnel'
                AND cl.type_client <> 'client'
                AND cl.type_client <> 'autres'
                AND cl.type_client <> ''
                AND cl.type_client <> 'membre'
                AND cl.type_client <> 'partenaire_specifique'
                ORDER BY cl.nom_client ASC")->result();
            
        }

        public function cl($c)
        {
            return $this->db->query("SELECT * FROM client cl
                WHERE cl.id_client = '$c'
                AND cl.actifclient = 1")->row();
        }
    }
    /** Client_model.php **/
    /** application/models/Client_model.php **/
