<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Tamponcodetr_model extends CI_Model
    {
        protected $table = 'tamponcodetr';

        /** Dernier code effectivement réservé (peut différer du préféré si collision). */
        public $last_codtampon = null;

        public function __construct()
        {
            parent::__construct();
        }

        public function exists($codtampon)
        {
            $codtampon = trim((string) $codtampon);
            if ($codtampon === '') {
                return false;
            }
            return (int) $this->db->where('codtampon', $codtampon)
                ->count_all_results($this->table) > 0;
        }

        /**
         * True si un passager (ou tamponcode) référence déjà ce code.
         */
        public function is_linked($codtampon)
        {
            $codtampon = trim((string) $codtampon);
            if ($codtampon === '') {
                return false;
            }
            $code = $this->db->escape($codtampon);

            if ($this->db->field_exists('tamponcodetr', 'passager')) {
                $n = (int) $this->db->query(
                    "SELECT COUNT(*) AS n FROM passager WHERE tamponcodetr = {$code}"
                )->row()->n;
                if ($n > 0) {
                    return true;
                }
            }

            if ($this->db->table_exists('tamponcode')
                && $this->db->field_exists('tamponcodtr', 'tamponcode')
            ) {
                $n = (int) $this->db->query(
                    "SELECT COUNT(*) AS n FROM tamponcode WHERE tamponcodtr = {$code}"
                )->row()->n;
                if ($n > 0) {
                    return true;
                }
            }

            return false;
        }

        /**
         * Choisit un code libre : réutilise un orphelin, sinon suffixe R1, R2…
         */
        public function allocate($preferred)
        {
            $preferred = trim((string) $preferred);
            if ($preferred === '') {
                $preferred = 'T' . date('ymdHis') . mt_rand(10, 99);
            }

            $code = $preferred;
            for ($i = 0; $i < 40; $i++) {
                if (!$this->exists($code)) {
                    return $code;
                }
                // Déjà en base mais sans vente liée → retry après échec partiel.
                if (!$this->is_linked($code)) {
                    return $code;
                }
                $code = $preferred . 'R' . ($i + 1);
            }

            return $preferred . 'R' . substr(str_replace('.', '', uniqid('', true)), -8);
        }

        /**
         * Réserve un code unique et retourne le code réel (à assigner à $tampo).
         */
        public function reserve($preferred)
        {
            $code = $this->allocate($preferred);
            $this->last_codtampon = $code;

            if (!$this->exists($code)) {
                $ok = $this->db->insert($this->table, array('codtampon' => $code));
                if (!$ok) {
                    // Course concurrente : retenter une fois avec suffixe aléatoire.
                    $code = $this->allocate($preferred . 'R' . mt_rand(100, 999));
                    $this->last_codtampon = $code;
                    if (!$this->exists($code)) {
                        $this->db->insert($this->table, array('codtampon' => $code));
                    }
                }
            }

            return $code;
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

            $preferred = isset($data['codtampon']) ? $data['codtampon'] : '';
            $code = $this->reserve($preferred);
            // Permet au caller de récupérer le code réel si le tableau est réutilisé.
            $data['codtampon'] = $code;

            return $code;
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
