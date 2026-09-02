<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Tampon_siege_model extends CI_Model
    {
        protected $table = 'tampon_siege';

        /** TTL par défaut des réservations UI (minutes). */
        const DEFAULT_TTL_MINUTES = 45;
        
        public function __construct()
        {
            parent::__construct();
        }

        /**
         * Schéma minimal : created_at + index UNIQUE (codepro, numsieg).
         */
        public function ensure_schema()
        {
            if (!$this->db->table_exists($this->table)) {
                return false;
            }
            if (!$this->db->field_exists('created_at', $this->table)) {
                $this->db->query(
                    "ALTER TABLE {$this->table}
                     ADD COLUMN created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP"
                );
            }
            $idx = $this->db->query("SHOW INDEX FROM {$this->table} WHERE Key_name = 'uq_tampon_code_siege'")->row();
            if (!$idx) {
                $this->db->query(
                    "DELETE t1 FROM {$this->table} t1
                     INNER JOIN {$this->table} t2
                       ON t1.codepro = t2.codepro AND t1.numsieg = t2.numsieg AND t1.idtamp > t2.idtamp
                     WHERE t1.codepro IS NOT NULL AND t1.numsieg IS NOT NULL"
                );
                $this->db->query(
                    "ALTER TABLE {$this->table}
                     ADD UNIQUE KEY uq_tampon_code_siege (codepro, numsieg)"
                );
            }
            return true;
        }
        
        public function get($p, $n)
        {
            return $this->db->query(
                "SELECT * FROM {$this->table} t
                 WHERE t.codepro = ? AND t.numsieg = ?",
                array(trim((string) $p), (int) $n)
            )->result();
        }

        /**
         * Alias historique (verifisiegesnr) — même résultat que get().
         *
         * @param string $p
         * @param int|string $n
         * @return array
         */
        public function get1($p, $n)
        {
            return $this->get($p, $n);
        }

        /**
         * Réserve un siège en tampon (idempotent si déjà présent).
         *
         * @param string $codepro
         * @param int $numsieg
         * @return array|null lignes tampon ou null si refus
         */
        public function reserve($codepro, $numsieg)
        {
            $codepro = trim((string) $codepro);
            $numsieg = (int) $numsieg;
            if ($codepro === '' || $numsieg <= 0) {
                return null;
            }

            $this->ensure_schema();

            $existing = $this->get($codepro, $numsieg);
            if (!empty($existing)) {
                return $existing;
            }

            $data = array(
                'codepro' => $codepro,
                'numsieg' => $numsieg,
                'dateid' => mdate('%Y-%m-%d', now('UTC')),
            );
            if ($this->db->field_exists('created_at', $this->table)) {
                $data['created_at'] = date('Y-m-d H:i:s');
            }

            if (!$this->db->insert($this->table, $data)) {
                $err = $this->db->error();
                if (!empty($err['code']) && (int) $err['code'] === 1062) {
                    return $this->get($codepro, $numsieg);
                }
                return null;
            }

            return $this->get($codepro, $numsieg);
        }

        public function create(array $data)
        {
            $this->ensure_schema();
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

        /**
         * Supprime les tampons expirés (TTL).
         *
         * @param int $minutes
         * @return int nombre de lignes supprimées
         */
        public function purge_expired($minutes = null)
        {
            if (!$this->db->table_exists($this->table)) {
                return 0;
            }
            $minutes = ($minutes === null) ? self::DEFAULT_TTL_MINUTES : max(1, (int) $minutes);
            if (!$this->db->field_exists('created_at', $this->table)) {
                return 0;
            }
            $cutoff = date('Y-m-d H:i:s', time() - ($minutes * 60));
            $this->db->query(
                "DELETE FROM {$this->table} WHERE created_at < ?",
                array($cutoff)
            );
            return (int) $this->db->affected_rows();
        }

        /**
         * Siège déjà en tampon sur le stock partagé ?
         *
         * @param string[] $codes
         * @param int $numsieg
         * @return bool
         */
        public function siege_en_tampon(array $codes, $numsieg)
        {
            $numsieg = (int) $numsieg;
            if ($numsieg <= 0 || empty($codes) || !$this->db->table_exists($this->table)) {
                return false;
            }
            $esc = array();
            foreach ($codes as $c) {
                $c = trim((string) $c);
                if ($c !== '') {
                    $esc[] = $this->db->escape($c);
                }
            }
            if (empty($esc)) {
                return false;
            }
            $in = implode(',', $esc);
            $row = $this->db->query(
                "SELECT 1 AS o FROM {$this->table}
                 WHERE codepro IN ({$in}) AND numsieg = ?
                 LIMIT 1",
                array($numsieg)
            )->row();
            return ($row !== null);
        }
    }
    /** Tampon_siege_model.php **/
    /** application/models/Tampon_siege_model.php **/
