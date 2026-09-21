<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Sous_gare_model extends CI_Model
    {
        protected $table = 'sousgare';
        
        public function __construct()
        {
            parent::__construct();
        }
        
        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }

        /**
         * JOIN sous-gares du même lieu physique (tous codes commerciaux sur garesid).
         * Permet à une compagnie affectée de réutiliser les sous-gares existantes.
         *
         * @param string $gdAlias alias de gare_exp (ex. gd)
         * @param string $sAlias alias de sousgare (ex. s)
         * @return string
         */
        public function sql_join_sousgare_lieu($gdAlias = 'gd', $sAlias = 's')
        {
            $gd = preg_replace('/[^a-zA-Z0-9_]/', '', (string) $gdAlias);
            $s = preg_replace('/[^a-zA-Z0-9_]/', '', (string) $sAlias);
            if ($gd === '') {
                $gd = 'gd';
            }
            if ($s === '') {
                $s = 's';
            }
            return "JOIN sousgare {$s} ON {$s}.gareprinceid IN (
                SELECT ge_sg.code_gaexp FROM gare_exp ge_sg WHERE ge_sg.garesid = {$gd}.garesid
            )";
        }

        /**
         * Sous-requête : idsousgare du lieu physique (code commercial ou idengare).
         *
         * @param string $gareCode code_gaexp ou idengare
         * @return string SQL fragment (sans parenthèses externes)
         */
        public function sql_ids_sousgare_lieu($gareCode)
        {
            $g = $this->db->escape_str(trim((string) $gareCode));
            return "SELECT s.idsousgare FROM sousgare s
                JOIN gare_exp ge ON s.gareprinceid = ge.code_gaexp
                WHERE ge.garesid = (
                    SELECT COALESCE(
                        (SELECT garesid FROM gare_exp WHERE code_gaexp = '{$g}' LIMIT 1),
                        '{$g}'
                    )
                )";
        }
            
                
        public function update($idsousgare, array $data)
        {
            return $this->db->where('idsousgare', $idsousgare)
            ->update($this->table, $data);
        }

        public function del($id)
        {
            return $this->db->where('idsousgare', $id)->delete($this->table);
        }
    
        public function get($cid, $gid, $sgd_id = FALSE)
        {
            // Liste par lieu physique : une seule sous-gare par nom (évite doublons multi-cie).
            if ($sgd_id === FALSE) {
                return $this->db->query(
                    "SELECT s.*, gd.*, g.*, v.*, c.*, e.*
                    FROM sousgare s
                    JOIN gare_exp gd ON s.gareprinceid = gd.code_gaexp
                    JOIN gares g ON gd.garesid = g.idengare
                    JOIN ville v ON gd.id_villegd = v.id_ville
                    JOIN compagnies c ON gd.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'
                    AND gd.garesid = '$gid'
                    AND s.idsousgare = (
                        SELECT MIN(s2.idsousgare) FROM sousgare s2
                        JOIN gare_exp gd2 ON s2.gareprinceid = gd2.code_gaexp
                        WHERE gd2.garesid = gd.garesid
                          AND s2.nomsousgare = s.nomsousgare
                    )
                    ORDER BY s.nomsousgare ASC")->result();
            }
                return $this->db->query(
                    "SELECT * FROM sousgare s
                    JOIN gare_exp gd ON s.gareprinceid = gd.code_gaexp
                    JOIN gares g ON gd.garesid = g.idengare
                    JOIN ville v ON gd.id_villegd = v.id_ville
                    JOIN compagnies c ON gd.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'
                    AND gd.garesid = '$gid'
                    AND s.idsousgare = '$sgd_id'")->row();
        }

        public function getsous($cid, $gid)
        {
            $cidEsc = $this->db->escape_str($cid);
            $gid = trim((string) $gid);
            if ($gid === '') {
                return array();
            }
            // Numérique ou code : toujours résoudre via lieu physique, dédoublonné par nom.
            if (ctype_digit($gid)) {
                $whereLieu = 'gd.garesid = ' . (int) $gid;
            } else {
                $gEsc = $this->db->escape_str($gid);
                $whereLieu = "gd.garesid = COALESCE(
                    (SELECT garesid FROM gare_exp WHERE code_gaexp = '{$gEsc}' LIMIT 1),
                    '{$gEsc}'
                )";
            }
            return $this->db->query(
                "SELECT s.*, gd.*, g.*, v.*, c.*, e.*
                FROM sousgare s
                JOIN gare_exp gd ON s.gareprinceid = gd.code_gaexp
                JOIN gares g ON gd.garesid = g.idengare
                JOIN ville v ON gd.id_villegd = v.id_ville
                JOIN compagnies c ON gd.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '{$cidEsc}'
                AND {$whereLieu}
                AND s.idsousgare = (
                    SELECT MIN(s2.idsousgare) FROM sousgare s2
                    JOIN gare_exp gd2 ON s2.gareprinceid = gd2.code_gaexp
                    WHERE gd2.garesid = gd.garesid
                      AND s2.nomsousgare = s.nomsousgare
                )
                ORDER BY s.nomsousgare ASC"
            )->result();
        }

        public function gets($cid, $gid)
        {
            
                return $this->db->query(
                    "SELECT * FROM sousgare s
                    JOIN gare_exp gd ON s.gareprinceid = gd.code_gaexp
                    JOIN gares g ON gd.garesid = g.idengare 
                    JOIN ville v ON gd.id_villegd = v.id_ville
                    JOIN compagnies c ON gd.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND gd.garesid = '$gid'")->row();
            
        }

        public function getes($cid, $gid, $gsd)
        {
            
                return $this->db->query(
                    "SELECT * FROM sousgare s
                    JOIN gare_exp gd ON s.gareprinceid = gd.code_gaexp
                    JOIN gares g ON gd.garesid = g.idengare 
                    JOIN ville v ON gd.id_villegd = v.id_ville
                    JOIN compagnies c ON gd.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND gd.garesid = '$gid'
                    AND s.idsousgare = '$gsd'")->result();
            
        }
        
        public function sget($cid, $gid, $gsd)
        {
            
                return $this->db->query(
                    "SELECT * FROM sousgare s
                    JOIN gare_exp gd ON s.gareprinceid = gd.code_gaexp
                    JOIN gares g ON gd.garesid = g.idengare 
                    JOIN ville v ON gd.id_villegd = v.id_ville
                    JOIN compagnies c ON gd.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND g.idengare = '$gid'
                    AND s.idsousgare = '$gsd'")->row();
            
        }

        public function sgettr($cid, $lit)
        {
            
            return $this->db->query(
                "SELECT * FROM sousgare s
                    JOIN gare_exp gd ON s.gareprinceid = gd.code_gaexp
                    JOIN gares g ON gd.garesid = g.idengare 
                    JOIN ville v ON gd.id_villegd = v.id_ville
                    JOIN compagnies c ON gd.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND gd.garesid = '$lit'")->result();
        }
        
    }
    /** Sous_gare_model.php **/
    /** application/models/Sous_gare_model.php **/
