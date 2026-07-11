<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Logique partagée vente guichet (addpassager) — réduit duplication et requêtes SQL.
 */
class Sale_passager_service
{
    /** @var CI_Controller */
    protected $ci;

    /** @var array|null */
    protected static $daily_counts;

    public function __construct()
    {
        $this->ci =& get_instance();
    }

    /**
     * Libère les réservations expirées (ticket R) une fois par requête.
     *
     * @param string $today Y-m-d
     */
    public function release_expired_reservations($today)
    {
        static $done = false;
        if ($done) {
            return;
        }
        $done = true;

        $dte = date('H:i', time() + 3600);
        $result = $this->ci->db->query(
            "SELECT p.code_passager, p.code_ticket FROM passager p
            JOIN programme pr ON p.code_pro = pr.code_progr
            JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
            JOIN heures h ON lh.heure_identif = h.id_heure
            WHERE h.heure <= " . $this->ci->db->escape($dte) . "
            AND pr.date_progr = " . $this->ci->db->escape($today) . "
            AND p.code_ticket = 'R'"
        )->result();

        if (empty($result)) {
            return;
        }

        $plarray = array(
            'num_siege_categorie' => null,
            'prixvente' => null,
            'num_cat' => null,
        );

        foreach ($result as $rew) {
            $this->ci->m_passager->update($rew->code_passager, $rew->code_ticket, $plarray);
        }
    }

    /**
     * Compteurs journaliers passagers (mis en cache pour la durée de la requête).
     *
     * @param string $today
     * @param string|int $iduser
     * @return object {id}
     */
    public function daily_user_count($today, $iduser)
    {
        $key = $today . ':' . $iduser;
        if (self::$daily_counts === null) {
            self::$daily_counts = array();
        }
        if (!isset(self::$daily_counts[$key])) {
            self::$daily_counts[$key] = $this->ci->db->query(
                "SELECT COUNT(code_passager) AS id FROM passager p
                WHERE p.datep_create = " . $this->ci->db->escape($today) . "
                AND p.idcptuser = " . $this->ci->db->escape($iduser) . "
                AND p.code_ticket != 'R' AND p.statut_code = 'vendu'"
            )->row();
        }

        return self::$daily_counts[$key];
    }

    /**
     * @param string $today
     * @return object {id}
     */
    public function daily_global_count($today)
    {
        static $global = array();
        if (!isset($global[$today])) {
            $global[$today] = $this->ci->db->query(
                "SELECT COUNT(code_passager) AS id FROM passager p WHERE p.datep_create = " . $this->ci->db->escape($today)
            )->row();
        }

        return $global[$today];
    }

    /**
     * @param string $tampon
     * @param string $cdtick
     * @param string $cid ekey entreprise
     * @param string $cd compagnie dest
     * @param string $reg gare exp
     * @param string $today
     * @return object|null
     */
    public function last_verif_passager($cid, $cd, $reg, $today)
    {
        return $this->ci->db->query(
            "SELECT p.verifpassager FROM passager p
            JOIN programme pr ON p.code_pro = pr.code_progr
            JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
            JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
            JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
            JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
            JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
            JOIN entreprise e ON c.id_entrep = e.id_entreprise
            WHERE e.ekey = " . $this->ci->db->escape($cid) . "
            AND dest.id_compaga = " . $this->ci->db->escape($cd) . "
            AND ex.code_gaexp = " . $this->ci->db->escape($reg) . "
            AND p.datep_create = " . $this->ci->db->escape($today) . "
            ORDER BY date_emis DESC LIMIT 1"
        )->row();
    }

    /**
     * @param string $tampon
     * @param string $cdtick
     * @param object|null $dernier
     */
    public function apply_verif_passager($tampon, $cdtick, $dernier)
    {
        $letter = 'A';
        if ($dernier !== null && isset($dernier->verifpassager)) {
            $map = array('A' => 'B', 'B' => 'C', 'C' => 'D', 'D' => 'E');
            $prev = $dernier->verifpassager;
            $letter = isset($map[$prev]) ? $map[$prev] : 'A';
        }

        $this->ci->db->query(
            "UPDATE passager SET verifpassager = " . $this->ci->db->escape($letter) . "
            WHERE code_passager = " . $this->ci->db->escape($tampon) . "
            AND code_ticket = " . $this->ci->db->escape($cdtick) . "
            AND statut_code = 'vendu'"
        );
    }

    /**
     * @param string $tampo
     * @param string $tampon
     */
    public function create_tampon_codes($tampo, $tampon)
    {
        $this->ci->m_tamponcodetr->create(array('tamponcodtr' => $tampo));
        $this->ci->m_tamponcode->create(array(
            'tamponcod' => $tampon,
            'tamponcodtr' => $tampo,
        ));
    }

    /**
     * @param string $codepro
     * @param string $numsieg
     */
    public function release_tampon_siege($codepro, $numsieg)
    {
        $results = $this->ci->db->query(
            "SELECT * FROM tampon_siege t WHERE t.codepro = " . $this->ci->db->escape($codepro) . "
            AND t.numsieg = " . $this->ci->db->escape($numsieg)
        )->row();

        if ($results === null) {
            return;
        }

        $this->ci->m_tampon_siege->del($results->idtamp, array(
            'codepro' => $codepro,
            'numsieg' => $numsieg,
        ));
    }

    /**
     * @param string $iduser
     * @param string $usen
     * @param string $reg
     * @param object $passecompter
     * @param object $passecompt
     * @return array tampon, cdtick, tampo
     */
    public function generate_codes($iduser, $usen, $reg, $passecompter, $passecompt)
    {
        $tampon = mdate('%y%m%d', now('UTC')) . ($passecompter->id + 1) . $reg . $usen . $iduser;
        $cdtick = mdate('%m%d', now('UTC')) . ($passecompt->id + 1) . $usen . $iduser;
        $tampo = $iduser . $usen . $reg . ($passecompter->id + 1) . mdate('%y%m%d', now('UTC'));

        return array($tampon, $cdtick, $tampo);
    }
}
