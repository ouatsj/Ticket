<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Admin — Rapport des arrêts de compte guichet par sous-gare et guichetier.
 */
class Rapport_arret_compte extends MY_Controller
{
    public $property = array(
        'title' => 'Rapports arrêts de compte',
    );

    public $company;

    public function __construct()
    {
        parent::__construct();
        setlocale(LC_TIME, 'fr_FR', 'fra');
        $this->property['pagetitle'] = utf8_encode(strftime('%d %b %G', now()));
    }

    protected function _require_admin()
    {
        if (!$this->session->userdata('agent') || !$this->session->userdata('company')) {
            redirect('login/ins');
            exit;
        }
        $role = (string) $this->session->agent->userole;
        if (!in_array($role, array('1', '2'), true)) {
            show_error('Accès réservé aux administrateurs / superviseurs.', 403);
            exit;
        }
    }

    protected function _compagnies_map()
    {
        $map = array();
        $q = $this->db->query("SELECT cle_compagnie, nom_compagnie FROM compagnies");
        if ($q) {
            foreach ($q->result() as $r) {
                $map[(string) $r->cle_compagnie] = (string) $r->nom_compagnie;
            }
        }
        return $map;
    }

    protected function _sousgares_list()
    {
        return $this->db->query(
            "SELECT idsousgare, nomsousgare, gareprinceid
             FROM sousgare
             ORDER BY gareprinceid ASC, nomsousgare ASC"
        )->result();
    }

    /**
     * Comptes de guichet (rôles 6 vente, 10 mobile, 17 escale) avec nom d'utilisateur normal.
     */
    protected function _guichetiers_list($ekey, $gare_code = null)
    {
        $where = array(
            'e.ekey = ?',
            'ar.userole IN (6, 10, 17)',
            'IFNULL(ar.activer_role, 0) = 0',
            'IFNULL(ul.comptactif, 0) = 0',
        );
        $binds = array((int) $ekey);
        if ($gare_code !== null && $gare_code !== '') {
            $where[] = 'ul.guser = ?';
            $binds[] = (string) $gare_code;
        }

        return $this->db->query(
            "SELECT DISTINCT ar.roleattribut,
                    cu.username,
                    u.first_name,
                    u.last_name,
                    ul.guser,
                    ar.userole
             FROM attributions_role ar
             JOIN user_login ul ON ul.uid_login = ar.idgestcompte
             JOIN compte_user cu ON cu.cpuser_id = ul.uid_usercpte
             JOIN utilisateurs u ON u.uid = cu.userlog_id
             JOIN entreprise e ON u.cle_comp = e.ekey
             WHERE " . implode(' AND ', $where) . "
             ORDER BY cu.username ASC, u.first_name ASC, u.last_name ASC",
            $binds
        )->result();
    }

    public function index($ckey)
    {
        $this->_require_admin();
        $this->company = $this->m_entreprises->get_key($ckey);
        if (!$this->company) {
            show_error('Entreprise introuvable.', 404);
            return;
        }

        $today = date('Y-m-d');
        $date_debut = trim((string) $this->input->get('date_debut'));
        $date_fin = trim((string) $this->input->get('date_fin'));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_debut)) {
            $date_debut = $today;
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_fin)) {
            $date_fin = $today;
        }

        // Sous-gare courante : GET prioritaire (0 = toutes), sinon dernière sous-gare mémorisée en session
        $getSg = $this->input->get('idsousgare');
        if ($getSg !== null && $getSg !== '') {
            $idsousgare = (int) $getSg;
            $this->session->set_userdata('rapport_arret_idsousgare', $idsousgare);
        } else {
            $sessSg = $this->session->userdata('rapport_arret_idsousgare');
            $idsousgare = ($sessSg !== null && $sessSg !== '') ? (int) $sessSg : 0;
        }

        $roleattribut = (int) $this->input->get('roleattribut');

        $sgRow = null;
        $gareFilter = null;
        if ($idsousgare > 0) {
            $sgRow = $this->db->query(
                "SELECT idsousgare, nomsousgare, gareprinceid FROM sousgare WHERE idsousgare = ? LIMIT 1",
                array($idsousgare)
            )->row();
            if ($sgRow) {
                $gareFilter = (string) $sgRow->gareprinceid;
            }
        }

        $where = array('aca.company_ekey = ?', 'DATE(aca.created_at) >= ?', 'DATE(aca.created_at) <= ?');
        $binds = array((int) $ckey, $date_debut, $date_fin);

        if ($sgRow) {
            $where[] = '(aca.idsousgare = ? OR (aca.idsousgare IS NULL AND aca.gare_code = ?))';
            $binds[] = (int) $sgRow->idsousgare;
            $binds[] = (string) $sgRow->gareprinceid;
        } elseif ($idsousgare > 0) {
            $where[] = 'aca.idsousgare = ?';
            $binds[] = $idsousgare;
        }
        if ($roleattribut > 0) {
            $where[] = 'aca.roleattribut = ?';
            $binds[] = $roleattribut;
        }

        $sql = "SELECT aca.*,
                       sg.nomsousgare,
                       MAX(cu.username) AS username,
                       MAX(u.first_name) AS first_name,
                       MAX(u.last_name) AS last_name,
                       MAX(ul.guser) AS guser
                FROM arret_compte_audit aca
                LEFT JOIN sousgare sg ON sg.idsousgare = aca.idsousgare
                LEFT JOIN attributions_role ar ON ar.roleattribut = aca.roleattribut
                LEFT JOIN user_login ul ON ul.uid_login = ar.idgestcompte
                LEFT JOIN compte_user cu ON cu.cpuser_id = ul.uid_usercpte
                LEFT JOIN utilisateurs u ON u.uid = cu.userlog_id
                WHERE " . implode(' AND ', $where) . "
                GROUP BY aca.id
                ORDER BY aca.created_at DESC, aca.id DESC
                LIMIT 500";

        $rows = $this->db->query($sql, $binds)->result();
        $compMap = $this->_compagnies_map();

        $totalMontant = 0.0;
        $totalPass = 0;
        $totalNp = 0;
        foreach ($rows as $r) {
            $totalMontant += (float) $r->total_amount;
            $totalPass += (int) $r->pass_count;
            $totalNp += (int) $r->np_count;
            $decoded = json_decode((string) $r->totals_json, true);
            $r->comp_details = array();
            if (is_array($decoded) && !empty($decoded['tickets']) && is_array($decoded['tickets'])) {
                foreach ($decoded['tickets'] as $cid => $m) {
                    $label = isset($compMap[(string) $cid]) ? $compMap[(string) $cid] : ('Comp. #' . $cid);
                    $r->comp_details[] = $label . ' : ' . number_format((float) $m, 0, ',', ' ') . ' F';
                }
            }
        }

        $this->property['ckey'] = $ckey;
        $this->property['date_debut'] = $date_debut;
        $this->property['date_fin'] = $date_fin;
        $this->property['idsousgare'] = $idsousgare;
        $this->property['current_sg'] = $sgRow;
        $this->property['roleattribut'] = $roleattribut;
        $this->property['sousgares'] = $this->_sousgares_list();
        $this->property['guichetiers'] = $this->_guichetiers_list($ckey, $gareFilter);
        $this->property['rows'] = $rows;
        $this->property['total_montant'] = $totalMontant;
        $this->property['total_pass'] = $totalPass;
        $this->property['total_np'] = $totalNp;

        return $this->layout->view('_param/rapport_arret_compte', $this->property);
    }

    public function voir($ckey, $id)
    {
        $this->_require_admin();
        $this->company = $this->m_entreprises->get_key($ckey);
        if (!$this->company) {
            show_error('Entreprise introuvable.', 404);
            return;
        }
        $row = $this->db->query(
            "SELECT aca.*,
                    sg.nomsousgare,
                    MAX(cu.username) AS username,
                    MAX(u.first_name) AS first_name,
                    MAX(u.last_name) AS last_name,
                    MAX(ul.guser) AS guser
             FROM arret_compte_audit aca
             LEFT JOIN sousgare sg ON sg.idsousgare = aca.idsousgare
             LEFT JOIN attributions_role ar ON ar.roleattribut = aca.roleattribut
             LEFT JOIN user_login ul ON ul.uid_login = ar.idgestcompte
             LEFT JOIN compte_user cu ON cu.cpuser_id = ul.uid_usercpte
             LEFT JOIN utilisateurs u ON u.uid = cu.userlog_id
             WHERE aca.id = ? AND aca.company_ekey = ?
             GROUP BY aca.id
             LIMIT 1",
            array((int) $id, (int) $ckey)
        )->row();

        if (!$row) {
            show_error('Arrêt de compte introuvable.', 404);
            return;
        }

        $decoded = json_decode((string) $row->totals_json, true);
        $compMap = $this->_compagnies_map();
        $ticketsByComp = array();
        if (is_array($decoded) && !empty($decoded['tickets']) && is_array($decoded['tickets'])) {
            foreach ($decoded['tickets'] as $cid => $m) {
                $ticketsByComp[] = array(
                    'compagnie' => isset($compMap[(string) $cid]) ? $compMap[(string) $cid] : ('Compagnie #' . $cid),
                    'montant' => (float) $m,
                );
            }
        }
        $bagagesByComp = array();
        if (is_array($decoded) && !empty($decoded['bagages']) && is_array($decoded['bagages'])) {
            foreach ($decoded['bagages'] as $cid => $m) {
                $bagagesByComp[] = array(
                    'compagnie' => isset($compMap[(string) $cid]) ? $compMap[(string) $cid] : ('Compagnie #' . $cid),
                    'montant' => (float) $m,
                );
            }
        }

        $this->property['ckey'] = $ckey;
        $this->property['arret'] = $row;
        $this->property['tickets_by_comp'] = $ticketsByComp;
        $this->property['bagages_by_comp'] = $bagagesByComp;
        $this->property['back_url'] = site_url('rapport_arret_compte/' . $ckey);
        $this->property['layout_print'] = TRUE;

        return $this->layout->view('_param/rapport_arret_compte_voir', $this->property);
    }
}
