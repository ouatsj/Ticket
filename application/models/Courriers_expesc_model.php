<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Courriers_expesc_model extends CI_Model
    {
        protected $table = 'courriers_expesc';
        
        public function __construct()
        {
            parent::__construct();
        }
        
        
        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
            
        public function update($courrierexpid, $num_cour, $departcolis, array $data)
        {

        $multiClause = array('courrierexpidesc' => $courrierexpid, 'num_couresc' => $num_cour, 'departcolisesc' => $departcolis);

            return $this->db->where($multiClause)->update($this->table, $data);
        }

        public function del($id, $num_cou, $departcolis)
        {
            $multiClause = array('courrierexpidesc' => $id, 'num_couresc' => $num_cou, 'departcolisesc' => $departcolis);
            return $this->db->where($multiClause)->delete($this->table);
        }
        
        public function listereportcour($cid, $gid, $dt1, $dt2, $cp, $acl = FALSE, $algn = FALSE)
        {
            
            if ($acl === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, lg.nom_ligne, es.prixcolisesc FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND ul.guser = '$gid'
                    AND dest.id_compaga = '$cp'
                    AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND es.actif_couresc = 0
                    GROUP BY lg.nom_ligne, es.prixcolisesc")->result();
            }
            elseif($algn === '')
            {
                return $this->db->query("SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, lg.nom_ligne, es.prixcolisesc FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND ul.guser = '$gid'
                    AND dest.id_compaga = '$cp'
                    AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND es.actif_couresc = 0
                    AND ar.roleattribut = '$acl'
                    GROUP BY lg.nom_ligne, es.prixcolisesc")->result();
            }
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, lg.nom_ligne, es.prixcolisesc FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND ul.guser = '$gid'
                    AND dest.id_compaga = '$cp'
                    AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND es.actif_couresc = 0
                    AND ar.roleattribut = '$acl'
                    AND lg.ident_ligne = '$algn'
                    GROUP BY lg.nom_ligne, es.prixcolisesc")->result();
        }

        public function expetatspli($cid, $dt1, $dt2, $idconx, $gd = FALSE, $tycr = FALSE, $cp = FALSE, $al = FALSE)
        {        
            if($gd === '' AND $tycr === '' AND $cp === ''  AND $al === ''){
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, cd.naturecoli, es.prixcolisesc, es.dateenvoiesc FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND ar.roleattribut = '$idconx'
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    AND es.verifcouresc IN('A', 'C', 'D')
                    GROUP BY ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, cd.naturecoli, es.prixcolisesc, es.dateenvoiesc
                    ORDER BY es.dateenvoiesc ASC")->result();
            }
            elseif($tycr === '' AND $cp === '' AND $al === ''){
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, cd.naturecoli, es.prixcolisesc, es.dateenvoiesc FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND ar.roleattribut = '$idconx'
                    AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND es.verifcouresc IN('A', 'C', 'D')
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    AND ul.guser = '$gd'
                    GROUP BY ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, cd.naturecoli, es.prixcolisesc, es.dateenvoiesc
                    ORDER BY es.dateenvoiesc ASC")->result();
            }
            elseif ($cp === '' AND $al === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, cd.naturecoli, es.prixcolisesc, es.dateenvoiesc FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND ul.guser = '$gd'
                    AND cd.naturecoli = '$tycr'
                    AND ar.roleattribut = '$idconx'
                    AND es.verifcouresc IN('A', 'C', 'D')
                    GROUP BY ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, cd.naturecoli, es.prixcolisesc, es.dateenvoiesc
                    ORDER BY es.dateenvoiesc ASC")->result();
            }
            elseif($tycr === '' AND $al === ''){
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, cd.naturecoli, es.prixcolisesc, es.dateenvoiesc FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND ar.roleattribut = '$idconx'
                    AND ul.guser = '$gd'
                    AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND es.verifcouresc IN('A', 'C', 'D')
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    GROUP BY ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, cd.naturecoli, es.prixcolisesc, es.dateenvoiesc
                    ORDER BY es.dateenvoiesc ASC")->result();
            }
            elseif ($al === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, cd.naturecoli, es.prixcolisesc, es.dateenvoiesc FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND ul.guser = '$gd'
                    AND ar.roleattribut = '$idconx'
                    AND cd.naturecoli = '$tycr'
                    AND es.verifcouresc IN('A', 'C', 'D')
                    GROUP BY ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, cd.naturecoli, es.prixcolisesc, es.dateenvoiesc
                    ORDER BY es.dateenvoiesc ASC")->result();
            }
            
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, cd.naturecoli, es.prixcolisesc, es.dateenvoiesc FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND ul.guser = '$gd'
                    AND ar.roleattribut = '$idconx'
                    AND cd.naturecoli = '$tycr'
                    AND lg.ident_ligne = '$al'
                    AND es.verifcouresc IN('A', 'C', 'D')
                    GROUP BY ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, cd.naturecoli, es.prixcolisesc, es.dateenvoiesc
                    ORDER BY es.dateenvoiesc ASC")->result();
        }

        public function expverspli($cid, $dt1, $dt2, $gd, $idconx, $tycr = FALSE, $cp = FALSE)
        {        
            if($tycr === '' AND $cp === ''){
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, cd.naturecoli, es.prixcolisesc, es.dateenvoiesc FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND ar.roleattribut = '$idconx'
                    AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND es.verifcouresc IN('A', 'C', 'D')
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    AND ul.guser = '$gd'
                    GROUP BY ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, cd.naturecoli, es.prixcolisesc, es.dateenvoiesc
                    ORDER BY es.dateenvoiesc ASC")->result();
            }
            elseif ($cp === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, cd.naturecoli, es.prixcolisesc, es.dateenvoiesc FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND ul.guser = '$gd'
                    AND cd.naturecoli = '$tycr'
                    AND ar.roleattribut = '$idconx'
                    AND es.verifcouresc IN('A', 'C', 'D')
                    GROUP BY ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, cd.naturecoli, es.prixcolisesc, es.dateenvoiesc
                    ORDER BY es.dateenvoiesc ASC")->result();
            }
            elseif($tycr === ''){
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, cd.naturecoli, es.prixcolisesc, es.dateenvoiesc FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND ar.roleattribut = '$idconx'
                    AND ul.guser = '$gd'
                    AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND es.verifcouresc IN('A', 'C', 'D')
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    AND dest.id_compaga = '$cp'
                    GROUP BY ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, cd.naturecoli, es.prixcolisesc, es.dateenvoiesc
                    ORDER BY es.dateenvoiesc ASC")->result();
            }
            
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, cd.naturecoli, es.prixcolisesc, es.dateenvoiesc FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND ul.guser = '$gd'
                    AND ar.roleattribut = '$idconx'
                    AND cd.naturecoli = '$tycr'
                    AND es.verifcouresc IN('A', 'C', 'D')
                    GROUP BY ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, cd.naturecoli, es.prixcolisesc, es.dateenvoiesc
                    ORDER BY es.dateenvoiesc ASC")->result();
        }

        public function expetatspli1($cid, $cp, $dt1, $dt2, $gd = FALSE, $tycr = FALSE, $idconx = FALSE)
        {        
            if ($gd === '' AND $tycr === '' AND $idconx === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, cd.naturecoli, es.prixcolisesc FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND es.actif_couresc = 0
                    GROUP BY dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc")->result();
            }
            elseif ($tycr === '' AND $idconx === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, cd.naturecoli, es.prixcolisesc FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND es.actif_couresc = 0
                    AND ul.guser = '$gd'
                    GROUP BY dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc")->result();
            }
            elseif ($idconx === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, cd.naturecoli, es.prixcolisesc FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND es.actif_couresc = 0
                    AND ul.guser = '$gd'
                    AND cd.naturecoli = '$tycr'
                    AND ul.guser = '$gd'
                    GROUP BY dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc")->result();
            }
            elseif($algn === '')
            {
                return $this->db->query("SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, cd.naturecoli, es.prixcolisesc FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgaresec = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND es.actif_couresc = 0
                    AND ul.guser = '$gd'
                    AND cd.naturecoli = '$tycr'
                    AND ar.roleattribut = '$idconx'
                    GROUP BY dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc")->result();
            }
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, cd.naturecoli, es.prixcolisesc FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND es.actif_couresc = 0
                    AND ul.guser = '$gd'
                    AND ar.roleattribut = '$idconx'
                    AND lg.ident_ligne = '$algn'
                    AND cd.naturecoli = '$tycr'
                    GROUP BY dest.id_compaga,, lg.nom_ligne, cd.naturecoli, es.prixcolisesc")->result();
        }

        public function expetatspli2($cid, $cp, $dt1, $dt2, $gd = FALSE, $tycr = FALSE, $idconx = FALSE)
        {        
            if ($gd === '' AND $tycr === '' AND $idconx === '') {
                return $this->db->query(
                    "SELECT SUM(prixcolisesc) AS montantesc, es.dateenvoiesc, cd.naturecoli FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND dest.id_compaga = '$cp'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND es.actif_couresc = 0
                    AND es.verifcouresc IN('A', 'C', 'D')
                    GROUP BY es.dateenvoiesc, cd.naturecoli")->result();
            }
            elseif ($tycr === '' AND $idconx === '') {
                return $this->db->query(
                    "SELECT SUM(prixcolisesc) AS montantesc, es.dateenvoiesc, cd.naturecoli FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND dest.id_compaga = '$cp'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND es.actif_couresc = 0
                    AND es.verifcouresc IN('A', 'C', 'D')
                    AND ul.guser = '$gd'
                    GROUP BY es.dateenvoiesc, cd.naturecoli")->result();
            }
            elseif ($idconx === '') {
                return $this->db->query(
                    "SELECT SUM(prixcolisesc) AS montantesc, es.dateenvoiesc, cd.naturecoli FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND dest.id_compaga = '$cp'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND es.actif_couresc = 0
                    AND ul.guser = '$gd'
                    AND cd.naturecoli = '$tycr'
                    AND es.verifcouresc IN('A', 'C', 'D')
                    GROUP BY es.dateenvoiesc, cd.naturecoli")->result();
            }
            
                return $this->db->query(
                    "SELECT SUM(prixcolisesc) AS montantesc, es.dateenvoiesc, cd.naturecoli FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND dest.id_compaga = '$cp'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND es.actif_couresc = 0
                    AND ul.guser = '$gd'
                    AND cd.naturecoli = '$tycr'
                    AND ar.roleattribut = '$idconx'
                    AND es.verifcouresc IN('A', 'C', 'D')
                    GROUP BY es.dateenvoiesc, cd.naturecoli")->result();
        }

        //report global des expeditions
        public function expetatspligl($cid, $dt1, $dt2, $gd = FALSE, $idconx = FALSE, $tycr = FALSE, $cp = FALSE, $algn = FALSE)
        {        
            if ($gd === '' AND $idconx === '' AND $tycr === '' AND $cp === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, cd.naturecoli, lg.nom_ligne, es.prixcolisesc FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    GROUP BY cd.naturecoli, lg.nom_ligne, es.prixcolisesc")->result();
            }
            elseif ($idconx === '' AND $tycr === '' AND $cp === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, cd.naturecoli, lg.nom_ligne, es.prixcolisesc FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND ul.guser = '$gd'
                    GROUP BY cd.naturecoli, lg.nom_ligne, es.prixcolisesc")->result();
            }
            elseif ($tycr === '' AND $cp === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, cd.naturecoli, lg.nom_ligne, es.prixcolisesc FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND ul.guser = '$gd'
                    AND ar.roleattribut = '$idconx'
                    GROUP BY cd.naturecoli, lg.nom_ligne, es.prixcolisesc")->result();
            }
            elseif ($cp === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, cd.naturecoli, lg.nom_ligne, es.prixcolisesc FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND ul.guser = '$gd'
                    AND ar.roleattribut = '$idconx'
                    AND cd.naturecoli = '$tycr'
                    GROUP BY cd.naturecoli, lg.nom_ligne, es.prixcolisesc")->result();
            }
            elseif($tycr === '' AND $algn === '')
            {
                return $this->db->query("SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montant, cd.naturecoli, lg.nom_ligne, es.prixcolisesc FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND dest.id_compaga = '$cp'
                    AND ul.guser = '$gd'
                    AND ar.roleattribut = '$idconx'
                    GROUP BY cd.naturecoli, lg.nom_ligne, es.prixcolisesc")->result();
            }
            elseif($algn === '')
            {
                return $this->db->query("SELECT COUNT(courrierexpidesc) AS nombresrsc, SUM(prixcolisrsc) AS montant, cd.naturecoli, lg.nom_ligne, es.prixcolisesc FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND dest.id_compaga = '$cp'
                    AND ul.guser = '$gd'
                    AND cd.naturecoli = '$tycr'
                    AND ar.roleattribut = '$idconx'
                    GROUP BY cd.naturecoli, lg.nom_ligne, es.prixcolisesc")->result();
            }
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, cd.naturecoli, lg.nom_ligne, es.prixcolisesc FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND dest.id_compaga = '$cp'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND ul.guser = '$gd'
                    AND cd.naturecoli = '$tycr'
                    AND ar.roleattribut = '$idconx'
                    AND lg.ident_ligne = '$algn'
                    GROUP BY cd.naturecoli, lg.nom_ligne, es.prixcolisesc")->result();
        }

        public function texpetatspligl($cid, $dt1, $dt2, $gd = FALSE, $cp = FALSE, $idconx = FALSE, $tycr = FALSE, $algn = FALSE)
        {        
            if ($gd === '' AND $cp === '' AND $idconx === '' AND $tycr === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, cd.naturecoli, lg.nom_ligne, es.prixcolisesc FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    GROUP BY lg.nom_ligne, es.prixcolisesc")->result();
            }
            elseif ($cp === '' AND $idconx === '' AND $tycr === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, cd.naturecoli, lg.nom_ligne, es.prixcolisesc FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND ul.guser = '$gd'
                    GROUP BY lg.nom_ligne, es.prixcolisesc")->result();
            }
            elseif ($idconx === '' AND $tycr === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, cd.naturecoli, lg.nom_ligne, es.prixcolisesc FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND ul.guser = '$gd'
                    AND dest.id_compaga = '$cp'
                    GROUP BY lg.nom_ligne, es.prixcolisesc")->result();
            }
            elseif($tycr === '' AND $algn === '')
            {
                return $this->db->query("SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, cd.naturecoli, lg.nom_ligne, es.prixcolisesc FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND dest.id_compaga = '$cp'
                    AND ul.guser = '$gd'
                    AND ar.roleattribut = '$idconx'
                    GROUP BY lg.nom_ligne, es.prixcolisesc")->result();
            }
            elseif($algn === '')
            {
                return $this->db->query("SELECT COUNT(courrierexpidesc) AS nombresrsc, SUM(prixcolisrsc) AS montantesc, cd.naturecoli, lg.nom_ligne, es.prixcolisesc FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND dest.id_compaga = '$cp'
                    AND ul.guser = '$gd'
                    AND cd.naturecoli = '$tycr'
                    AND ar.roleattribut = '$idconx'
                    GROUP BY lg.nom_ligne, es.prixcolisesc")->result();
            }
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, cd.naturecoli, lg.nom_ligne, es.prixcolisesc FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND dest.id_compaga = '$cp'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND ul.guser = '$gd'
                    AND cd.naturecoli = '$tycr'
                    AND ar.roleattribut = '$idconx'
                    AND lg.ident_ligne = '$algn'
                    GROUP BY lg.nom_ligne, es.prixcolisesc")->result();
        }

        //global plis
        public function expetatspliglob($cid, $dt1, $dt2, $gd = FALSE, $cp = FALSE, $tycr = FALSE, $algn = FALSE)
        {        
            if ($gd === '' AND $cp === '' AND $tycr === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT * FROM courriers_expesc es
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN expediteurs ex ON er.expditid = ex.id_expedit
                    JOIN client cl ON ex.clientexpedit = cl.id_client
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL")->result();
            }
            elseif ($cp === '' AND $tycr === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT * FROM courriers_expesc es
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN expediteurs ex ON er.expditid = ex.id_expedit
                    JOIN client cl ON ex.clientexpedit = cl.id_client
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND gex.code_gaexp = '$gd'")->result();
            }
            
            elseif($tycr === '' AND $algn === '')
            {
                return $this->db->query("SELECT * FROM courriers_expesc es
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN expediteurs ex ON er.expditid = ex.id_expedit
                    JOIN client cl ON ex.clientexpedit = cl.id_client 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND gex.code_gaexp = '$gd'")->result();
            }
            elseif($algn === '')
            {
                return $this->db->query("SELECT * FROM courriers_expesc es
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN expediteurs ex ON er.expditid = ex.id_expedit
                    JOIN client cl ON ex.clientexpedit = cl.id_client 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND gex.code_gaexp = '$gd'
                    AND cd.naturecoli = '$tycr'")->result();
            }
                return $this->db->query(
                    "SELECT * FROM courriers_expesc es
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN expediteurs ex ON er.expditid = ex.id_expedit
                    JOIN client cl ON ex.clientexpedit = cl.id_client
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND gex.code_gaexp = '$gd'
                    AND lg.ident_ligne = '$algn'
                    AND cd.naturecoli = '$tycr'")->result();
        }

        //moitie plis

        public function expetatspliexo($cid, $dt1, $dt2, $cp = FALSE, $gd = FALSE, $tycr = FALSE, $algn = FALSE)
        {        
            if ($cp === '' AND $gd === '' AND $tycr === '' AND $algn === NULL) {
                return $this->db->query(
                    "SELECT * FROM courriers_expesc es
                    JOIN sousgare sg ON eesc.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN expediteurs ex ON er.expditid = ex.id_expedit
                    JOIN client cl ON ex.clientexpedit = cl.id_client 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    AND es.verifcouresc IN('A', 'C', 'D')")->result();
            }
            elseif ($gd === '' AND $tycr === '' AND $algn === NULL) {
                return $this->db->query(
                    "SELECT * FROM courriers_expesc es
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN expediteurs ex ON er.expditid = ex.id_expedit
                    JOIN client cl ON ex.clientexpedit = cl.id_client
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND dest.id_compaga = '$cp'
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    AND es.verifcouresc IN('A', 'C', 'D')")->result();
            }
            elseif ($tycr === '' AND $algn === NULL) {
            
                return $this->db->query("SELECT * FROM courriers_expesc es
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN expediteurs ex ON er.expditid = ex.id_expedit
                    JOIN client cl ON ex.clientexpedit = cl.id_client
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND gex.code_gaexp = '$gd'
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    AND es.verifcouresc IN('A', 'C', 'D')")->result();
            }
            elseif($algn === NULL) {
                return $this->db->query(
                    "SELECT * FROM courriers_expesc es
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN expediteurs ex ON er.expditid = ex.id_expedit
                    JOIN client cl ON ex.clientexpedit = cl.id_client
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND gex.code_gaexp = '$gd'
                    AND cd.naturecoli = '$tycr'
                    AND es.verifcouresc IN('A', 'C', 'D')")->result();

            }
                return $this->db->query(
                    "SELECT * FROM courriers_expesc es
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN expediteurs ex ON er.expditid = ex.id_expedit
                    JOIN client cl ON ex.clientexpedit = cl.id_client
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND gex.code_gaexp = '$gd'
                    AND lg.ident_ligne = '$algn'
                    AND cd.naturecoli = '$tycr'
                    AND es.verifcouresc IN('A', 'C', 'D')")->result();
        }

        //recapt

        public function recaptexopli($cid, $dt1, $dt2, $gd = FALSE, $cp = FALSE, $tycr = FALSE, $algn = FALSE)
        {        
            if ($gd === '' AND $cp === '' AND $tycr === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc FROM courriers_expesc es
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    AND es.verifcouresc IN('A', 'C', 'D')
                    GROUP BY dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc")->result();
            }
            elseif ($cp === '' AND $tycr === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc FROM courriers_expesc es
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND gex.code_gaexp = '$gd'
                    AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    AND es.verifcouresc IN('A', 'C', 'D')
                    GROUP BY dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc")->result();
            }
            
            elseif ($tycr === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc FROM courriers_expesc es
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    AND es.verifcouresc IN('A', 'C', 'D')
                    AND gex.code_gaexp = '$gd'
                    GROUP BY dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc")->result();
            }
            
            elseif($algn === '')
            {
                return $this->db->query("SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc FROM courriers_expesc es
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND gex.code_gaexp = '$gd'
                    AND cd.naturecoli = '$tycr'
                    AND es.verifcouresc IN('A', 'C', 'D')
                    GROUP BY dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc")->result();
            }
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc FROM courriers_expesc es
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND gex.code_gaexp = '$gd'
                    AND lg.ident_ligne = '$algn'
                    AND cd.naturecoli = '$tycr'
                    AND es.verifcouresc IN('A', 'C', 'D')
                    GROUP BY dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc")->result();
        }

        
        public function expetatsplis($cid, $dt1, $dt2, $gd = FALSE, $cp = FALSE, $idconx = FALSE, $tycr = FALSE, $al = FALSE)
        {        
            if($gd === '' AND $cp === '' AND $idconx === '' AND $tycr === '' AND $al === ''){
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombres, SUM(prixcolisesc) AS montantesc, ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, cd.naturecoli, e.prixcolisesc, e.dateenvoiesc FROM courriers_exp e
                    JOIN attributions_role ar ON e.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON e.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND e.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND e.prixcolisesc IS NOT NULL
                    AND e.partocouresc IS NULL
                    AND e.verifcouresc IN('A', 'C', 'D')
                    GROUP BY ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, e.prixcolisesc
                    ORDER BY e.dateenvoiesc")->result();
            }
            elseif($cp === '' AND $idconx === '' AND $tycr === '' AND $al === ''){
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombres, SUM(prixcolisesc) AS montantesc, ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, cd.naturecoli, e.prixcolisesc, e.dateenvoiesc FROM courriers_exp e
                    JOIN attributions_role ar ON e.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON e.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND e.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND e.prixcolisesc IS NOT NULL
                    AND e.partocouresc IS NULL
                    AND ul.guser = '$gd'
                    AND e.verifcouresc IN('A', 'C', 'D')
                    GROUP BY ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, e.prixcolisesc
                    ORDER BY e.dateenvoiesc")->result();
            }
            elseif ($idconx === '' AND $tycr === '' AND $al === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombres, SUM(prixcolisesc) AS montantesc, ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, cd.naturecoli, e.prixcolisesc, e.dateenvoiesc FROM courriers_exp e
                    JOIN attributions_role ar ON e.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON e.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND e.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND e.prixcolisesc IS NOT NULL
                    AND e.partocouresc IS NULL
                    AND ul.guser = '$gd'
                    AND e.verifcouresc IN('A', 'C', 'D')
                    GROUP BY ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, e.prixcolisesc
                    ORDER BY e.dateenvoiesc")->result();
            }
            elseif($tycr === '' AND $al === ''){
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombres, SUM(prixcolisesc) AS montantesc, ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, cd.naturecoli, e.prixcolisesc, e.dateenvoiesc FROM courriers_exp e
                    JOIN attributions_role ar ON e.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON e.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND e.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND e.prixcolisesc IS NOT NULL
                    AND e.partocouresc IS NULL
                    AND ul.guser = '$gd'
                    AND ar.roleattribut = '$idconx'
                    AND e.verifcouresc IN('A', 'C', 'D')
                    GROUP BY ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, e.prixcolisesc
                    ORDER BY e.dateenvoiesc")->result();
            }
            elseif ($al === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombres, SUM(prixcolisesc) AS montantesc, ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, cd.naturecoli, e.prixcolisesc, e.dateenvoiesc FROM courriers_exp e
                    JOIN attributions_role ar ON e.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON e.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND e.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND e.prixcolisesc IS NOT NULL
                    AND e.partocouresc IS NULL
                    AND ul.guser = '$gd'
                    AND ar.roleattribut = '$idconx'
                    AND cd.naturecoli = '$tycr'
                    AND e.verifcouresc IN('A', 'C', 'D')
                    GROUP BY ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, e.prixcolisesc
                    ORDER BY e.dateenvoiesc")->result();
            }
            
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombres, SUM(prixcolisesc) AS montantesc, ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, cd.naturecoli, e.prixcolisesc, e.dateenvoiesc FROM courriers_exp e
                    JOIN attributions_role ar ON e.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON e.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND e.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND e.prixcolisesc IS NOT NULL
                    AND e.partocouresc IS NULL
                    AND ul.guser = '$gd'
                    AND ar.roleattribut = '$idconx'
                    AND cd.naturecoli = '$tycr'
                    AND lg.ident_ligne = '$al'
                    AND e.verifcouresc IN('A', 'C', 'D')
                    GROUP BY ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, e.prixcolisesc
                    ORDER BY e.dateenvoiesc ASC")->result();
        }

        public function recaptexopligr($cid, $dt1, $dt2, $gd = FALSE, $cp = FALSE, $tycr = FALSE, $algn = FALSE)
        {        
            if ($gd === '' AND $cp === '' AND $tycr === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT * FROM courriers_expesc e
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND e.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND e.prixcolisesc IS NOT NULL
                    AND e.partocouresc IS NULL
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    AND e.verifcouresc IN('A', 'C', 'D')")->result();
            }
            elseif ($cp === '' AND $tycr === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT * FROM courriers_expesc e
                    JOIN sousgare sg ON e.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND gex.code_gaexp = '$gd'
                    AND e.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND e.prixcolisesc IS NOT NULL
                    AND e.partocouresc IS NULL
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    AND e.verifcouresc IN('A', 'C', 'D')")->result();
            }
            elseif ($cp === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT * FROM courriers_expesc e
                    JOIN sousgare sg ON e.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND gex.code_gaexp = '$gd'
                    AND e.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND e.prixcolisesc IS NOT NULL
                    AND e.partocouresc IS NULL
                    AND cd.naturecoli = '$tycr'
                    AND e.verifcouresc IN('A', 'C', 'D')")->result();
            }
            elseif ($tycr === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT * FROM courriers_expesc e
                    JOIN sousgare sg ON e.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND e.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND e.prixcolisesc IS NOT NULL
                    AND e.partocouresc IS NULL
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    AND e.verifcouresc IN('A', 'C', 'D')
                    AND gex.code_gaexp = '$gd'")->result();
            }
            
            elseif($algn === '')
            {
                return $this->db->query("SELECT * FROM courriers_expesc e
                    JOIN sousgare sg ON e.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND e.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND e.prixcolisesc IS NOT NULL
                    AND e.partocouresc IS NULL
                    AND gex.code_gaexp = '$gd'
                    AND cd.naturecoli = '$tycr'
                    AND e.verifcouresc IN('A', 'C', 'D')")->result();
            }
                return $this->db->query(
                    "SELECT * FROM courriers_expesc e
                    JOIN sousgare sg ON e.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND e.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND e.prixcolisesc IS NOT NULL
                    AND e.partocouresc IS NULL
                    AND gex.code_gaexp = '$gd'
                    AND lg.ident_ligne = '$algn'
                    AND cd.naturecoli = '$tycr'
                    AND e.verifcouresc IN('A', 'C', 'D')")->result();
        }

        public function recaptexoplid($cid, $dt1, $dt2, $gd = FALSE, $cp = FALSE, $tycr = FALSE, $algn = FALSE)
        {        
            if ($gd === '' AND $cp === '' AND $tycr === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, dest.id_compaga, lg.nom_ligne, cd.naturecoli, e.prixcolisesc FROM courriers_expesc e
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND e.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND e.prixcolisesc IS NOT NULL
                    AND e.partocouresc IS NULL
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    AND e.exocresc = 1
                    GROUP BY dest.id_compaga, lg.nom_ligne, cd.naturecoli, e.prixcolisesc")->result();
            }
            elseif ($cp === '' AND $tycr === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, dest.id_compaga, lg.nom_ligne, cd.naturecoliesc, e.prixcolisesc FROM courriers_expesc e
                    JOIN sousgare sg ON e.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND gex.code_gaexp = '$gd'
                    AND e.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND e.prixcolisesc IS NOT NULL
                    AND e.partocouresc IS NULL
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    AND e.exocresc = 1
                    GROUP BY dest.id_compaga, lg.nom_ligne, cd.naturecoli, e.prixcolisesc")->result();
            }
            elseif ($cp === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, dest.id_compaga, lg.nom_ligne, cd.naturecoli, e.prixcolisesc FROM courriers_expesc e
                    JOIN sousgare sg ON e.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND gex.code_gaexp = '$gd'
                    AND e.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND e.prixcolisesc IS NOT NULL
                    AND e.partocouresc IS NULL
                    AND cd.naturecoli = '$tycr'
                    AND e.exocresc = 1
                    GROUP BY dest.id_compaga, lg.nom_ligne, cd.naturecoli, e.prixcolisesc")->result();
            }
            elseif ($tycr === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, dest.id_compaga, lg.nom_ligne, cd.naturecoli, e.prixcolisesc FROM courriers_expesc e
                    JOIN sousgare sg ON e.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND e.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND e.prixcolisesc IS NOT NULL
                    AND e.partocouresc IS NULL
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    AND e.exocresc = 1
                    AND gex.code_gaexp = '$gd'
                    GROUP BY dest.id_compaga, lg.nom_ligne, cd.naturecoli, e.prixcolisesc")->result();
            }
            
            elseif($algn === '')
            {
                return $this->db->query("SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, dest.id_compaga, lg.nom_ligne, cd.naturecoli, e.prixcolisesc FROM courriers_expesc e
                    JOIN sousgare sg ON e.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND e.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND e.prixcolisesc IS NOT NULL
                    AND e.partocouresc IS NULL
                    AND gex.code_gaexp = '$gd'
                    AND cd.naturecoli = '$tycr'
                    AND e.exocresc = 1
                    GROUP BY dest.id_compaga, lg.nom_ligne, cd.naturecoli, e.prixcolisesc")->result();
            }
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, dest.id_compaga, lg.nom_ligne, cd.naturecoli, e.prixcolisesc FROM courriers_expesc e
                    JOIN sousgare sg ON e.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND e.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND e.prixcolisesc IS NOT NULL
                    AND e.partocouresc IS NULL
                    AND gex.code_gaexp = '$gd'
                    AND lg.ident_ligne = '$algn'
                    AND cd.naturecoli = '$tycr'
                    AND e.exocresc = 1
                    GROUP BY dest.id_compaga, lg.nom_ligne, cd.naturecoli, e.prixcolisesc")->result();
        }

        public function recaptexoplijr($cid, $cp, $dt1, $dt2, $gd = FALSE, $tycr = FALSE, $algn = FALSE)
        {        
            if ($gd === '' AND $tycr === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc, h.heure FROM courriers_expesc es
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND es.verifcouresc IN('A', 'C', 'D')
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    GROUP BY dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc, h.id_heure
                    ORDER BY heure ASC")->result();
            }
            elseif ($tycr === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc, h.heure FROM courriers_expesc es
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND es.verifcouresc IN('A', 'C', 'D')
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    AND gex.code_gaexp = '$gd'
                    GROUP BY dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc, h.id_heure
                    ORDER BY heure ASC")->result();
            }
            
            elseif($algn === '')
            {
                return $this->db->query("SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc, h.heure FROM courriers_expesc es
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND gex.code_gaexp = '$gd'
                    AND cd.naturecoli = '$tycr'
                    AND es.verifcouresc IN('A', 'C', 'D')
                    GROUP BY dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc, h.id_heure
                    ORDER BY heure ASC")->result();
            }
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc, h.heure FROM courriers_expesc es
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND gex.code_gaexp = '$gd'
                    AND lg.ident_ligne = '$algn'
                    AND cd.naturecoli = '$tycr'
                    AND es.verifcouresc IN('A', 'C', 'D')
                    GROUP BY dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc, h.id_heure
                    ORDER BY heure ASC")->result();
        }

        
       public function recaptexopliheb($cid, $dt1, $dt2, $gd = FALSE, $cp = FALSE, $tycr = FALSE, $algn = FALSE)
        {        
            if ($gd === '' AND $cp === '' AND $tycr === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, lg.nom_ligne, e.prixcolisesc, h.heure, e.dateenvoiesc FROM courriers_expesc e
                    JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND e.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND e.prixcolisesc IS NOT NULL
                    AND e.partocouresc IS NULL
                    AND e.verifcouresc IN('A', 'C', 'D')
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    GROUP BY lg.nom_ligne, e.prixcolisesc, h.id_heure, e.dateenvoiesc
                    ORDER BY e.dateenvoiesc, h.id_heure")->result();
            }
            elseif ($cp === '' AND $tycr === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, lg.nom_ligne, e.prixcolisesc, h.heure, e.dateenvoiesc FROM courriers_expesc e
                    JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND gex.code_gaexp = '$gd'
                    AND e.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND e.prixcolisesc IS NOT NULL
                    AND e.partocouresc IS NULL
                    AND e.verifcouresc IN('A', 'C', 'D')
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    GROUP BY lg.nom_ligne, e.prixcolisesc, h.id_heure, e.dateenvoiesc
                    ORDER BY e.dateenvoiesc, h.id_heure")->result();
            }
            elseif($tycr === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, lg.nom_ligne, e.prixcolisesc, h.heure, e.dateenvoiesc FROM courriers_expesc e
                    JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND e.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND e.prixcolisesc IS NOT NULL
                    AND e.partocouresc IS NULL
                    AND e.verifcouresc IN('A', 'C', 'D')
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    AND gex.code_gaexp = '$gd'
                    GROUP BY lg.nom_ligne, e.prixcolisesc, h.id_heure, e.dateenvoiesc
                    ORDER BY e.dateenvoiesc, h.id_heure")->result();
            }
            
            elseif($algn === '')
            {
                return $this->db->query("SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, lg.nom_ligne, e.prixcolisesc, h.heure, e.dateenvoiesc FROM courriers_expesc e
                    JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND e.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND e.prixcolisesc IS NOT NULL
                    AND e.partocouresc IS NULL
                    AND gex.code_gaexp = '$gd'
                    AND cd.naturecoli = '$tycr'
                    AND e.verifcouresc IN('A', 'C', 'D')
                    GROUP BY lg.nom_ligne, e.prixcolisesc, h.id_heure, e.dateenvoiesc
                    ORDER BY e.dateenvoiesc, h.id_heure")->result();
            }
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, lg.nom_ligne, e.prixcolisesc, h.heure, e.dateenvoiesc FROM courriers_expesc e
                    JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND e.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND e.prixcolisesc IS NOT NULL
                    AND e.partocouresc IS NULL
                    AND gex.code_gaexp = '$gd'
                    AND lg.ident_ligne = '$algn'
                    AND cd.naturecoli = '$tycr'
                    AND e.verifcouresc IN('A', 'C', 'D')
                    GROUP BY lg.nom_ligne, e.prixcolisesc, h.id_heure, e.dateenvoiesc
                    ORDER BY e.dateenvoiesc, h.id_heure ASC")->result();
        }

        public function recaptpli($cid, $cp, $dt1, $dt2, $gd = FALSE, $tycr = FALSE, $algn = FALSE)
        {        
            if ($gd === '' AND $tycr === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc FROM courriers_expesc es
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    GROUP BY dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc")->result();
            }
            elseif ($tycr === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc FROM courriers_expesc es
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    AND gex.code_gaexp = '$gd'
                    GROUP BY dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc")->result();
            }
            
            elseif($algn === '')
            {
                return $this->db->query("SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc FROM courriers_expesc es
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND gex.code_gaexp = '$gd'
                    AND cd.naturecoli = '$tycr'
                    GROUP BY dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc")->result();
            }
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc FROM courriers_expesc es
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND gex.code_gaexp = '$gd'
                    AND lg.ident_ligne = '$algn'
                    AND cd.naturecoli = '$tycr'
                    GROUP BY dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc")->result();
        }

        public function recaptpligl($cid, $dt1, $dt2, $gd = FALSE, $tycr = FALSE, $cp = FALSE, $algn = FALSE)
        {        
            if ($gd === '' AND $tycr === '' AND $cp === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc FROM courriers_expesc es
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    GROUP BY dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc")->result();
            }
            elseif($tycr === '' AND $cp === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc FROM courriers_exp es
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND gex.code_gaexp = '$gd'
                    AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    GROUP BY dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc")->result();
            }
            elseif ($cp === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc FROM courriers_expesc es
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND gex.code_gaexp = '$gd'
                    AND cd.naturecoli = '$tycr'
                    GROUP BY dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc")->result();
            }
            
            elseif($algn === '')
            {
                return $this->db->query("SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc FROM courriers_expesc es
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND gex.code_gaexp = '$gd'
                    AND cd.naturecoli = '$tycr'
                    GROUP BY dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc")->result();
            }
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc FROM courriers_expesc es
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND gex.code_gaexp = '$gd'
                    AND lg.ident_ligne = '$algn'
                    AND cd.naturecoli = '$tycr'
                    GROUP BY dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc")->result();
        }

        public function trecaptpligl($cid, $dt1, $dt2, $cp = FALSE, $gd = FALSE, $tycr = FALSE, $algn = FALSE)
        {        
            if ($cp === '' AND $gd === '' AND $tycr === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    GROUP BY dest.id_compaga, lg.nom_ligne, es.prixcolisesc")->result();
            }
            elseif($gd === '' AND $tycr === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND dest.id_compaga = '$cp'
                    GROUP BY dest.id_compaga, lg.nom_ligne, es.prixcolisesc")->result();
            }
            elseif ($tycr === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND ul.guser = '$gd'
                    AND dest.id_compaga = '$cp'
                    GROUP BY dest.id_compaga, lg.nom_ligne, es.prixcolisesc")->result();
            }
            elseif($algn === '')
            {
                return $this->db->query("SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND dest.id_compaga = '$cp'
                    AND ul.guser = '$gd'
                    AND cd.naturecoliesc = '$tycr'
                    GROUP BY dest.id_compaga, lg.nom_ligne, es.prixcolisesc")->result();
            }
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND dest.id_compaga = '$cp'
                    AND ul.guser = '$gd'
                    AND lg.ident_ligne = '$algn'
                    AND cd.naturecoliesc = '$tycr'
                    GROUP BY dest.id_compaga, lg.nom_ligne, es.prixcolisesc")->result();
        }
        //factures
        public function facts($cid, $dt1, $dt2, $tcl, $gd, $nat = FALSE)
        {   
            if($nat === ''){
                return $this->db->query(
                "SELECT COUNT(courrierexpidesc) AS nbrsesc, SUM(nombrecolisesc) AS nbcolesc, cd.naturecoli FROM courriers_expesc es
                JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN gares g ON ul.guser = g.idengare
                JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN expediteurs ex ON er.expditid = ex.id_expedit
                JOIN recepteurs re ON er.receptid = re.idrecepetion
                JOIN client cl ON ex.clientexpedit = cl.id_client
                JOIN contrat_client ctcl ON ctcl.idtype_client = cl.id_client
                JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                AND ctcl.idtype_client = '$tcl'
                AND lg.gaexp_lg = '$gd'
                AND cd.clorecodecour = 1
                AND es.courcloreesc = 1
                AND es.partocouresc IS NOT NULL
                AND es.rexepedieresc IS NULL
                GROUP BY cd.naturecoli")->result();
            }     
            elseif($nat === 'Petit_plis' OR $nat === 'Gros_plis')
            {
              return $this->db->query(
                "SELECT COUNT(courrierexpidesc) AS nbrsesc, SUM(nombrecolisesc) AS nbcolesc FROM courriers_expesc es
                JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN gares g ON ul.guser = g.idengare
                JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN expediteurs ex ON er.expditid = ex.id_expedit
                JOIN recepteurs re ON er.receptid = re.idrecepetion
                JOIN client cl ON ex.clientexpedit = cl.id_client
                JOIN contrat_client ctcl ON ctcl.idtype_client = cl.id_client
                JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                AND ctcl.idtype_client = '$tcl'
                AND lg.gaexp_lg = '$gd'
                AND cd.clorecodecour = 1
                AND cd.naturecoli IN ('Petit_plis', 'Moyen_plis', 'Gros_plis')
                AND es.courcloreesc = 1
                AND es.partocouresc IS NOT NULL
                AND es.rexepedieresc IS NULL
                GROUP BY cd.naturecoli")->result();
            }
 
            elseif($nat === 'Petit_colis' OR $nat === 'Gros_colis' OR $nat === 'Moyen_colis')
            {
              return $this->db->query(
                "SELECT COUNT(courrierexpidesc) AS nbrsesc, SUM(nombrecolisesc) AS nbcolesc FROM courriers_expesc es
                JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN gares g ON ul.guser = g.idengare
                JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN expediteurs ex ON er.expditid = ex.id_expedit
                JOIN recepteurs re ON er.receptid = re.idrecepetion
                JOIN client cl ON ex.clientexpedit = cl.id_client
                JOIN contrat_client ctcl ON ctcl.idtype_client = cl.id_client
                JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                AND ctcl.idtype_client = '$tcl'
                AND lg.gaexp_lg = '$gd'
                AND cd.clorecodecour = 1
                AND cd.naturecoli IN ('Petit_colis', 'Moyen_colis', 'Gros_colis')
                AND es.courcloreesc = 1
                AND es.partocouresc IS NOT NULL
                AND es.rexepedieresc IS NULL
                GROUP BY cd.naturecoli")->result();
            }
              return $this->db->query(
                "SELECT COUNT(courrierexpidesc) AS nbrsesc, SUM(nombrecolisesc) AS nbcolesc FROM courriers_expesc es
                JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN gares g ON ul.guser = g.idengare
                JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN expediteurs ex ON er.expditid = ex.id_expedit
                JOIN recepteurs re ON er.receptid = re.idrecepetion
                JOIN client cl ON ex.clientexpedit = cl.id_client
                JOIN contrat_client ctcl ON ctcl.idtype_client = cl.id_client
                JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                AND ctcl.idtype_client = '$tcl'
                AND lg.gaexp_lg = '$gd'
                AND cd.naturecoli = '$nat'
                AND cd.clorecodecour = 1
                AND es.courcloreesc = 1
                AND es.partocouresc IS NOT NULL
                AND es.rexepedieresc IS NULL
                GROUP BY cd.naturecoli")->result();
        
        }

        public function factcolis($cid, $dt1, $dt2, $tcl, $gd, $nt)
        {   
            if($nt === 'Petit_colis' OR $nt === 'Moyen_colis' OR $nt === 'Gros_colis'){
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nbrsesc, SUM(nombrecolisesc) AS nbcolesc, es.dateenvoiesc, es.naturecourrieresc FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN expediteurs ex ON er.expditid = ex.id_expedit
                    JOIN recepteurs re ON er.receptid = re.idrecepetion
                    JOIN client cl ON ex.clientexpedit = cl.id_client
                    JOIN contrat_client ctcl ON ctcl.idtype_client = cl.id_client
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND ctcl.idtype_client = '$tcl'
                    AND lg.gaexp_lg = '$gd'
                    AND cd.clorecodecour = 1
                    AND cd.naturecoli IN ('Petit_colis', 'Moyen_colis', 'Gros_colis')
                    AND es.courcloreesc = 1
                    AND es.partocouresc IS NOT NULL
                    AND es.rexepedieresc IS NULL
                    GROUP BY es.dateenvoiesc, es.naturecourrieresc")->result();
            }
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nbrsesc, SUM(nombrecolisesc) AS nbcolesc, es.dateenvoiesc, es.naturecourrieresc FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN expediteurs ex ON er.expditid = ex.id_expedit
                    JOIN recepteurs re ON er.receptid = re.idrecepetion
                    JOIN client cl ON ex.clientexpedit = cl.id_client
                    JOIN contrat_client ctcl ON ctcl.idtype_client = cl.id_client
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                    AND ctcl.idtype_client = '$tcl'
                    AND cd.naturecoli = '$nt'
                    AND lg.gaexp_lg = '$gd'
                    AND cd.clorecodecour = 1
                    AND es.courcloreesc = 1
                    AND es.partocouresc IS NOT NULL
                    AND es.rexepedieresc IS NULL
                    GROUP BY es.dateenvoiesc, es.naturecourrieresc")->result();
        
        }

        public function factvald($cid, $dt1, $dt2, $gd = FALSE, $tcl = FALSE)
        {        
            if ($gd === '' AND $tcl === '') {
                return $this->db->query(
                "SELECT * FROM courriers_expesc es
                JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN expediteurs ex ON er.expditid = ex.id_expedit
                JOIN recepteurs re ON er.receptid = re.idrecepetion
                JOIN client cl ON ex.clientexpedit = cl.id_client
                JOIN contrat_client ctcl ON ctcl.idtype_client = cl.id_client
                JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                AND cd.clorecodecour = 1
                AND es.courcloreesc = 1
                AND es.partocouresc = IS NOT NULL
                AND es.rexepedieresc IS NULL")->result();
        
            }
            elseif ($tcl === '') {
                return $this->db->query(
                "SELECT * FROM courriers_expesc es
                JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN expediteurs ex ON er.expditid = ex.id_expedit
                JOIN recepteurs re ON er.receptid = re.idrecepetion
                JOIN client cl ON ex.clientexpedit = cl.id_client
                JOIN contrat_client ctcl ON ctcl.idtype_client = cl.id_client
                JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND lg.gaexp_lg = '$gd'
                AND cd.clorecodecour = 1
                AND es.courcloreesc = 1
                AND es.partocouresc IS NOT NULL
                AND es.rexepedieresc IS NULL
                AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'")->result();
            }
                return $this->db->query(
                "SELECT * FROM courriers_expesc es
                JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN expediteurs ex ON er.expditid = ex.id_expedit
                JOIN recepteurs re ON er.receptid = re.idrecepetion
                JOIN client cl ON ex.clientexpedit = cl.id_client
                JOIN contrat_client ctcl ON ctcl.idtype_client = cl.id_client
                JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND lg.gaexp_lg = '$gd'
                AND cd.clorecodecour = 1
                AND es.courcloreesc = 1
                AND es.partocouresc IS NOT NULL
                AND es.rexepedieresc IS NULL
                AND es.dateenvoiesc BETWEEN '$dt1' AND '$dt2'
                AND ctcl.idtype_client = '$tcl'")->result();
        }

        public function getexps($cid, $gd, $sg, $exp = FALSE)
        {
            $day = mdate("%Y-%m-%d", now('UTC'));
            if ($exp === FALSE) {
                return $this->db->query(
                "SELECT * FROM courriers_expesc e
                JOIN sousgare sg ON e.courrierdepartgareesc = sg.idsousgare
                JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN expediteurs ex ON er.expditid = ex.id_expedit
                JOIN client cl ON ex.clientexpedit = cl.id_client
                JOIN recepteurs re ON er.receptid = re.idrecepetion 
                JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                JOIN compagnies c ON gex.id_compagd = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND gex.code_gaexp = '$gd'
                AND sg.idsousgare = '$sg'
                AND e.dateenvoiesc = '$day'
                AND e.actif_couresc = 0")->result();
            }
            return $this->db->query(
                "SELECT * FROM courriers_expesc e
                JOIN sousgare sg ON e.courrierdepartgareesc = sg.idsousgare
                JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN expediteurs ex ON er.expditid = ex.id_expedit
                JOIN client cl ON ex.clientexpedit = cl.id_client
                JOIN recepteurs re ON er.receptid = re.idrecepetion 
                JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                JOIN compagnies c ON gex.id_compagd = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND gex.code_gaexp = '$gd'
                AND sg.idsousgare = '$sg'
                AND e.courrierexpidesc = '$exp'
                AND e.dateenvoiesc = '$day'
                AND e.actif_couresc = 0")->row();
        }

        public function getdests($cid, $gd, $sg, $exp = FALSE)
        {
            $day = mdate("%Y-%m-%d", now('UTC'));
            if ($exp === FALSE) {
                return $this->db->query(
                "SELECT * FROM courriers_expesc e
                JOIN sousgare sg ON e.courrierdepartgareesc = sg.idsousgare
                JOIN code_courriers cd ON e.id_codecourrierescesc = cd.codecolisid
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN expediteurs ex ON er.expditid = ex.id_expedit
                JOIN recepteurs re ON er.receptid = re.idrecepetion
                JOIN client cl ON re.client_recept = cl.id_client 
                JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                JOIN compagnies c ON gex.id_compagd = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND gex.code_gaexp = '$gd'
                AND sg.idsousgare = '$sg'
                AND e.dateenvoiesc = '$day'
                AND e.actif_couresc = 0")->result();
            }
            return $this->db->query(
                "SELECT * FROM courriers_expesc e
                JOIN sousgare sg ON e.courrierdepartgareesc = sg.idsousgare
                JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN expediteurs ex ON er.expditid = ex.id_expedit
                JOIN recepteurs re ON er.receptid = re.idrecepetion
                JOIN client cl ON re.client_recept = cl.id_client 
                JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                JOIN compagnies c ON gex.id_compagd = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND gex.code_gaexp = '$gd'
                AND sg.idsousgare = '$sg'
                AND e.courrierexpidesc = '$exp'
                AND e.dateenvoiesc = '$day'
                AND e.actif_couresc = 0")->row();
        }
        
        public function getexperso($cid, $gd, $sg, $exp = FALSE)
        {
            $day = mdate("%Y-%m-%d", now('UTC'));
            if ($exp === FALSE) {
                return $this->db->query(
                "SELECT * FROM courriers_expesc e
                JOIN sousgare sg ON e.courrierdepartgareesc = sg.idsousgare
                JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN expediteurs ex ON er.expditid = ex.id_expedit
                JOIN personnels pe ON ex.persoexp = pe.matricule
                JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                JOIN compagnies c ON gex.id_compagd = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND gex.code_gaexp = '$gd'
                AND sg.idsousgare = '$sg'
                AND e.dateenvoiesc = '$day'
                AND e.actif_couresc = 0")->result();
            }
            return $this->db->query(
                "SELECT * FROM courriers_expesc e
                JOIN sousgare sg ON e.courrierdepartgareesc = sg.idsousgare
                JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN expediteurs ex ON er.expditid = ex.id_expedit
                JOIN personnels pe ON ex.persoexp = pe.matricule
                JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                JOIN compagnies c ON gex.id_compagd = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND gex.code_gaexp = '$gd'
                AND sg.idsousgare = '$sg'
                AND e.courrierexpidesc = '$exp'
                AND e.dateenvoiesc = '$day'
                AND e.actif_couresc = 0")->row();
        }
        //compte le nombre de courrier envoyes
        public function groupcountexp($cid, $idconx, $gd, $sg)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
                return $this->db->query(
                "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, c.nom_compagnie, dest.id_compaga, e.courrierdepartgareesc  FROM courriers_expesc e
                JOIN attributions_role ar ON e.idoperateuresc = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN sousgare sg ON e.courrierdepartgareesc = sg.idsousgare
                JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN expediteurs ex ON er.expditid = ex.id_expedit
                JOIN recepteurs re ON er.receptid = re.idrecepetion 
                JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND sg.idsousgare = '$sg'
                AND e.statutcouresc = 0
                AND ar.roleattribut = '$idconx'
                AND ul.guser = '$gd'
                AND cu.is_conect = 1
                AND ar.activeattrib = 1
                AND e.dateenvoiesc <= '$today'
                AND e.prixcolisesc IS NOT NULL
                AND e.partocouresc IS NULL
                AND cu.date_conect <= '$today'
                AND e.actif_couresc = 0
                GROUP BY e.idoperateuresc, dest.id_compaga, c.nom_compagnie")->result();
        }

        public function groupcountexptr($cid, $idconx, $gd, $sg)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
                return $this->db->query(
                "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, c.nom_compagnie, dest.id_compaga, e.courrierdepartgareesc  FROM courriers_expesc e
                JOIN attributions_role ar ON e.idoperateuresc = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN sousgare sg ON e.courrierdepartgareesc = sg.idsousgare
                JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN expediteurs ex ON er.expditid = ex.id_expedit
                JOIN recepteurs re ON er.receptid = re.idrecepetion 
                JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND e.courrierdepartgareesc NOT IN (SELECT s.idsousgare FROM sousgare s WHERE s.gareprinceid = '$g')
                AND e.statutcouresc = 0
                AND ar.roleattribut = '$idconx'
                AND ul.guser = '$gd'
                AND cu.is_conect = 1
                AND ar.activeattrib = 1
                AND e.dateenvoiesc <= '$today'
                AND e.prixcolisesc IS NOT NULL
                AND e.partocouresc IS NULL
                AND cu.date_conect <= '$today'
                AND e.actif_couresc = 0
                GROUP BY e.idoperateuresc, dest.id_compaga, c.nom_compagnie")->result();
        }

        public function countexp($cid, $idconx, $gd, $sg)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
                return $this->db->query(
                "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc FROM courriers_expesc e
                JOIN attributions_role ar ON e.idoperateuresc = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN sousgare sg ON e.courrierdepartgareesc = sg.idsousgare
                JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN expediteurs ex ON er.expditid = ex.id_expedit
                JOIN recepteurs re ON er.receptid = re.idrecepetion 
                JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND sg.idsousgare = '$sg'
                AND e.statutcouresc = 0
                AND ar.roleattribut = '$idconx'
                AND ul.guser = '$gd'
                AND cu.is_conect = 1
                AND ar.activeattrib = 1
                AND e.dateenvoiesc <= '$today'
                AND e.prixcolisesc IS NOT NULL
                AND e.partocouresc IS NULL
                AND cu.date_conect <= '$today'
                AND e.actif_couresc = 0
                GROUP BY e.idoperateuresc")->row();
        }

        public function compteur($cd, $idcox, $g)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
            
            return $this->db->query("SELECT SUM(prixcolisesc) AS totaenesc FROM courriers_expesc e
                JOIN attributions_role ar ON e.idoperateuresc = ar.roleattribut
                WHERE ar.roleattribut = '$idcox'
                AND ar.activeattrib = 1
                AND e.statutcouresc = 0
                AND e.dateenvoiesc <= '$today'
                AND e.prixcolisesc IS NOT NULL
                AND e.partocouresc IS NULL
                AND e.actif_couresc = 0
                GROUP BY e.idoperateuresc")->row();
        }

        public function compteurcd($cd, $idcox, $g)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));            
            return $this->db->query("SELECT SUM(prixcolisesc) AS totaenesc FROM courriers_expesc e
                JOIN attributions_role ar ON e.idoperateuresc = ar.roleattribut
                WHERE ar.roleattribut = '$idcox'
                AND ar.activeattrib = 1
                AND e.statutcouresc = 0
                AND e.dateenvoiesc < '$today'
                AND e.prixcolisesc IS NOT NULL
                AND e.prixcolisesc != '0.00'
                AND e.partocouresc IS NULL
                GROUP BY e.idoperateuresc")->row();
        }


        public function getexpedition($cid, $exp = FALSE)
        {
            if ($exp === FALSE) {
                return $this->db->query(
                "SELECT * FROM courriers_expesc e
                JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                JOIN sousgare sg ON e.courrierdepartgareesc = sg.idsousgare
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND e.actif_couresc = 0")->result();
            }
            return $this->db->query(
                "SELECT * FROM courriers_expesc e
                JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                JOIN sousgare sg ON e.courrierdepartgareesc = sg.idsousgare
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND e.courrierexpidesc = '$exp'
                AND e.actif_couresc = 0
                ORDER BY e.courrierexpidesc DESC LIMIT 1")->row();
        }

        public function getexpedition1($cid, $cdpg, $exp = FALSE)
        {
            if ($exp === FALSE) {
                return $this->db->query(
                "SELECT * FROM courriers_expesc e
                JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                JOIN sousgare sg ON e.courrierdepartgareesc = sg.idsousgare
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND e.departcolisesc = '$cdpg'
                AND e.actif_couresc = 0")->result();
            }
            return $this->db->query(
                "SELECT * FROM courriers_expesc e
                JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                JOIN sousgare sg ON e.courrierdepartgareesc = sg.idsousgare
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND e.departcolisesc = '$cdpg'
                AND e.courrierexpidesc = '$exp'
                AND e.actif_couresc = 0")->row();
        }

        public function rapexp($cid, $idconx, $comp, $gd, $sg)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
                return $this->db->query(
                "SELECT COUNT(courrierexpidesc) AS nombres, SUM(prixcolisesc) AS montant, lg.ident_ligne, lg.nom_ligne, e.prixcolisesc, e.courrierdepartgareesc, dest.id_compaga, ar.roleattribut FROM courriers_expesc e
                JOIN attributions_role ar ON e.idoperateuresc = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN sousgare sg ON e.courrierdepartgareesc = sg.idsousgare
                JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN expediteurs ex ON er.expditid = ex.id_expedit
                JOIN recepteurs re ON er.receptid = re.idrecepetion 
                JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND sg.idsousgare = '$sg'
                AND e.statutcouresc = 1
                AND ar.roleattribut = '$idconx'
                AND ul.guser = '$gd'
                AND cu.is_conect = 1
                AND ar.activeattrib = 1
                AND dest.id_compaga = '$comp'
                AND e.dateenvoiesc <= '$today'
                AND e.prixcolisesc IS NOT NULL
                AND e.partocouresc IS NULL
                AND cu.date_conect <= '$today'
                AND e.actif_couresc = 0
                AND e.validcouresc = 0
                GROUP BY lg.ident_ligne, e.courrierdepartgareesc, e.prixcolisesc, dest.id_compaga, e.idoperateuresc")->result();
        }

        public function getdest($cid, $gd, $sg, $exp = FALSE)
        {
            $day = mdate("%Y-%m-%d", now('UTC'));
            if ($exp === FALSE) {
                return $this->db->query(
                "SELECT * FROM courriers_expesc e
                JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN sousgare sg ON e.sousgarearrividesc = sg.idsousgare
                JOIN recepteurs re ON er.receptid = re.idrecepetion
                JOIN client cl ON re.client_recept = cl.id_client 
                JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND e.garearrivecolisesc = '$gd'
                AND sg.idsousgare = '$sg'
                AND e.is_validcouresc = 0
                AND e.actif_couresc = 0
                AND e.statuscourrieresc = 'pas_transit'")->result();
            }
            return $this->db->query(
                "SELECT * FROM courriers_expesc e
                JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN sousgare sg ON e.sousgarearrividesc = sg.idsousgare
                JOIN recepteurs re ON er.receptid = re.idrecepetion
                JOIN client cl ON re.client_recept = cl.id_client 
                JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND e.garearrivecolisesc = '$gd'
                AND e.courrierexpidesc = '$exp'
                AND e.is_validcouresc = 0
                AND e.actif_couresc = 0
                AND sg.idsousgare = '$sg'
                AND e.statuscourrieresc = 'pas_transit'")->row();
        }

        public function lg($cid, $gd, $sg)
        {
                return $this->db->query(
                "SELECT lg.ident_ligne, lg.nom_ligne FROM courriers_expesc e
                JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN sousgare sg ON e.sousgarearrividesc = sg.idsousgare
                JOIN recepteurs re ON er.receptid = re.idrecepetion
                JOIN client cl ON re.client_recept = cl.id_client 
                JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND e.garearrivecolisesc = '$gd'
                AND sg.idsousgare = '$sg'
                AND e.is_validcouresc = 0
                AND e.statuscourrieresc = 'pas_transit'
                GROUP BY lg.ident_ligne, e.garearrivecolisesc, sg.idsousgare")->result();
        }

        public function getrecept($cid, $expid, $gd, $sgid)
        {
            
            return $this->db->query(
                "SELECT * FROM courriers_expesc e
                JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN sousgare sg ON e.sousgarearrividesc = sg.idsousgare
                JOIN recepteurs re ON er.receptid = re.idrecepetion
                JOIN client cl ON re.client_recept = cl.id_client
                JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND BINARY e.num_couresc = '$expid'
                AND e.garearrivecolisesc = '$gd'
                AND e.is_validcouresc = 1
                AND sg.idsousgare = '$sgid'
                AND e.actif_couresc = 0
                AND re.datetimerecept IS NULL")->row();
        }

        public function getreceptperso($cid, $expid, $gd, $sgid)
        {
            
            return $this->db->query(
                "SELECT * FROM courriers_expesc e
                JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN sousgare sg ON e.sousgarearrividesc = sg.idsousgare
                JOIN recepteurs re ON er.receptid = re.idrecepetion
                JOIN personnels pe ON re.persorecep = pe.matricule
                JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND BINARY e.num_couresc = '$expid'
                AND e.garearrivecolisesc = '$gd'
                AND e.is_validcouresc = 1
                AND sg.idsousgare = '$sgid'
                AND e.actif_couresc = 0
                AND re.datetimerecept IS NULL")->row();
        }

        public function vald($cid, $gd, $sg, $sgdep, $dat, $hre, $exp = FALSE)
        {
            if ($exp === FALSE) {
                return $this->db->query(
                "SELECT * FROM courriers_expesc e
                JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN sousgare sg ON e.sousgarearrividesc = sg.idsousgare
                JOIN recepteurs re ON er.receptid = re.idrecepetion
                JOIN client cl ON re.client_recept = cl.id_client 
                JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND e.garearrivecolisesc = '$gd'
                AND sg.idsousgare = '$sg'
                AND e.courrierdepartgareesc = '$sgdep'
                AND e.dateenvoiesc = '$dat'
                AND h.id_heure = '$hre'
                AND e.is_validcouresc = 1")->result();
            }
            return $this->db->query(
                "SELECT * FROM courriers_expesc e
                JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN sousgare sg ON e.sousgarearrividesc = sg.idsousgare
                JOIN recepteurs re ON er.receptid = re.idrecepetion
                JOIN client cl ON re.client_recept = cl.id_client
                JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND e.garearrivecolisesc = '$gd'
                AND sg.idsousgare = '$sg'
                AND e.dateenvoiesc = '$dat'
                AND h.id_heure = '$hre'
                AND e.is_validcouresc = 1
                AND e.courrierdepartgareesc = '$sgdep'
                AND e.courrierexpidesc = '$exp'")->row();
        }

        public function valdpers($cid, $gd, $sg, $sgdep, $dat, $hre, $exp = FALSE)
        {
            if ($exp === FALSE) {
                return $this->db->query(
                "SELECT * FROM courriers_expesc e
                JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN sousgare sg ON e.sousgarearrividesc = sg.idsousgare
                JOIN recepteurs re ON er.receptid = re.idrecepetion
                JOIN personnels pe ON re.persorecep = pe.matricule
                JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND e.garearrivecolisesc = '$gd'
                AND sg.idsousgare = '$sg'
                AND e.courrierdepartgareesc = '$sgdep'
                AND e.dateenvoiesc = '$dat'
                AND h.id_heure = '$hre'
                AND e.is_validcouresc = 1")->result();
            }
            return $this->db->query(
                "SELECT * FROM courriers_expesc e
                JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN sousgare sg ON e.sousgarearrividesc = sg.idsousgare
                JOIN recepteurs re ON er.receptid = re.idrecepetion
                JOIN personnels pe ON re.persorecep = pe.matricule
                JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND e.garearrivecolisesc = '$gd'
                AND sg.idsousgare = '$sg'
                AND e.courrierdepartgareesc = '$sgdep'
                AND e.dateenvoiesc = '$dat'
                AND h.id_heure = '$hre'
                AND e.is_validcouresc = 1
                AND e.courrierexpidesc = '$exp'")->row();
        }

        public function listad1($cid, $cdprog, $h, $dt, $qt = FALSE)
        {
            if($qt === ''){
                return $this->db->query(
                "SELECT * FROM courriers_expesc e
                JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN sousgare sg ON e.courrierdepartgareesc = sg.idsousgare
                JOIN recepteurs re ON er.receptid = re.idrecepetion
                JOIN client cl ON re.client_recept = cl.id_client 
                JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND e.departcolisesc = '$cdprog'
                AND lh.id_ligneheure = '$h'
                AND e.dateenvoiesc = '$dt'")->result();
            }
            return $this->db->query(
                "SELECT * FROM courriers_expesc e
                JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN sousgare sg ON e.courrierdepartgareesc = sg.idsousgare
                JOIN recepteurs re ON er.receptid = re.idrecepetion
                JOIN client cl ON re.client_recept = cl.id_client 
                JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND e.departcolisesc = '$cdprog'
                AND lh.id_ligneheure = '$h'
                AND e.dateenvoiesc = '$dt'
                AND e.quartier_courrieresc = '$qt'")->result();
        }

        public function list1($cid, $gid, $sgd, $cdprog, $h, $dt, $qt = FALSE)
        {
            if($qt === ''){

                return $this->db->query(
                    "SELECT * FROM courriers_expesc e
                    JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN sousgare sg ON e.courrierdepartgareesc = sg.idsousgare
                    JOIN recepteurs re ON er.receptid = re.idrecepetion
                    JOIN client cl ON re.client_recept = cl.id_client
                    JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND e.departcolisesc = '$cdprog'
                    AND lh.id_ligneheure = '$h'
                    AND e.dateenvoiesc = '$dt'
                    AND gex.code_gaexp = '$gid'
                    AND sg.idsousgare = '$sgd'")->result();
            }
                return $this->db->query(
                    "SELECT * FROM courriers_expesc e
                    JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN sousgare sg ON e.courrierdepartgareesc = sg.idsousgare
                    JOIN recepteurs re ON er.receptid = re.idrecepetion
                    JOIN client cl ON re.client_recept = cl.id_client
                    JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND e.departcolisesc = '$cdprog'
                    AND lh.id_ligneheure = '$h'
                    AND e.dateenvoiesc = '$dt'
                    AND gex.code_gaexp = '$gid'
                    AND sg.idsousgare = '$sgd'
                    AND e.quartier_courrieresc = '$qt'")->result();   
        }
    }
    /** Courriers_expesc_model.php **/
    /** application/models/Courriers_expesc_model.php **/
