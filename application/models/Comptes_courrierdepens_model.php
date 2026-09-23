<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Comptes_courrierdepens_model extends CI_Model
    {
        protected $table = 'compte_courrierdepens';
        
        public function __construct()
        {
            parent::__construct();
        }
        
        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
            
                
        public function update($idcpcourriers, array $data)
        {
            return $this->db->where('idcpcourrierdepens', $idcpcourriers)
            ->update($this->table, $data);
        }

        public function del($id)
        {
        return $this->db->where('idcpcourrierdepens', $id)->delete($this->table);
        }

        public function getcomptedep($cd, $gid, $idsg, $ad)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
            if (!isset($this->m_compte_user)) {
                $this->load->model('Compte_user_model', 'm_compte_user');
            }
            $lieu = $this->m_compte_user->sql_ul_guser_lieu($gid, 'ul');
            $cd = $this->db->escape_str($cd);
            $ad = $this->db->escape_str($ad);
            $idsg = $this->db->escape_str($idsg);
                return $this->db->query(
                    "SELECT * FROM compte_courrierdepens ccd
                    JOIN attributions_role ar ON ccd.comptiduserdepens = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN compagnies c ON ccd.compcourdepens = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cd'
                    AND ar.roleattribut = '$ad'
                    AND ccd.validcompteisdepens = 0
                    {$lieu}
                    AND ccd.idsousgdepens = '$idsg'
                    AND ccd.comptedepensactif = 0")->result();
                
        }


        public function depsfiltrecour($key, $gd, $db, $df, $cp, $use = FALSE)
        {
            if (!isset($this->m_compte_user)) {
                $this->load->model('Compte_user_model', 'm_compte_user');
            }
            // $gd peut être code_gaexp (TRI) : résoudre lieu (phys + codes) via ul.guser.
            $lieu = $this->m_compte_user->sql_ul_guser_lieu($gd, 'ul');
            $key = $this->db->escape_str($key);
            $cp = $this->db->escape_str($cp);
            $db = $this->db->escape_str($db);
            $df = $this->db->escape_str($df);

            $useSql = '';
            $useTrim = is_string($use) || is_numeric($use) ? trim((string) $use) : '';
            if ($useTrim !== '' && $useTrim !== '0') {
                // Slash éventuel déjà retiré côté Rapport::_resolve_report_operateur.
                $slashPos = strpos($useTrim, '/');
                if ($slashPos !== false) {
                    $useTrim = trim(substr($useTrim, 0, $slashPos));
                }
                $useSql = " AND ar.roleattribut = '" . $this->db->escape_str($useTrim) . "' ";
            }

            return $this->db->query("SELECT * FROM compte_courrierdepens ccd
                JOIN attributions_role ar ON ccd.comptiduserdepens = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN compagnies c ON ccd.compcourdepens = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$key'
                {$lieu}
                AND ccd.compcourdepens = '$cp'
                AND ccd.comptdatearretdepens BETWEEN '$db' AND '$df'
                {$useSql}
                ORDER BY ccd.comptdatearretdepens ASC")->result();
        }
    }
    /** Comptes_courrierdepens_model.php **/
    /** application/models/Comptes_courrierdepens_model.php **/
