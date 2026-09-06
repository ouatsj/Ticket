<?php defined('BASEPATH') OR exit('No direct script access allowed');
    
    class Utilisateur_model extends CI_Model
    {
        protected $table = 'utilisateurs';
        
        public function __construct()
        {
            parent::__construct();
        }

        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }     
                
        public function update($uid, array $data)
        {
            return $this->db->where('uid', $uid)
            ->update($this->table, $data);
        }

        public function del($id)
        {
            return $this->db->where('uid', $id)->delete($this->table);
        }

        public function get_user($cid, $user_id = FALSE)
        {
            if ($user_id === FALSE) {
                return $this->db->query(
                    "SELECT * FROM compte_user cu
                    JOIN user_login ul ON ul.uid_usercpte = cu.cpuser_id
                    JOIN attributions_role ar ON ar.idgestcompte = ul.uid_login
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN user_roles r ON ar.userole = r.id_rols
                    JOIN entreprise e ON u.cle_comp = e.ekey
                    WHERE e.ekey = '$cid'
                    AND ar.activer_role = 0")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM compte_user cu
                    JOIN user_login ul ON ul.uid_usercpte = cu.cpuser_id
                    JOIN attributions_role ar ON ar.idgestcompte = ul.uid_login
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN user_roles r ON ar.userole = r.id_rols
                    JOIN entreprise e ON u.cle_comp = e.ekey
                    WHERE e.ekey = '$cid'
                    AND u.uid = '$user_id'
                    AND ar.activer_role = 0")->row();
        }
        
        /**
         * SELECT compte_user optionnels (colonnes ajoutées par migrations).
         * @return string
         */
        protected function _compte_user_select_extras()
        {
            static $extras = null;
            if ($extras !== null) {
                return $extras;
            }
            $cols = array(
                'cu.cpuser_id',
                'cu.username',
                'cu.activer',
                'cu.is_conect',
                'cu.date_deconect',
            );
            $optional = array(
                'derniere_activite_at',
                'exempt_desactivation_auto',
                'autorisation_vente_forcee',
                'autorisation_vente_jusquau',
                'desactivation_motif',
                'desactivation_at',
            );
            foreach ($optional as $col) {
                if ($this->db->field_exists($col, 'compte_user')) {
                    $cols[] = 'cu.' . $col;
                } else {
                    $cols[] = 'NULL AS ' . $col;
                }
            }
            $extras = implode(",\n                        ", $cols);
            return $extras;
        }

        public function get_use($cid, $user_id = FALSE)
        {
            $cidEsc = $this->db->escape_str($cid);
            $selectCu = $this->_compte_user_select_extras();
            if ($user_id === FALSE) {
                $q = $this->db->query(
                    "SELECT u.*, {$selectCu}
                    FROM utilisateurs u
                    JOIN entreprise e ON u.cle_comp = e.ekey
                    LEFT JOIN compte_user cu ON cu.userlog_id = u.uid
                    WHERE e.ekey = '{$cidEsc}'"
                );
                return $q ? $q->result() : array();
            }

            $uidEsc = $this->db->escape_str($user_id);
            $q = $this->db->query(
                "SELECT u.*, {$selectCu}
                FROM utilisateurs u
                JOIN entreprise e ON u.cle_comp = e.ekey
                LEFT JOIN compte_user cu ON cu.userlog_id = u.uid
                WHERE e.ekey = '{$cidEsc}'
                AND u.uid = '{$uidEsc}'"
            );
            return $q ? $q->row() : null;
        }


        //compte utilisateur
        public function userget($cid, $uid, $user_id = FALSE)
        {
            if ($user_id === FALSE) {
                return $this->db->query(
                    "SELECT * FROM compte_user cu
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN entreprise e ON u.cle_comp = e.ekey
                    WHERE e.ekey = '$cid'
                    AND u.uid = '$uid'")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM compte_user cu
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN entreprise e ON u.cle_comp = e.ekey
                    WHERE e.ekey = '$cid'
                    AND u.uid = '$uid'
                    AND cu.cpuser_id = '$user_id'")->row();
        }

        
        public function getgare($cid, $uid, $user_id = FALSE)
        {
            if ($user_id === FALSE) {
                return $this->db->query(
                    "SELECT * FROM user_login ul
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN entreprise e ON u.cle_comp = e.ekey
                    WHERE e.ekey = '$cid'
                    AND cu.cpuser_id = '$uid'")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM user_login ul
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN entreprise e ON u.cle_comp = e.ekey
                    WHERE e.ekey = '$cid'
                    AND cu.cpuser_id = '$uid'
                    AND ul.uid_login = '$user_id'")->row();
        }

        public function getrole($cid, $uid, $g, $user_id = FALSE)
        {
            if ($user_id === FALSE) {
                return $this->db->query(
                    "SELECT * FROM attributions_role ar
                     JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                     JOIN user_roles r ON ar.userole = r.id_rols
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN entreprise e ON u.cle_comp = e.ekey
                    WHERE e.ekey = '$cid'
                    AND ul.uid_login = '$uid'
                    AND ul.guser = '$g'")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM attributions_role ar
                     JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                     JOIN user_roles r ON ar.userole = r.id_rols
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN entreprise e ON u.cle_comp = e.ekey
                    WHERE e.ekey = '$cid'
                    AND ul.uid_login = '$uid'
                    AND ul.guser = '$g'
                    AND ul.roleattribut = '$user_id'")->row();
        }

        public function u($a)
        {
            
                return $this->db->query(
                    "SELECT * FROM compte_user cu
                    JOIN user_login ul ON ul.uid_usercpte = cu.cpuser_id
                    JOIN attributions_role ar ON ar.idgestcompte = ul.uid_login
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    WHERE ar.roleattribut = '$a'")->row();
        }

        /**
         * Comptes login liés à une fiche utilisateur.
         *
         * @return object[]
         */
        public function comptes_of($uid, $ekey = null)
        {
            $uid = (int) $uid;
            if ($uid <= 0) {
                return array();
            }

            if ($ekey === null || $ekey === '') {
                return $this->db->query(
                    "SELECT cu.cpuser_id FROM compte_user cu WHERE cu.userlog_id = ?",
                    array($uid)
                )->result();
            }

            return $this->db->query(
                "SELECT cu.cpuser_id FROM compte_user cu
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN entreprise e ON u.cle_comp = e.ekey
                WHERE cu.userlog_id = ?
                AND e.ekey = ?",
                array($uid, $ekey)
            )->result();
        }

        /**
         * True si la fiche utilisateur (et tous ses comptes) n'a jamais travaillé.
         */
        public function peut_supprimer($uid, $ekey = null)
        {
            $uid = (int) $uid;
            if ($uid <= 0) {
                return false;
            }

            if ($ekey !== null && $ekey !== '') {
                $row = $this->db->query(
                    "SELECT u.uid FROM utilisateurs u
                    JOIN entreprise e ON u.cle_comp = e.ekey
                    WHERE u.uid = ? AND e.ekey = ? LIMIT 1",
                    array($uid, $ekey)
                )->row();
                if (!$row) {
                    return false;
                }
            }

            $this->load->model('Compte_user_model', 'm_compte_user');
            foreach ($this->comptes_of($uid, $ekey) as $compte) {
                if (!$this->m_compte_user->peut_supprimer((int) $compte->cpuser_id)) {
                    return false;
                }
            }

            return true;
        }

        /**
         * Supprime la fiche utilisateur + comptes associés s'il n'y a aucune activité métier.
         *
         * @return array{ok:bool,error?:string}
         */
        public function delete_if_unused($uid, $ekey = null)
        {
            $uid = (int) $uid;
            if ($uid <= 0) {
                return array('ok' => false, 'error' => 'Utilisateur invalide.');
            }

            if ($ekey !== null && $ekey !== '') {
                $owned = $this->db->query(
                    "SELECT u.uid FROM utilisateurs u
                    JOIN entreprise e ON u.cle_comp = e.ekey
                    WHERE u.uid = ? AND e.ekey = ? LIMIT 1",
                    array($uid, $ekey)
                )->row();
                if (!$owned) {
                    return array('ok' => false, 'error' => 'Utilisateur introuvable pour cette entreprise.');
                }
            }

            $this->load->model('Compte_user_model', 'm_compte_user');
            $comptes = $this->comptes_of($uid, $ekey);
            foreach ($comptes as $compte) {
                $check = $this->m_compte_user->usage_reasons((int) $compte->cpuser_id);
                if (!empty($check)) {
                    return array(
                        'ok' => false,
                        'error' => 'Suppression impossible : l\'utilisateur a déjà travaillé ('
                            . implode(', ', $check) . '). Désactivez le compte à la place.',
                    );
                }
            }

            foreach ($comptes as $compte) {
                $res = $this->m_compte_user->delete_if_unused((int) $compte->cpuser_id, $ekey);
                if (empty($res['ok'])) {
                    return $res;
                }
            }

            $this->del($uid);
            return array('ok' => true);
        }
    }
