<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Gare_depart_model extends CI_Model
    {
        protected $table = 'gare_exp';
        
        public function __construct()
        {
            parent::__construct();
        }
        
        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
            
                
        public function update($code_gaexp, array $data)
        {
            return $this->db->where('code_gaexp', $code_gaexp)
            ->update($this->table, $data);
        }

        public function del($id)
        {
            return $this->db->where('code_gaexp', $id)->delete($this->table);
        }
    
    
        public function get($cid, $gd_id = FALSE)
        {
            if ($gd_id === FALSE) {
                return $this->db->query(
                    "SELECT * FROM gare_exp gd
                    JOIN gares g ON gd.garesid = g.idengare
                    JOIN ville v ON gd.id_villegd = v.id_ville
                    JOIN compagnies c ON gd.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'
                    ORDER BY c.nom_compagnie ASC, gd.nom_gaep ASC")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM gare_exp gd
                    JOIN gares g ON gd.garesid = g.idengare
                    JOIN ville v ON gd.id_villegd = v.id_ville
                    JOIN compagnies c ON gd.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'
                    AND gd.code_gaexp = '$gd_id'")->row();
        }

        /**
         * Regroupe les gares de départ d'une entreprise par compagnie.
         *
         * @param int|string $cid id_entreprise
         * @return array Liste de groupes [nom_compagnie, cle_compagnie, gares[]]
         */
        public function get_grouped_by_compagnie($cid)
        {
            $gares = $this->get($cid);
            $groups = array();
            if (empty($gares)) {
                return $groups;
            }
            foreach ($gares as $gare) {
                $key = (string) $gare->id_compagd;
                if (!isset($groups[$key])) {
                    $groups[$key] = array(
                        'cle_compagnie' => $gare->id_compagd,
                        'nom_compagnie' => $gare->nom_compagnie,
                        'gares' => array(),
                    );
                }
                $groups[$key]['gares'][] = $gare;
            }
            return $groups;
        }



        public function getgid($cid)
        {
            $gd_id = $this->session->agent->guser;

                return $this->db->query(
                    "SELECT * FROM gare_exp gd
                    JOIN gares g ON gd.garesid = g.idengare
                    JOIN ville v ON gd.id_villegd = v.id_ville
                    JOIN compagnies c ON gd.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'
                    AND gd.code_gaexp = '$gd_id'")->result();
        }

        public function getatr($cid, $r)
        {

            return $this->db->query(
                "SELECT * FROM gare_exp gd
                JOIN gares g ON gd.garesid = g.idengare
                JOIN user_login ul ON ul.guser = g.idengare
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN attributions_role ar ON ar.idgestcompte = ul.uid_login
                JOIN ville v ON gd.id_villegd = v.id_ville
                JOIN compagnies c ON gd.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.id_entreprise = '$cid'
                AND ar.userole = '$r'")->result();
        }

        public function cmpgetad($cid)
        {

                return $this->db->query(
                    "SELECT * FROM gare_exp gd
                    JOIN gares g ON gd.garesid = g.idengare
                    JOIN ville v ON gd.id_villegd = v.id_ville
                    JOIN compagnies c ON gd.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'")->result();
        }
        
        public function cmpget($cid, $gdid)
        {
                return $this->db->query(
                    "SELECT * FROM gare_exp gd
                    JOIN gares g ON gd.garesid = g.idengare
                    JOIN ville v ON gd.id_villegd = v.id_ville
                    JOIN compagnies c ON gd.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'
                    AND g.idengare = '$gdid'")->result();
        }

        public function getbis($cid, $gd_id = FALSE)
        {
            if ($gd_id === FALSE) {
                return $this->db->query(
                    "SELECT * FROM gare_exp gd
                    JOIN gares g ON gd.garesid = g.idengare
                    JOIN ville v ON gd.id_villegd = v.id_ville
                    JOIN sousgare s ON s.gareprinceid = gd.code_gaexp
                    JOIN compagnies c ON gd.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM gare_exp gd
                    JOIN gares g ON gd.garesid = g.idengare
                    JOIN ville v ON gd.id_villegd = v.id_ville
                    JOIN sousgare s ON s.gareprinceid = gd.code_gaexp
                    JOIN compagnies c ON gd.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'
                    AND gd.code_gaexp = '$gd_id'")->row();
        }
        


        public function getgidbis($cid, $gdid)
        {
                return $this->db->query(
                    "SELECT * FROM gare_exp gd
                    JOIN gares g ON gd.garesid = g.idengare
                    JOIN ville v ON gd.id_villegd = v.id_ville
                    JOIN sousgare s ON s.gareprinceid = gd.code_gaexp
                    JOIN compagnies c ON gd.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'
                    AND g.idengare = '$gdid'")->result();
        }

        public function getn($gd_id)
        {
            return $this->db->query(
                "SELECT * FROM gare_exp gd
                JOIN gares g ON gd.garesid = g.idengare
                JOIN ville v ON gd.id_villegd = v.id_ville
                JOIN compagnies c ON gd.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE gd.code_gaexp = '$gd_id'")->row();
        }

        public function getno($gd_id)
        {
            return $this->db->query(
                "SELECT g.garenom FROM gare_exp gd
                JOIN gares g ON gd.garesid = g.idengare
                JOIN ville v ON gd.id_villegd = v.id_ville
                JOIN compagnies c ON gd.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE gd.code_gaexp = '$gd_id'")->row();
        }

        public function getgidbisad($cid)
        {

            return $this->db->query(
                "SELECT * FROM gare_exp gd
                JOIN gares g ON gd.garesid = g.idengare
                JOIN ville v ON gd.id_villegd = v.id_ville
                JOIN sousgare s ON s.gareprinceid = gd.code_gaexp
                JOIN compagnies c ON gd.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.id_entreprise = '$cid'")->result();
        }
        
        public function getgar($cid, $idg, $idsg, $idl, $idh)
        {
            return $this->db->query("SELECT * FROM gare_exp gd
                JOIN gares g ON gd.garesid = g.idengare
                JOIN ville v ON gd.id_villegd = v.id_ville
                JOIN sousgare s ON s.gareprinceid = gd.code_gaexp
                JOIN positionlignegare pg ON pg.idsousgar = s.idsousgare
                JOIN lignes lg ON pg.idligne = lg.ident_ligne
                JOIN intervalletemp i ON pg.idposit = i.idinter 
                JOIN compagnies c ON gd.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.id_entreprise = '$cid'
                AND g.idengare = '$idg'
                AND s.idsousgare = '$idsg'
                AND lg.ident_ligne = '$idl'
                AND pg.lgheures = '$idh'")->row();
        }
        
        public function getgbiss($cid)
        {
            $gd_id = $this->session->agent->guser;

                return $this->db->query(
                    "SELECT * FROM gare_exp gd
                    JOIN gares g ON gd.garesid = g.idengare
                    JOIN sousgare s ON s.gareprinceid = gd.code_gaexp
                    JOIN ville v ON gd.id_villegd = v.id_ville
                    JOIN compagnies c ON gd.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'
                    AND gd.code_gaexp != '$gd_id'")->result();
        }
    }
    /** Gare_depart_model.php **/
    /** application/models/Gare_depart_model.php **/
