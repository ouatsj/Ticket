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

    /**
     * Charge une escale active par id (source de vérité nom / code / prix).
     *
     * @param int $id_escale
     * @return object|null
     */
    public function escale_row_by_id($id_escale)
    {
        $id_escale = (int) $id_escale;
        if ($id_escale <= 0) {
            return null;
        }

        return $this->ci->db->query(
            "SELECT ie.id_escale, ie.id_lignes, ie.code_gadest, ie.nom_escale, ie.prix_escale,
                    ga.nom_gadest AS arrivee_escale
             FROM itineraire_escales ie
             LEFT JOIN gare_dest ga ON ga.code_gadest = ie.code_gadest
             WHERE ie.id_escale = ?
               AND ie.actif_escale = 1
             LIMIT 1",
            array($id_escale)
        )->row();
    }

    /**
     * Champs passager à persister pour une escale (validés en base).
     *
     * @param int $id_escale
     * @return array
     */
    public function escale_passager_fields($id_escale)
    {
        $row = $this->escale_row_by_id($id_escale);
        if (!$row) {
            return array();
        }

        $nom = trim((string) $row->nom_escale);
        if ($nom === '' && !empty($row->arrivee_escale)) {
            $nom = trim((string) $row->arrivee_escale);
        }

        $out = array(
            'id_escale_vente' => (int) $row->id_escale,
            'code_gadest_vente' => (string) $row->code_gadest,
            'nom_dest_vente' => $nom,
        );
        if ($row->prix_escale !== null && $row->prix_escale !== '') {
            $out['prixvente'] = round((float) $row->prix_escale, 2);
        }

        return $out;
    }

    /**
     * Code programme de la dernière jambe transit (POST idcheminheure*).
     *
     * @param int $nbr nombre de correspondances (2..4)
     * @param bool $isFid
     * @return string
     */
    public function escale_last_leg_code_pro($nbr, $isFid = false)
    {
        $nbr = (int) $nbr;
        $fieldMap = $isFid
            ? array(
                2 => 'idcheminheurefid',
                3 => 'idcheminheure1fid',
                4 => 'idcheminheure2fid',
            )
            : array(
                2 => 'idcheminheure',
                3 => 'idcheminheure1',
                4 => 'idcheminheure2',
            );
        if (!isset($fieldMap[$nbr])) {
            return '';
        }
        $raw = $this->ci->input->post($fieldMap[$nbr]);
        if ($raw === null || $raw === '') {
            return '';
        }
        $raw = (string) $raw;
        $pos = strpos($raw, '/');
        return $pos === false ? trim($raw) : trim(substr($raw, 0, $pos));
    }

    /**
     * Ligne (ident_ligne) d'un programme.
     *
     * @param string $code_pro
     * @return string
     */
    public function ligne_of_programme($code_pro)
    {
        $code_pro = trim((string) $code_pro);
        if ($code_pro === '') {
            return '';
        }
        $row = $this->ci->db->query(
            "SELECT lh.ligne_id
             FROM programme pr
             JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
             WHERE pr.code_progr = ?
             LIMIT 1",
            array($code_pro)
        )->row();
        return $row && !empty($row->ligne_id) ? (string) $row->ligne_id : '';
    }

    /**
     * Détermine le suffixe POST de l'escale à appliquer à ce passager
     * ('' = vente directe, '_tr2'..'_tr4' = dernière jambe transit).
     *
     * Matching transit : par code_pro de la dernière jambe (pas par prix —
     * sinon 1re et dernière jambe au même tarif reçoivent toutes deux l'escale).
     *
     * @param array $data données passager avant insert
     * @return string|null null = pas d'escale
     */
    public function escale_request_suffix_for_passager(array $data)
    {
        $input = $this->ci->input;
        if (!empty($data['id_escale_vente'])) {
            return '';
        }

        $nbr = (int) $input->post('nombretransite');
        $isFid = false;
        if ($nbr < 2) {
            $nbr = (int) $input->post('nombretransitefid');
            $isFid = ($nbr >= 2);
        }

        // Vente directe (ou sans multi-transit) : champs sans suffixe.
        if ($nbr < 2) {
            $id = $input->post('id_escale_vente');
            if ($id !== null && $id !== '' && (int) $id > 0) {
                return '';
            }
            return null;
        }

        // Transit : escale uniquement sur la dernière correspondance (trN).
        $suffix = '_tr' . $nbr;
        $idLast = $input->post('id_escale_vente' . $suffix);
        if ($idLast === null || $idLast === '' || (int) $idLast <= 0) {
            return null;
        }

        $codePro = isset($data['code_pro']) ? trim((string) $data['code_pro']) : '';
        $lastCode = $this->escale_last_leg_code_pro($nbr, $isFid);
        if ($codePro === '' || $lastCode === '' || $codePro !== $lastCode) {
            return null;
        }

        return $suffix;
    }

    /**
     * Enrichit $data avec id/code/nom/prix escale si la requête est une vente escale.
     * Escales : pas de quartier → quart forcé vide.
     *
     * @param array $data
     * @return array
     */
    public function enrich_passager_escale(array $data)
    {
        $suffix = $this->escale_request_suffix_for_passager($data);
        if ($suffix === null) {
            return $data;
        }

        $input = $this->ci->input;
        $id = !empty($data['id_escale_vente'])
            ? (int) $data['id_escale_vente']
            : (int) $input->post('id_escale_vente' . $suffix);

        $fields = $this->escale_passager_fields($id);
        if (empty($fields)) {
            // Repli POST si l'id n'est plus actif / introuvable.
            $code = $input->post('code_gadest_vente' . $suffix);
            $nom = $input->post('nom_dest_vente' . $suffix);
            if (($code === null || $code === '') && ($nom === null || $nom === '')) {
                return $data;
            }
            $data['id_escale_vente'] = $id > 0 ? $id : null;
            if ($code !== null && $code !== '') {
                $data['code_gadest_vente'] = $code;
            }
            if ($nom !== null && $nom !== '') {
                $data['nom_dest_vente'] = $nom;
            }
            // Pas de quartier sur une vente escale.
            $data['quart'] = null;
            return $data;
        }

        // Sécurité : l'escale doit appartenir à la ligne du programme de ce passager.
        if (!empty($data['code_pro'])) {
            $ligneProg = $this->ligne_of_programme($data['code_pro']);
            $rowEsc = $this->escale_row_by_id($fields['id_escale_vente']);
            if ($ligneProg !== '' && $rowEsc && (string) $rowEsc->id_lignes !== $ligneProg) {
                return $data;
            }
        }

        $data['id_escale_vente'] = $fields['id_escale_vente'];
        $data['code_gadest_vente'] = $fields['code_gadest_vente'];
        $data['nom_dest_vente'] = $fields['nom_dest_vente'];
        if (isset($fields['prixvente']) && array_key_exists('prixvente', $data)) {
            $data['prixvente'] = $fields['prixvente'];
        }
        // Escales sans quartier.
        $data['quart'] = null;

        return $data;
    }
}
