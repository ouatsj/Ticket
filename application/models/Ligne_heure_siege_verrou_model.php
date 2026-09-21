<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Verrous admin de sièges liés à une ligne_heure (Paramètres).
 * Source de vérité pour préremplir programme_siege_bloque à la création.
 */
class Ligne_heure_siege_verrou_model extends CI_Model
{
    protected $table = 'ligne_heure_siege_verrou';

    public function __construct()
    {
        parent::__construct();
    }

    public function ensure_table()
    {
        if ($this->db->table_exists($this->table)) {
            return true;
        }
        $this->db->query(
            "CREATE TABLE IF NOT EXISTS {$this->table} (
                id_verrou INT UNSIGNED NOT NULL AUTO_INCREMENT,
                id_entreprise INT UNSIGNED NOT NULL,
                id_ligneheure INT UNSIGNED NOT NULL,
                siege_num INT UNSIGNED NOT NULL,
                created_by INT UNSIGNED NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id_verrou),
                UNIQUE KEY uq_lh_siege (id_ligneheure, siege_num),
                KEY idx_entreprise (id_entreprise),
                KEY idx_ligneheure (id_ligneheure)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
        return $this->db->table_exists($this->table);
    }

    /**
     * @return int[]
     */
    public function sieges_for_ligneheure($id_ligneheure)
    {
        $this->ensure_table();
        $id = (int) $id_ligneheure;
        if ($id <= 0 || !$this->db->table_exists($this->table)) {
            return array();
        }
        $rows = $this->db->select('siege_num')
            ->where('id_ligneheure', $id)
            ->order_by('siege_num', 'ASC')
            ->get($this->table)
            ->result();
        $out = array();
        foreach ($rows as $r) {
            $n = (int) $r->siege_num;
            if ($n > 0) {
                $out[] = $n;
            }
        }
        return $out;
    }

    /**
     * Map id_ligneheure => int[] pour l’entreprise.
     *
     * @return array
     */
    public function map_for_entreprise($id_entreprise)
    {
        $this->ensure_table();
        $eid = (int) $id_entreprise;
        if ($eid <= 0 || !$this->db->table_exists($this->table)) {
            return array();
        }
        $rows = $this->db->select('id_ligneheure, siege_num')
            ->where('id_entreprise', $eid)
            ->order_by('id_ligneheure', 'ASC')
            ->order_by('siege_num', 'ASC')
            ->get($this->table)
            ->result();
        $map = array();
        foreach ($rows as $r) {
            $lh = (int) $r->id_ligneheure;
            $n = (int) $r->siege_num;
            if ($lh <= 0 || $n <= 0) {
                continue;
            }
            if (!isset($map[$lh])) {
                $map[$lh] = array();
            }
            $map[$lh][] = $n;
        }
        return $map;
    }

    /**
     * Remplace la liste des sièges verrouillés pour une ligne_heure.
     *
     * @param int[] $sieges
     */
    public function replace_for_ligneheure($id_entreprise, $id_ligneheure, array $sieges, $created_by = null)
    {
        $this->ensure_table();
        $eid = (int) $id_entreprise;
        $lh = (int) $id_ligneheure;
        if ($eid <= 0 || $lh <= 0 || !$this->db->table_exists($this->table)) {
            return false;
        }
        $norm = array();
        foreach ($sieges as $n) {
            $n = (int) $n;
            if ($n > 0) {
                $norm[$n] = $n;
            }
        }
        $norm = array_values($norm);
        sort($norm);

        $this->db->where('id_ligneheure', $lh)->delete($this->table);
        foreach ($norm as $n) {
            $this->db->insert($this->table, array(
                'id_entreprise' => $eid,
                'id_ligneheure' => $lh,
                'siege_num' => $n,
                'created_by' => $created_by !== null ? (int) $created_by : null,
            ));
        }
        return true;
    }

    /**
     * Fusionne les verrous admin dans une liste de sièges bloqués programme.
     *
     * @param int[] $bloques
     * @return int[]
     */
    public function merge_into_bloques($id_ligneheure, array $bloques)
    {
        $verrous = $this->sieges_for_ligneheure($id_ligneheure);
        if (empty($verrous)) {
            return array_values(array_unique(array_map('intval', $bloques)));
        }
        $set = array();
        foreach ($bloques as $n) {
            $n = (int) $n;
            if ($n > 0) {
                $set[$n] = $n;
            }
        }
        foreach ($verrous as $n) {
            $set[$n] = $n;
        }
        $out = array_values($set);
        sort($out);
        return $out;
    }

    /**
     * Applique les verrous aux programmes futurs (date >= aujourd’hui) d’une ligne_heure.
     *
     * @return int nombre de programmes mis à jour
     */
    public function apply_to_future_programmes($id_ligneheure)
    {
        $lh = (int) $id_ligneheure;
        if ($lh <= 0) {
            return 0;
        }
        $verrous = $this->sieges_for_ligneheure($lh);
        if (!isset($this->m_programme)) {
            $this->load->model('Programme_model', 'm_programme');
        }
        $today = date('Y-m-d');
        $rows = $this->db->query(
            "SELECT code_progr, intervalle1, intervalle2
             FROM programme
             WHERE id_heur = ?
               AND date_progr >= ?
               AND (actif_prog = 0 OR actif_prog IS NULL)",
            array($lh, $today)
        )->result();
        $n = 0;
        foreach ($rows as $pr) {
            $code = (string) $pr->code_progr;
            $exist = $this->m_programme->sieges_bloques_programme($code);
            $merged = $this->merge_into_bloques($lh, $exist);
            // Si verrous vidés : retirer seulement les anciens verrous? On remplace
            // en fusionnant : si template vide, on ne touche pas aux blocs manuels.
            if (empty($verrous)) {
                continue;
            }
            $this->m_programme->sync_sieges_bloques_programme(
                $code,
                $merged,
                (int) $pr->intervalle1,
                (int) $pr->intervalle2
            );
            $n++;
        }
        return $n;
    }
}
