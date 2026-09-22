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

        /**
         * Affectation déjà présente : même gare physique + même compagnie.
         *
         * @param int|string $id_entreprise
         * @param int|string $cle_compagnie
         * @param string $garesid
         * @return object|null
         */
        public function find_affectation($id_entreprise, $cle_compagnie, $garesid)
        {
            $id_entreprise = (int) $id_entreprise;
            $cle_compagnie = (int) $cle_compagnie;
            $garesid = trim((string) $garesid);
            if ($id_entreprise <= 0 || $cle_compagnie <= 0 || $garesid === '') {
                return null;
            }
            return $this->db->query(
                "SELECT gd.code_gaexp, gd.nom_gaep
                 FROM gare_exp gd
                 JOIN compagnies c ON gd.id_compagd = c.cle_compagnie
                 WHERE c.id_entrep = ?
                   AND gd.id_compagd = ?
                   AND gd.garesid = ?
                 LIMIT 1",
                array($id_entreprise, $cle_compagnie, $garesid)
            )->row();
        }

        /**
         * @param string $code
         * @return bool
         */
        public function code_exists($code)
        {
            $code = trim((string) $code);
            if ($code === '') {
                return false;
            }
            return (bool) $this->db->query(
                "SELECT 1 FROM gare_exp WHERE code_gaexp = ? LIMIT 1",
                array($code)
            )->row();
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

        /**
         * Résout un code_gaexp ou idengare → lieu physique + tous les codes commerciaux du lieu.
         *
         * @param string $code_or_phys
         * @return array{phys:string,codes:string[]}
         */
        public function resolve_lieu($code_or_phys)
        {
            $raw = trim((string) $code_or_phys);
            $out = array('phys' => '', 'codes' => array());
            if ($raw === '' || $raw === '0') {
                return $out;
            }

            $byCode = $this->db->query(
                "SELECT code_gaexp, garesid FROM gare_exp WHERE code_gaexp = ? LIMIT 1",
                array($raw)
            )->row();
            if ($byCode) {
                $phys = trim((string) $byCode->garesid);
                if ($phys === '') {
                    $phys = trim((string) $byCode->code_gaexp);
                }
            } else {
                $byPhys = $this->db->query(
                    "SELECT idengare FROM gares WHERE idengare = ? LIMIT 1",
                    array($raw)
                )->row();
                $phys = $byPhys ? trim((string) $byPhys->idengare) : $raw;
            }
            $out['phys'] = $phys;

            $rows = $this->db->query(
                "SELECT DISTINCT code_gaexp AS c FROM gare_exp
                 WHERE garesid = ? OR code_gaexp = ? OR garesid = ? OR code_gaexp = ?",
                array($phys, $raw, $raw, $phys)
            )->result();
            $codes = array();
            foreach ($rows as $r) {
                $c = trim((string) $r->c);
                if ($c !== '') {
                    $codes[$c] = $c;
                }
            }
            $codes[$raw] = $raw;
            if ($phys !== '') {
                $codes[$phys] = $phys;
            }
            $out['codes'] = array_values($codes);
            return $out;
        }

        /**
         * Gares de départ pour le tri = lg.gaexp_lg des lignes dont la compagnie
         * d’arrivée (id_compaga) est celle choisie — pas id_compagd (sinon CBT
         * remonte aussi les lignes VIP/CIT/CMT partant de ses gares).
         *
         * @return array
         */
        public function list_for_tri_compagnie($ekey, $cle_compagnie)
        {
            $cle_compagnie = trim((string) $cle_compagnie);
            if ($cle_compagnie === '' || $cle_compagnie === '0') {
                return array();
            }

            // Source principale : gares de départ des lignes de la compagnie (arrivée).
            $rows = $this->db->query(
                "SELECT DISTINCT
                    ge.code_gaexp,
                    ge.nom_gaep,
                    ge.garesid,
                    ge.id_compagd
                FROM lignes lg
                JOIN gare_exp ge ON lg.gaexp_lg = ge.code_gaexp
                JOIN gare_dest ga ON lg.gadest_lg = ga.code_gadest
                JOIN compagnies c_arr ON ga.id_compaga = c_arr.cle_compagnie
                JOIN entreprise e ON c_arr.id_entrep = e.id_entreprise
                WHERE e.ekey = ?
                AND ga.id_compaga = ?
                AND IFNULL(lg.actif_lg, 1) = 1
                AND IFNULL(ga.nom_gadest, '') != 'OUAGAESCAL'
                ORDER BY ge.nom_gaep ASC, ge.code_gaexp ASC",
                array($ekey, $cle_compagnie)
            )->result();

            if (!empty($rows)) {
                return $rows;
            }

            // Repli : affectation commerciale seule (compagnie sans ligne encore).
            $fallback = $this->db->query(
                "SELECT DISTINCT gd.code_gaexp, gd.nom_gaep, gd.garesid, gd.id_compagd
                FROM gare_exp gd
                JOIN compagnies c ON gd.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                JOIN gares g ON gd.garesid = g.idengare
                WHERE e.ekey = ?
                AND gd.id_compagd = ?
                ORDER BY gd.nom_gaep ASC, gd.code_gaexp ASC",
                array($ekey, $cle_compagnie)
            )->result();

            return is_array($fallback) ? $fallback : array();
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
                    JOIN sousgare s ON s.gareprinceid IN (
                        SELECT ge_sg.code_gaexp FROM gare_exp ge_sg WHERE ge_sg.garesid = gd.garesid
                    )
                    JOIN compagnies c ON gd.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM gare_exp gd
                    JOIN gares g ON gd.garesid = g.idengare
                    JOIN ville v ON gd.id_villegd = v.id_ville
                    JOIN sousgare s ON s.gareprinceid IN (
                        SELECT ge_sg.code_gaexp FROM gare_exp ge_sg WHERE ge_sg.garesid = gd.garesid
                    )
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
                    JOIN sousgare s ON s.gareprinceid IN (
                        SELECT ge_sg.code_gaexp FROM gare_exp ge_sg WHERE ge_sg.garesid = gd.garesid
                    )
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
                JOIN sousgare s ON s.gareprinceid IN (
                    SELECT ge_sg.code_gaexp FROM gare_exp ge_sg WHERE ge_sg.garesid = gd.garesid
                )
                JOIN compagnies c ON gd.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.id_entreprise = '$cid'")->result();
        }
        
        /**
         * Position / décalage d'heure pour l'impression ticket.
         * 1) Match exact sous-gare + ligne + horaire
         * 2) Sinon repli « Maintenant » (ou 1ʳᵉ position) sur la gare de la ligne
         *    — cas fréquent 2ᵉ jambe correspondance : departclient_idgare reste celui de la 1ʳᵉ jambe
         * 3) Sinon objet synthétique Maintenant/0 pour que l'heure programme s'affiche quand même
         */
        public function getgar($cid, $idg, $idsg, $idl, $idh)
        {
            $cid = $this->db->escape_str($cid);
            $idg = $this->db->escape_str($idg);
            $idsg = $this->db->escape_str($idsg);
            $idl = $this->db->escape_str($idl);
            $idh = $this->db->escape_str($idh);

            $base = "SELECT gd.*, g.*, s.*, pg.*, i.*, lg.*, c.*, e.*
                FROM gare_exp gd
                JOIN gares g ON gd.garesid = g.idengare
                JOIN ville v ON gd.id_villegd = v.id_ville
                JOIN sousgare s ON s.gareprinceid IN (
                    SELECT ge_sg.code_gaexp FROM gare_exp ge_sg WHERE ge_sg.garesid = gd.garesid
                )
                JOIN positionlignegare pg ON pg.idsousgar = s.idsousgare
                JOIN lignes lg ON pg.idligne = lg.ident_ligne
                JOIN intervalletemp i ON pg.idposit = i.idinter
                JOIN compagnies c ON gd.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.id_entreprise = '{$cid}'
                AND g.idengare = '{$idg}'
                AND lg.ident_ligne = '{$idl}'
                AND pg.lgheures = '{$idh}'";

            $row = $this->db->query($base . " AND s.idsousgare = '{$idsg}' LIMIT 1")->row();
            if ($row) {
                return $row;
            }

            // Repli : même gare/ligne/horaire, prioriser position Maintenant
            $row = $this->db->query(
                $base . " ORDER BY CASE WHEN i.possitiongare = 'Maintenant' THEN 0 ELSE 1 END, s.idsousgare ASC LIMIT 1"
            )->row();
            if ($row) {
                return $row;
            }

            // Filet d'impression : ne jamais bloquer l'affichage de l'heure programme
            $fallback = new stdClass();
            $fallback->possitiongare = 'Maintenant';
            $fallback->minutetemps = 0;
            $fallback->nomsousgare = '';
            $fallback->quart = '';
            return $fallback;
        }
        
        public function getgbiss($cid)
        {
            $gd_id = $this->session->agent->guser;

                return $this->db->query(
                    "SELECT * FROM gare_exp gd
                    JOIN gares g ON gd.garesid = g.idengare
                    JOIN sousgare s ON s.gareprinceid IN (
                        SELECT ge_sg.code_gaexp FROM gare_exp ge_sg WHERE ge_sg.garesid = gd.garesid
                    )
                    JOIN ville v ON gd.id_villegd = v.id_ville
                    JOIN compagnies c ON gd.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'
                    AND gd.code_gaexp != '$gd_id'")->result();
        }
    }
    /** Gare_depart_model.php **/
    /** application/models/Gare_depart_model.php **/
