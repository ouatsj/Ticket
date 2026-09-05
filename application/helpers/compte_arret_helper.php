<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Arrêt de compte vendeur — ticket, bagage, courrier.
 *
 * Règles :
 * - Vente libre si rien en suspens.
 * - Journée passée avec ventes non arrêtées → COMPTE seulement.
 * - Arrêt fait, validation en attente < 48 h → vente autorisée (grace).
 * - Arrêt fait, validation en attente > 48 h → blocage total.
 * - Dérogation admin ou rôles 1/2 exemptés.
 */

if (!function_exists('compte_arret_rules_enabled')) {
    /**
     * Interrupteur global — FALSE = tous les comptes libérés (pas de garde vente ni UI blocage).
     */
    function compte_arret_rules_enabled()
    {
        static $enabled = null;
        if ($enabled !== null) {
            return $enabled;
        }

        if (function_exists('get_instance')) {
            $CI =& get_instance();
            if (is_object($CI) && isset($CI->config)) {
                $CI->config->load('compte_arret', true, true);
                $val = $CI->config->item('compte_arret_enabled', 'compte_arret');
                if ($val !== null) {
                    $enabled = (bool) $val;
                    return $enabled;
                }
            }
        }

        $path = APPPATH . 'config/compte_arret.php';
        if (is_file($path)) {
            $config = [];
            include $path;
            if (isset($config['compte_arret_enabled'])) {
                $enabled = (bool) $config['compte_arret_enabled'];
                return $enabled;
            }
        }

        $enabled = true;
        return $enabled;
    }
}

if (!function_exists('compte_arret_inactivite_cron_enabled')) {
    function compte_arret_inactivite_cron_enabled()
    {
        return (bool) compte_arret_config_item('compte_arret_inactivite_cron', true);
    }
}

if (!function_exists('compte_arret_admin_roles')) {
    function compte_arret_admin_roles()
    {
        return ['1', '2'];
    }
}

if (!function_exists('compte_arret_vendeur_roles')) {
    function compte_arret_vendeur_roles()
    {
        return ['5', '6', '10', '12', '15', '16', '17'];
    }
}

if (!function_exists('compte_arret_role_vendeur')) {
    function compte_arret_role_vendeur($userole)
    {
        return in_array((string) $userole, compte_arret_vendeur_roles(), true);
    }
}

if (!function_exists('compte_arret_param_keys')) {
    /**
     * Clés éditables depuis Paramètres (valeur = type).
     *
     * @return array
     */
    function compte_arret_param_keys()
    {
        return array(
            'compte_arret_inactivite_cron' => 'bool',
            'compte_desactivation_jours' => 'int',
            'session_deconnexion_auto' => 'bool',
            'session_inactivite_minutes' => 'int',
            'chef_arret_obligatoire' => 'bool',
            'chef_arret_delai_heures' => 'int',
            'restriction_caissier_enabled' => 'bool',
            'restriction_caissier_gares' => 'list',
            'restriction_caissier_delai_jour' => 'int',
            'restriction_caissier_delai_par_gare' => 'map',
            'restriction_sup_agence_enabled' => 'bool',
            'restriction_sup_agence_gares' => 'list',
            'restriction_sup_agence_delai_jour' => 'int',
            'restriction_sup_agence_delai_par_gare' => 'map',
            'restriction_vendeur_enabled' => 'bool',
            'restriction_vendeur_gares' => 'list',
            'restriction_vendeur_delai_heures' => 'int',
            'restriction_vendeur_delai_par_gare' => 'map',
        );
    }
}

if (!function_exists('compte_arret_param_labels')) {
    function compte_arret_param_labels()
    {
        return array(
            'compte_arret_inactivite_cron' => 'Désactivation automatique des comptes inactifs',
            'compte_desactivation_jours' => 'Délai avant désactivation (jours)',
            'session_deconnexion_auto' => 'Déconnexion automatique des sessions',
            'session_inactivite_minutes' => 'Délai déconnexion session (minutes)',
            'chef_arret_obligatoire' => 'Blocage chef si arrêt non envoyé',
            'chef_arret_delai_heures' => 'Délai max chef avant blocage (heures)',
            'restriction_caissier_enabled' => 'Restriction ciblée caissiers (rôle 4)',
            'restriction_caissier_gares' => 'Gares concernées (caissiers)',
            'restriction_caissier_delai_jour' => 'Délai max caissier par défaut (jour du mois suivant)',
            'restriction_caissier_delai_par_gare' => 'Délais caissier par gare (jour)',
            'restriction_sup_agence_enabled' => 'Restriction ciblée superviseurs d\'agence (rôle 13)',
            'restriction_sup_agence_gares' => 'Gares concernées (superviseurs d\'agence)',
            'restriction_sup_agence_delai_jour' => 'Délai max superviseur agence par défaut (jour du mois suivant)',
            'restriction_sup_agence_delai_par_gare' => 'Délais superviseur agence par gare (jour)',
            'restriction_vendeur_enabled' => 'Restriction ciblée vendeurs (rôles 6/10/12/15/17)',
            'restriction_vendeur_gares' => 'Gares concernées (vendeurs)',
            'restriction_vendeur_delai_heures' => 'Délai max vendeur par défaut (heures après arrêt)',
            'restriction_vendeur_delai_par_gare' => 'Délais vendeur par gare (heures)',
        );
    }
}

if (!function_exists('compte_arret_param_ensure_table')) {
    /**
     * @param object|null $db CI_DB ou mysqli
     * @return bool
     */
    function compte_arret_param_ensure_table($db = null)
    {
        static $ensured = false;
        if ($ensured) {
            return true;
        }

        $sql = "CREATE TABLE IF NOT EXISTS param_compte_restriction (
            cle VARCHAR(64) NOT NULL,
            valeur TEXT NOT NULL,
            updated_at DATETIME NULL,
            updated_by INT NULL,
            PRIMARY KEY (cle)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        if ($db instanceof mysqli) {
            $ok = (bool) $db->query($sql);
            if ($ok) {
                $ensured = true;
            }

            return $ok;
        }
        if ($db && method_exists($db, 'query')) {
            $ok = (bool) $db->query($sql);
            if ($ok) {
                $ensured = true;
            }

            return $ok;
        }
        if (function_exists('get_instance')) {
            $CI =& get_instance();
            if (is_object($CI) && isset($CI->db)) {
                $ok = (bool) $CI->db->query($sql);
                if ($ok) {
                    $ensured = true;
                }

                return $ok;
            }
        }

        return false;
    }
}

if (!function_exists('compte_arret_param_mysqli_cli')) {
    /**
     * Connexion légère pour lire les params en CLI (cron).
     *
     * @return mysqli|null
     */
    function compte_arret_param_mysqli_cli()
    {
        static $m = null;
        static $tried = false;
        if ($tried) {
            return $m;
        }
        $tried = true;
        $path = (defined('APPPATH') ? APPPATH : dirname(__DIR__) . '/') . 'config/database.php';
        if (!is_file($path)) {
            return null;
        }
        $db = array();
        require $path;
        if (empty($db['default'])) {
            return null;
        }
        $c = $db['default'];
        $mysqli = @new mysqli(
            $c['hostname'],
            $c['username'],
            $c['password'],
            $c['database'],
            (int) (isset($c['port']) ? $c['port'] : 3306)
        );
        if ($mysqli->connect_error) {
            return null;
        }
        $m = $mysqli;

        return $m;
    }
}

if (!function_exists('compte_arret_param_load_db')) {
    /**
     * Charge tous les params DB (cache requête).
     *
     * @param bool $refresh
     * @return array
     */
    function compte_arret_param_load_db($refresh = false)
    {
        static $cache = null;
        if (!$refresh && $cache !== null) {
            return $cache;
        }
        $cache = array();

        $rows = array();
        if (function_exists('get_instance')) {
            $CI =& get_instance();
            if (is_object($CI) && isset($CI->db)) {
                compte_arret_param_ensure_table($CI->db);
                $q = $CI->db->query('SELECT cle, valeur FROM param_compte_restriction');
                if ($q) {
                    $rows = $q->result();
                }
            }
        }
        if (!$rows) {
            $m = compte_arret_param_mysqli_cli();
            if ($m instanceof mysqli) {
                compte_arret_param_ensure_table($m);
                $res = $m->query('SELECT cle, valeur FROM param_compte_restriction');
                if ($res) {
                    while ($r = $res->fetch_object()) {
                        $rows[] = $r;
                    }
                    $res->free();
                }
            }
        }

        foreach ($rows as $r) {
            $cache[(string) $r->cle] = (string) $r->valeur;
        }

        return $cache;
    }
}

if (!function_exists('compte_arret_param_cast')) {
    function compte_arret_param_cast($key, $raw)
    {
        $types = compte_arret_param_keys();
        $type = isset($types[$key]) ? $types[$key] : 'string';
        if ($type === 'bool') {
            return in_array((string) $raw, array('1', 'true', 'TRUE', 'on', 'yes'), true);
        }
        if ($type === 'int') {
            return (int) $raw;
        }
        if ($type === 'list') {
            if (is_array($raw)) {
                return array_values(array_filter(array_map('strval', $raw), 'strlen'));
            }
            $raw = trim((string) $raw);
            if ($raw === '') {
                return array();
            }
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                return array_values(array_filter(array_map('strval', $decoded), 'strlen'));
            }

            return array_values(array_filter(array_map('trim', explode(',', $raw)), 'strlen'));
        }
        if ($type === 'map') {
            if (is_array($raw)) {
                $out = array();
                foreach ($raw as $k => $v) {
                    $gk = preg_replace('/[^A-Za-z0-9_-]/', '', (string) $k);
                    if ($gk === '') {
                        continue;
                    }
                    $out[$gk] = (int) $v;
                }

                return $out;
            }
            $raw = trim((string) $raw);
            if ($raw === '') {
                return array();
            }
            $decoded = json_decode($raw, true);
            if (!is_array($decoded)) {
                return array();
            }
            $out = array();
            foreach ($decoded as $k => $v) {
                $gk = preg_replace('/[^A-Za-z0-9_-]/', '', (string) $k);
                if ($gk === '') {
                    continue;
                }
                $out[$gk] = (int) $v;
            }

            return $out;
        }

        return $raw;
    }
}

if (!function_exists('compte_arret_param_save')) {
    /**
     * Enregistre un paramètre (UI Paramètres).
     *
     * @param object $db CI_DB
     * @param string $key
     * @param mixed $value
     * @param int|null $user_id
     * @return bool
     */
    function compte_arret_param_save($db, $key, $value, $user_id = null)
    {
        $keys = compte_arret_param_keys();
        if (!isset($keys[$key])) {
            return false;
        }
        compte_arret_param_ensure_table($db);

        if ($keys[$key] === 'bool') {
            $store = $value ? '1' : '0';
        } elseif ($keys[$key] === 'int') {
            $store = (string) max(0, (int) $value);
        } elseif ($keys[$key] === 'list') {
            $list = is_array($value) ? $value : compte_arret_param_cast($key, $value);
            $clean = array();
            foreach ($list as $item) {
                $item = preg_replace('/[^A-Za-z0-9_-]/', '', (string) $item);
                if ($item !== '') {
                    $clean[$item] = $item;
                }
            }
            $store = json_encode(array_values($clean));
        } elseif ($keys[$key] === 'map') {
            $map = is_array($value) ? $value : compte_arret_param_cast($key, $value);
            $clean = array();
            foreach ($map as $gk => $gv) {
                $gk = preg_replace('/[^A-Za-z0-9_-]/', '', (string) $gk);
                if ($gk === '') {
                    continue;
                }
                $clean[$gk] = (int) $gv;
            }
            $store = json_encode($clean);
        } else {
            $store = (string) $value;
        }

        $now = date('Y-m-d H:i:s');
        $uid = $user_id !== null ? (int) $user_id : null;
        $exists = $db->query(
            'SELECT cle FROM param_compte_restriction WHERE cle = ? LIMIT 1',
            array($key)
        )->row();
        if ($exists) {
            $db->where('cle', $key)->update('param_compte_restriction', array(
                'valeur' => $store,
                'updated_at' => $now,
                'updated_by' => $uid,
            ));
        } else {
            $db->insert('param_compte_restriction', array(
                'cle' => $key,
                'valeur' => $store,
                'updated_at' => $now,
                'updated_by' => $uid,
            ));
        }
        compte_arret_param_load_db(true);

        return true;
    }
}

if (!function_exists('compte_arret_param_get_all_effective')) {
    /**
     * Valeurs effectives (DB si présente, sinon fichier config).
     *
     * @return array
     */
    function compte_arret_param_get_all_effective()
    {
        $out = array();
        $defaults = array(
            'compte_arret_inactivite_cron' => true,
            'compte_desactivation_jours' => 5,
            'session_deconnexion_auto' => true,
            'session_inactivite_minutes' => 30,
            'chef_arret_obligatoire' => true,
            'chef_arret_delai_heures' => 36,
            'restriction_caissier_enabled' => false,
            'restriction_caissier_gares' => array(),
            'restriction_caissier_delai_jour' => 10,
            'restriction_caissier_delai_par_gare' => array(),
            'restriction_sup_agence_enabled' => false,
            'restriction_sup_agence_gares' => array(),
            'restriction_sup_agence_delai_jour' => 20,
            'restriction_sup_agence_delai_par_gare' => array(),
            'restriction_vendeur_enabled' => false,
            'restriction_vendeur_gares' => array(),
            'restriction_vendeur_delai_heures' => 48,
            'restriction_vendeur_delai_par_gare' => array(),
        );
        foreach (compte_arret_param_keys() as $key => $type) {
            $out[$key] = compte_arret_config_item($key, $defaults[$key]);
        }

        return $out;
    }
}

if (!function_exists('compte_arret_config_item')) {
    /**
     * Lit une clé : d’abord param_compte_restriction (Paramètres),
     * sinon config/compte_arret.php.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function compte_arret_config_item($key, $default = null)
    {
        $db_params = compte_arret_param_load_db();
        if (array_key_exists($key, $db_params)) {
            return compte_arret_param_cast($key, $db_params[$key]);
        }

        if (function_exists('get_instance')) {
            $CI =& get_instance();
            if (is_object($CI) && isset($CI->config)) {
                $CI->config->load('compte_arret', true, true);
                $val = $CI->config->item($key, 'compte_arret');
                if ($val !== null) {
                    return $val;
                }
            }
        }

        $path = (defined('APPPATH') ? APPPATH : dirname(__DIR__) . '/') . 'config/compte_arret.php';
        if (is_file($path)) {
            $config = [];
            include $path;
            if (isset($config[$key])) {
                return $config[$key];
            }
        }

        return $default;
    }
}

if (!function_exists('compte_arret_restriction_role_map')) {
    /**
     * Rôles pouvant être restreints par gare (désactivation ciblée + délais).
     *
     * @return array
     */
    function compte_arret_restriction_role_map()
    {
        $vendeur = array(
            'enabled' => 'restriction_vendeur_enabled',
            'gares' => 'restriction_vendeur_gares',
            'delai' => 'restriction_vendeur_delai_heures',
            'delai_par_gare' => 'restriction_vendeur_delai_par_gare',
            'delai_default' => 48,
            'delai_unit' => 'hours',
            'label' => 'vendeur',
        );

        return array(
            4 => array(
                'enabled' => 'restriction_caissier_enabled',
                'gares' => 'restriction_caissier_gares',
                'delai' => 'restriction_caissier_delai_jour',
                'delai_par_gare' => 'restriction_caissier_delai_par_gare',
                'delai_default' => 10,
                'delai_unit' => 'day',
                'label' => 'caissier',
            ),
            13 => array(
                'enabled' => 'restriction_sup_agence_enabled',
                'gares' => 'restriction_sup_agence_gares',
                'delai' => 'restriction_sup_agence_delai_jour',
                'delai_par_gare' => 'restriction_sup_agence_delai_par_gare',
                'delai_default' => 20,
                'delai_unit' => 'day',
                'label' => 'superviseur d\'agence',
            ),
            6 => $vendeur,
            10 => $vendeur,
            12 => $vendeur,
            15 => $vendeur,
            17 => $vendeur,
        );
    }
}

if (!function_exists('compte_arret_restriction_gares_for_role')) {
    /**
     * @param int|string $userole
     * @return array|null null = pas de mode ciblé (suit le cron global)
     */
    function compte_arret_restriction_gares_for_role($userole)
    {
        $map = compte_arret_restriction_role_map();
        $role = (int) $userole;
        if (!isset($map[$role])) {
            return null;
        }
        $cfg = $map[$role];
        if (!(bool) compte_arret_config_item($cfg['enabled'], false)) {
            return null;
        }
        $gares = compte_arret_config_item($cfg['gares'], array());
        if (!is_array($gares)) {
            $gares = compte_arret_param_cast($cfg['gares'], $gares);
        }

        return array_values($gares);
    }
}

if (!function_exists('compte_arret_restriction_scoped_roles')) {
    /**
     * @return int[]
     */
    function compte_arret_restriction_scoped_roles()
    {
        $out = array();
        foreach (compte_arret_restriction_role_map() as $role => $cfg) {
            if ((bool) compte_arret_config_item($cfg['enabled'], false)) {
                $out[] = (int) $role;
            }
        }

        return $out;
    }
}

if (!function_exists('compte_arret_restriction_gare_selected')) {
    /**
     * Liste vide = toutes les gares. Une gare cochée couvre toutes ses sous-gares.
     *
     * @param array $gares
     * @param string $gare_id
     * @return bool
     */
    function compte_arret_restriction_gare_selected(array $gares, $gare_id)
    {
        $gare_id = trim((string) $gare_id);
        if ($gare_id === '') {
            return false;
        }
        if (empty($gares)) {
            return true;
        }

        return in_array($gare_id, $gares, true);
    }
}

if (!function_exists('compte_arret_restriction_delai_for_gare')) {
    /**
     * Délai effectif pour un rôle + gare (override map, sinon défaut rôle).
     *
     * @param int|string $userole
     * @param string|null $gare_id
     * @return int
     */
    function compte_arret_restriction_delai_for_gare($userole, $gare_id = null)
    {
        $map = compte_arret_restriction_role_map();
        $role = (int) $userole;
        if (!isset($map[$role])) {
            return 0;
        }
        $cfg = $map[$role];
        $unit = isset($cfg['delai_unit']) ? $cfg['delai_unit'] : 'day';
        $max = ($unit === 'hours') ? 168 : 28;
        $val = (int) compte_arret_config_item($cfg['delai'], $cfg['delai_default']);

        $gare_id = trim((string) $gare_id);
        if ($gare_id !== '' && !empty($cfg['delai_par_gare'])) {
            $by_gare = compte_arret_config_item($cfg['delai_par_gare'], array());
            if (!is_array($by_gare)) {
                $by_gare = compte_arret_param_cast($cfg['delai_par_gare'], $by_gare);
            }
            if (isset($by_gare[$gare_id]) && (int) $by_gare[$gare_id] > 0) {
                $val = (int) $by_gare[$gare_id];
            }
        }

        if ($val < 1) {
            $val = (int) $cfg['delai_default'];
        }
        if ($val > $max) {
            $val = $max;
        }

        return $val;
    }
}

if (!function_exists('compte_arret_restriction_delai_jour')) {
    /**
     * Jour du mois suivant limite (1–28). Défaut : 10 caissier, 20 superviseur agence.
     * Si $gare_id fourni, utilise le délai spécifique à la gare le cas échéant.
     *
     * @param int|string $userole
     * @param string|null $gare_id
     * @return int
     */
    function compte_arret_restriction_delai_jour($userole, $gare_id = null)
    {
        $map = compte_arret_restriction_role_map();
        $role = (int) $userole;
        if (!isset($map[$role]) || (isset($map[$role]['delai_unit']) && $map[$role]['delai_unit'] === 'hours')) {
            return 0;
        }

        return compte_arret_restriction_delai_for_gare($userole, $gare_id);
    }
}

if (!function_exists('compte_arret_restriction_delai_heures')) {
    /**
     * Délai vendeur en heures (défaut 48, override par gare).
     *
     * @param int|string $userole
     * @param string|null $gare_id
     * @return int
     */
    function compte_arret_restriction_delai_heures($userole, $gare_id = null)
    {
        $map = compte_arret_restriction_role_map();
        $role = (int) $userole;
        if (!isset($map[$role]) || empty($map[$role]['delai_unit']) || $map[$role]['delai_unit'] !== 'hours') {
            return 48;
        }

        return compte_arret_restriction_delai_for_gare($userole, $gare_id);
    }
}

if (!function_exists('compte_arret_vendeur_restriction_applies')) {
    /**
     * Restriction vendeur active pour ce rôle + gare.
     *
     * @param int|string $userole
     * @param string|null $gare_id
     * @return bool
     */
    function compte_arret_vendeur_restriction_applies($userole, $gare_id = null)
    {
        if (!function_exists('validerecette_is_vendeur_userole')
            || !validerecette_is_vendeur_userole($userole)) {
            return false;
        }
        if (!(bool) compte_arret_config_item('restriction_vendeur_enabled', false)) {
            return false;
        }
        $gares = compte_arret_restriction_gares_for_role($userole);
        if ($gares === null) {
            return false;
        }

        return compte_arret_restriction_gare_selected($gares, trim((string) $gare_id));
    }
}

if (!function_exists('compte_arret_month_deadline_ts')) {
    /**
     * Échéance = jour N du mois suivant le mois d'opération (fin de journée UTC).
     *
     * @param string $ym YYYY-MM
     * @param int $day
     * @return int unix timestamp
     */
    function compte_arret_month_deadline_ts($ym, $day)
    {
        $ym = preg_replace('/[^0-9\-]/', '', (string) $ym);
        if (!preg_match('/^\d{4}-\d{2}$/', $ym)) {
            return 0;
        }
        $day = max(1, min(28, (int) $day));
        try {
            $dt = new DateTime($ym . '-01 00:00:00', new DateTimeZone('UTC'));
            $dt->modify('first day of next month');
            $max = (int) $dt->format('t');
            $dt->setDate((int) $dt->format('Y'), (int) $dt->format('m'), min($day, $max));
            $dt->setTime(23, 59, 59);

            return (int) $dt->format('U');
        } catch (Exception $e) {
            return 0;
        }
    }
}

if (!function_exists('compte_arret_caissier_pending_status')) {
    /**
     * Caissier : opérations validées non arrêtées (ferme_cais*=0) dont l'échéance
     * (jour N du mois suivant) est dépassée.
     *
     * @return array{blocked:bool,reason:string,code:string,mois:string}
     */
    function compte_arret_caissier_pending_status($userole, $roleattribut, $gare_id)
    {
        $open = array(
            'blocked' => false,
            'reason' => '',
            'code' => 'ok',
            'mois' => '',
        );

        if ((string) $userole !== '4'
            || !(bool) compte_arret_config_item('restriction_caissier_enabled', false)) {
            return $open;
        }

        $gares = compte_arret_restriction_gares_for_role(4);
        if ($gares === null) {
            return $open;
        }
        $gare_id = trim((string) $gare_id);
        if (!compte_arret_restriction_gare_selected($gares, $gare_id)) {
            return $open;
        }

        $CI =& get_instance();
        $roleattribut = (int) $roleattribut;
        if ($roleattribut <= 0 || $gare_id === '') {
            return $open;
        }

        $day = compte_arret_restriction_delai_jour(4, $gare_id);
        $row = $CI->db->query(
            "SELECT mois, nb FROM (
                SELECT DATE_FORMAT(r.date_recet, '%Y-%m') AS mois, COUNT(*) AS nb
                FROM recette r
                JOIN caisse cs ON r.idcaisse = cs.id_caiss
                WHERE r.operavalid = ?
                AND cs.gexp_caiss = ?
                AND r.is_validerecet = 1 AND r.is_actifrecet = 1
                AND r.ferme_caisrecet = 0
                AND IFNULL(r.type_recet,'') <> 'Courrier'
                GROUP BY DATE_FORMAT(r.date_recet, '%Y-%m')
                UNION ALL
                SELECT DATE_FORMAT(d.date_depens, '%Y-%m'), COUNT(*)
                FROM depense d
                JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                WHERE d.opevalid = ?
                AND cs.gexp_caiss = ?
                AND d.is_validedep = 1 AND d.is_actifdep = 1
                AND d.ferme_caisdep = 0
                AND IFNULL(d.type_depense,'') <> 'Courrier'
                GROUP BY DATE_FORMAT(d.date_depens, '%Y-%m')
                UNION ALL
                SELECT DATE_FORMAT(dp.datedepot, '%Y-%m'), COUNT(*)
                FROM depot dp
                JOIN caisse cs ON dp.idcaisse_depot = cs.id_caiss
                WHERE dp.opvalid = ?
                AND cs.gexp_caiss = ?
                AND dp.is_validdepo = 1 AND dp.is_actifdepo = 1
                AND dp.ferme_caisdepo = 0
                AND IFNULL(dp.type_depot,'') <> 'Courrier'
                GROUP BY DATE_FORMAT(dp.datedepot, '%Y-%m')
                UNION ALL
                SELECT DATE_FORMAT(v.date_versement, '%Y-%m'), COUNT(*)
                FROM versements v
                JOIN caisse cs ON v.idcaisse_versement = cs.id_caiss
                WHERE v.validop = ?
                AND cs.gexp_caiss = ?
                AND v.ferme_caisvers = 0
                GROUP BY DATE_FORMAT(v.date_versement, '%Y-%m')
            ) x
            ORDER BY mois ASC",
            array(
                $roleattribut, $gare_id,
                $roleattribut, $gare_id,
                $roleattribut, $gare_id,
                $roleattribut, $gare_id,
            )
        )->result();

        $now = time();
        foreach ($row as $r) {
            $mois = (string) $r->mois;
            $deadline = compte_arret_month_deadline_ts($mois, $day);
            if ($deadline > 0 && $now > $deadline && (int) $r->nb > 0) {
                $parts = explode('-', $mois);
                $label = (isset($parts[1], $parts[0]) ? ($parts[1] . '/' . $parts[0]) : $mois);

                return array(
                    'blocked' => true,
                    'reason' => 'Arrêt de caisse en retard : régularisez le mois '
                        . $label . ' avant le ' . $day . ' du mois suivant (échéance dépassée).',
                    'code' => 'caissier_arret_overdue',
                    'mois' => $mois,
                );
            }
        }

        return $open;
    }
}

if (!function_exists('compte_arret_sup_agence_pending_status')) {
    /**
     * Superviseur d'agence : éléments arrêtés par le caissier (ferme=1)
     * non validés comptable (valid_cptable*=0) hors délai (jour N du mois suivant).
     *
     * @return array{blocked:bool,reason:string,code:string,mois:string}
     */
    function compte_arret_sup_agence_pending_status($userole, $roleattribut, $gare_id)
    {
        $open = array(
            'blocked' => false,
            'reason' => '',
            'code' => 'ok',
            'mois' => '',
        );

        if ((string) $userole !== '13'
            || !(bool) compte_arret_config_item('restriction_sup_agence_enabled', false)) {
            return $open;
        }

        $gares = compte_arret_restriction_gares_for_role(13);
        if ($gares === null) {
            return $open;
        }
        $gare_id = trim((string) $gare_id);
        if (!compte_arret_restriction_gare_selected($gares, $gare_id)) {
            return $open;
        }

        $CI =& get_instance();
        if ($gare_id === '') {
            return $open;
        }

        $day = compte_arret_restriction_delai_jour(13, $gare_id);
        $row = $CI->db->query(
            "SELECT mois, nb FROM (
                SELECT DATE_FORMAT(r.date_recet, '%Y-%m') AS mois, COUNT(*) AS nb
                FROM recette r
                JOIN caisse cs ON r.idcaisse = cs.id_caiss
                WHERE cs.gexp_caiss = ?
                AND r.ferme_caisrecet = 1 AND IFNULL(r.valid_cptablerecet, 0) = 0
                AND r.is_validerecet = 1 AND r.is_actifrecet = 1
                AND IFNULL(r.type_recet,'') <> 'Courrier'
                GROUP BY DATE_FORMAT(r.date_recet, '%Y-%m')
                UNION ALL
                SELECT DATE_FORMAT(d.date_depens, '%Y-%m'), COUNT(*)
                FROM depense d
                JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                WHERE cs.gexp_caiss = ?
                AND d.ferme_caisdep = 1 AND IFNULL(d.validcptabledep, 0) = 0
                AND d.is_validedep = 1 AND d.is_actifdep = 1
                AND IFNULL(d.type_depense,'') <> 'Courrier'
                GROUP BY DATE_FORMAT(d.date_depens, '%Y-%m')
                UNION ALL
                SELECT DATE_FORMAT(dp.datedepot, '%Y-%m'), COUNT(*)
                FROM depot dp
                JOIN caisse cs ON dp.idcaisse_depot = cs.id_caiss
                WHERE cs.gexp_caiss = ?
                AND dp.ferme_caisdepo = 1 AND IFNULL(dp.valid_cptabledepo, 0) = 0
                AND dp.is_validdepo = 1 AND dp.is_actifdepo = 1
                AND IFNULL(dp.type_depot,'') <> 'Courrier'
                GROUP BY DATE_FORMAT(dp.datedepot, '%Y-%m')
                UNION ALL
                SELECT DATE_FORMAT(v.date_versement, '%Y-%m'), COUNT(*)
                FROM versements v
                JOIN caisse cs ON v.idcaisse_versement = cs.id_caiss
                WHERE cs.gexp_caiss = ?
                AND v.ferme_caisvers = 1 AND IFNULL(v.valid_cptablevers, 0) = 0
                GROUP BY DATE_FORMAT(v.date_versement, '%Y-%m')
            ) x
            ORDER BY mois ASC",
            array($gare_id, $gare_id, $gare_id, $gare_id)
        )->result();

        $now = time();
        foreach ($row as $r) {
            $mois = (string) $r->mois;
            $deadline = compte_arret_month_deadline_ts($mois, $day);
            if ($deadline > 0 && $now > $deadline && (int) $r->nb > 0) {
                $parts = explode('-', $mois);
                $label = (isset($parts[1], $parts[0]) ? ($parts[1] . '/' . $parts[0]) : $mois);

                return array(
                    'blocked' => true,
                    'reason' => 'Validations caissier en retard : traitez le mois '
                        . $label . ' avant le ' . $day . ' du mois suivant (échéance dépassée).',
                    'code' => 'sup_agence_validation_overdue',
                    'mois' => $mois,
                );
            }
        }

        return $open;
    }
}

if (!function_exists('compte_arret_hours_limit')) {
    /**
     * Délai grâce / expiration arrêt vendeur (heures).
     * Si restriction vendeur active pour la gare : délai paramétré (éventuellement par gare).
     * Sinon : jours de désactivation × 24 (comportement historique).
     *
     * @param string|null $gare_id
     * @param int|string|null $userole
     * @return int
     * @deprecated Sans gare/rôle, préfère compte_arret_desactivation_jours() pour le cron.
     */
    function compte_arret_hours_limit($gare_id = null, $userole = null)
    {
        if ($gare_id !== null && $userole !== null
            && function_exists('compte_arret_vendeur_restriction_applies')
            && compte_arret_vendeur_restriction_applies($userole, $gare_id)) {
            return compte_arret_restriction_delai_heures($userole, $gare_id);
        }

        return (int) compte_arret_desactivation_jours() * 24;
    }
}

if (!function_exists('compte_arret_desactivation_jours')) {
    function compte_arret_desactivation_jours()
    {
        $jours = (int) compte_arret_config_item('compte_desactivation_jours', 3);
        return $jours > 0 ? $jours : 3;
    }
}

if (!function_exists('compte_arret_session_deconnexion_enabled')) {
    function compte_arret_session_deconnexion_enabled()
    {
        return (bool) compte_arret_config_item('session_deconnexion_auto', true);
    }
}

if (!function_exists('compte_arret_session_idle_minutes')) {
    function compte_arret_session_idle_minutes()
    {
        $m = (int) compte_arret_config_item('session_inactivite_minutes', 30);
        return $m > 0 ? $m : 30;
    }
}

if (!function_exists('compte_arret_get_cpuser')) {
    function compte_arret_get_cpuser($cpuser_id)
    {
        $CI =& get_instance();
        $cpuser_id = (int) $cpuser_id;
        if ($cpuser_id <= 0) {
            return null;
        }

        return $CI->db->query(
            'SELECT cpuser_id, activer, autorisation_vente_forcee, autorisation_vente_jusquau,
                autorisation_vente_motif, exempt_desactivation_auto, derniere_activite_at,
                desactivation_motif, desactivation_at
            FROM compte_user WHERE cpuser_id = ? LIMIT 1',
            [$cpuser_id]
        )->row();
    }
}

if (!function_exists('compte_arret_has_admin_override')) {
    function compte_arret_has_admin_override($cpuser_id)
    {
        $row = compte_arret_get_cpuser($cpuser_id);
        if (!$row || (int) $row->autorisation_vente_forcee !== 1) {
            return false;
        }
        if (empty($row->autorisation_vente_jusquau)) {
            return false;
        }

        return strtotime($row->autorisation_vente_jusquau) >= time();
    }
}

if (!function_exists('compte_arret_track_activity')) {
    /**
     * Met à jour derniere_activite_at (throttle ~60 s pour éviter un UPDATE à chaque requête).
     *
     * @param int $cpuser_id
     * @param string|null $known_last Si déjà lu en DB, évite un SELECT
     */
    function compte_arret_track_activity($cpuser_id, $known_last = null)
    {
        $CI =& get_instance();
        $cpuser_id = (int) $cpuser_id;
        if ($cpuser_id <= 0) {
            return;
        }

        if ($known_last !== null && $known_last !== '') {
            $last_ts = strtotime((string) $known_last);
            if ($last_ts && (time() - $last_ts) < 60) {
                return;
            }
        }

        $now = mdate('%Y-%m-%d %H:%i:%s', now('UTC'));
        if ($known_last === null) {
            $CI->db->where('cpuser_id', $cpuser_id)
                ->group_start()
                    ->where('derniere_activite_at IS NULL', null, false)
                    ->or_where('derniere_activite_at <', date('Y-m-d H:i:s', time() - 60))
                ->group_end()
                ->update('compte_user', array('derniere_activite_at' => $now));
            return;
        }

        $CI->db->where('cpuser_id', $cpuser_id)->update('compte_user', array(
            'derniere_activite_at' => $now,
        ));
    }
}

if (!function_exists('compte_arret_unclosed_ticket')) {
    function compte_arret_unclosed_ticket($roleattribut, $gare_id = null)
    {
        $CI =& get_instance();
        $roleattribut = (int) $roleattribut;
        $today = mdate('%Y-%m-%d', now('UTC'));

        $sql = "SELECT 1 FROM passager p
            WHERE p.idcptuser = ?
            AND p.datep_create < ?
            AND p.statutvente = 0
            AND p.prixvente IS NOT NULL
            AND p.statut_code = 'vendu'
            LIMIT 1";
        $params = [$roleattribut, $today];

        if ($gare_id !== null && $gare_id !== '') {
            $sql = "SELECT 1 FROM passager p
                JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                WHERE p.idcptuser = ?
                AND ul.guser = ?
                AND p.datep_create < ?
                AND p.statutvente = 0
                AND p.prixvente IS NOT NULL
                AND p.statut_code = 'vendu'
                LIMIT 1";
            $params = [$roleattribut, $gare_id, $today];
        }

        if ($CI->db->query($sql, $params)->row()) {
            return true;
        }

        // Aligné phase A : retours au même scope gare + vendeur (pas de filtre sous-gare).
        if ($gare_id !== null && $gare_id !== '') {
            $sql_np = "SELECT 1 FROM non_passager np
                JOIN attributions_role ar ON np.cptus = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                WHERE np.cptus = ?
                AND ul.guser = ?
                AND np.datevente < ?
                AND np.statvente = 0
                LIMIT 1";
            return (bool) $CI->db->query($sql_np, [$roleattribut, $gare_id, $today])->row();
        }

        $sql_np = "SELECT 1 FROM non_passager np
            WHERE np.cptus = ?
            AND np.datevente < ?
            AND np.statvente = 0
            LIMIT 1";

        return (bool) $CI->db->query($sql_np, [$roleattribut, $today])->row();
    }
}

if (!function_exists('compte_arret_unclosed_bagage')) {
    function compte_arret_unclosed_bagage($roleattribut, $gare_id = null)
    {
        $CI =& get_instance();
        $today = mdate('%Y-%m-%d', now('UTC'));

        return (bool) $CI->db->query(
            "SELECT 1 FROM bagages b
            WHERE b.idoperabagage = ?
            AND b.date_create < ?
            AND b.isvalidbag = 0
            AND b.annulebag = 0
            AND b.prix_bagage IS NOT NULL
            LIMIT 1",
            [(int) $roleattribut, $today]
        )->row();
    }
}

if (!function_exists('compte_arret_unclosed_courrier')) {
    function compte_arret_unclosed_courrier($roleattribut, $gare_id = null)
    {
        $CI =& get_instance();
        $today = mdate('%Y-%m-%d', now('UTC'));

        return (bool) $CI->db->query(
            "SELECT 1 FROM courriers_expesc e
            WHERE e.idoperateuresc = ?
            AND e.dateenvoiesc < ?
            AND e.statutcouresc = 0
            AND e.actif_couresc = 0
            LIMIT 1",
            [(int) $roleattribut, $today]
        )->row();
    }
}

if (!function_exists('compte_arret_pending_ticket')) {
    /** @return object|null row with expired flag */
    function compte_arret_pending_ticket($roleattribut, $hours = null)
    {
        $CI =& get_instance();
        $hours = $hours !== null ? max(1, (int) $hours) : (int) compte_arret_hours_limit();

        return $CI->db->query(
            "SELECT cg.idcpguichet, cg.lastcptg_update,
                (cg.lastcptg_update < DATE_SUB(NOW(), INTERVAL {$hours} HOUR)) AS expired
            FROM compte_guichet cg
            WHERE cg.idusercompt = ?
            AND cg.is_validcompte = 0
            AND cg.actifcompt = 0
            ORDER BY cg.lastcptg_update DESC
            LIMIT 1",
            [(int) $roleattribut]
        )->row();
    }
}

if (!function_exists('compte_arret_pending_bagage')) {
    function compte_arret_pending_bagage($roleattribut, $hours = null)
    {
        $CI =& get_instance();
        $hours = $hours !== null ? max(1, (int) $hours) : (int) compte_arret_hours_limit();

        return $CI->db->query(
            "SELECT cb.idcpguichetbg, cb.lastcptg_updatebg AS lastcptg_update,
                (cb.lastcptg_updatebg < DATE_SUB(NOW(), INTERVAL {$hours} HOUR)) AS expired
            FROM compte_bagage cb
            WHERE cb.idusercomptbg = ?
            AND cb.is_validcomptebg = 0
            AND cb.actifcomptbg = 0
            ORDER BY cb.lastcptg_updatebg DESC
            LIMIT 1",
            [(int) $roleattribut]
        )->row();
    }
}

if (!function_exists('compte_arret_pending_courrier')) {
    function compte_arret_pending_courrier($roleattribut, $hours = null)
    {
        $CI =& get_instance();
        $hours = $hours !== null ? max(1, (int) $hours) : (int) compte_arret_hours_limit();

        return $CI->db->query(
            "SELECT cc.idcpcourrier, cc.update_lastcptg AS lastcptg_update,
                (cc.update_lastcptg < DATE_SUB(NOW(), INTERVAL {$hours} HOUR)) AS expired
            FROM compte_courrier cc
            WHERE cc.comptiduser = ?
            AND cc.validcompteis = 0
            AND cc.compteactif = 0
            ORDER BY cc.update_lastcptg DESC
            LIMIT 1",
            [(int) $roleattribut]
        )->row();
    }
}

if (!function_exists('compte_arret_activite_label')) {
    function compte_arret_activite_label($code)
    {
        $labels = [
            'ticket' => 'ticket',
            'bagage' => 'bagage',
            'courrier' => 'courrier',
        ];

        return isset($labels[$code]) ? $labels[$code] : $code;
    }
}

if (!function_exists('compte_arret_chef_roles')) {
    function compte_arret_chef_roles()
    {
        return array('5', '16');
    }
}

if (!function_exists('compte_arret_chef_enabled')) {
    function compte_arret_chef_enabled()
    {
        return (bool) compte_arret_config_item('chef_arret_obligatoire', true);
    }
}

if (!function_exists('compte_arret_chef_hours_limit')) {
    function compte_arret_chef_hours_limit()
    {
        $hours = (int) compte_arret_config_item('chef_arret_delai_heures', 36);
        return $hours > 0 ? $hours : 36;
    }
}

if (!function_exists('compte_arret_chef_pending_status')) {
    /**
     * Recherche la plus ancienne opération du chef pas encore envoyée au caissier.
     *
     * @return array{blocked:bool,only_compte:bool,grace:bool,reason:string,code:string,warnings:array}
     */
    function compte_arret_chef_pending_status($userole, $roleattribut, $gare_id)
    {
        $open = array(
            'blocked' => false,
            'only_compte' => false,
            'grace' => false,
            'reason' => '',
            'code' => 'ok',
            'warnings' => array(),
        );

        if (!compte_arret_chef_enabled()
            || !in_array((string) $userole, compte_arret_chef_roles(), true)) {
            return $open;
        }

        $CI =& get_instance();
        $roleattribut = (int) $roleattribut;
        $gare_id = trim((string) $gare_id);
        if ($roleattribut <= 0 || $gare_id === '') {
            return $open;
        }

        $pending_q = $CI->db->query(
            "SELECT MIN(pending_date) AS oldest_date, COALESCE(SUM(nb), 0) AS pending_count
            FROM (
                SELECT MIN(COALESCE(
                        FROM_UNIXTIME(NULLIF(r.createdrecet_at, 0)),
                        TIMESTAMP(r.date_recet)
                    )) AS pending_date,
                    COUNT(*) AS nb
                FROM recette r
                JOIN caisse cs ON r.idcaisse = cs.id_caiss
                WHERE r.idopera = ?
                AND cs.gexp_caiss = ?
                AND r.is_actifrecet = 0
                AND r.actif_rect = 0
                AND r.type_recet <> 'Courrier'
                AND r.active_recet = 0
                AND (r.is_validerecet = 0 OR r.is_validerecet IS NULL)

                UNION ALL

                SELECT MIN(COALESCE(
                        FROM_UNIXTIME(NULLIF(d.createddep_at, 0)),
                        TIMESTAMP(d.date_depens)
                    )) AS pending_date,
                    COUNT(*) AS nb
                FROM depense d
                JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                WHERE d.idop_dep = ?
                AND cs.gexp_caiss = ?
                AND d.is_actifdep = 0
                AND d.actif_deps = 0
                AND d.type_depense <> 'Courrier'
                AND d.active_dep = 0
                AND (d.is_validedep = 0 OR d.is_validedep IS NULL)

                UNION ALL

                SELECT MIN(COALESCE(
                        d.createddepot_at,
                        TIMESTAMP(d.datedepot) + INTERVAL 1 DAY - INTERVAL 1 SECOND
                    )) AS pending_date,
                    COUNT(*) AS nb
                FROM depot d
                JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                WHERE d.idop_depot = ?
                AND cs.gexp_caiss = ?
                AND d.arret_caisdepo = 0
                AND d.is_actifdepo = 0
                AND d.is_validdepo = 0
                AND d.actif_depo = 0
                AND d.type_depot <> 'Courrier'
                AND COALESCE(d.valid_depo, '') <> 'valid'
            ) pending",
            array(
                $roleattribut, $gare_id,
                $roleattribut, $gare_id,
                $roleattribut, $gare_id,
            )
        );
        if ($pending_q === false) {
            log_message('error', 'compte_arret_chef_pending_status: requête pending échouée');
            return $open;
        }
        $row = $pending_q->row();

        if (!$row || (int) $row->pending_count <= 0 || empty($row->oldest_date)) {
            return $open;
        }

        $oldest = strtotime((string) $row->oldest_date . ' UTC');
        $hours = compte_arret_chef_hours_limit();
        $deadline = $oldest ? $oldest + ($hours * 3600) : 0;
        if ($deadline <= 0 || time() <= $deadline) {
            return $open;
        }

        return array(
            'blocked' => true,
            'only_compte' => true,
            'grace' => false,
            'reason' => 'Délai de ' . $hours . ' h dépassé : '
                . (int) $row->pending_count
                . ' opération(s) ne sont pas encore envoyées au caissier. '
                . 'Effectuez immédiatement votre arrêt de compte.',
            'code' => 'chef_arret_overdue',
            'warnings' => array(),
        );
    }
}

if (!function_exists('compte_arret_check_activite')) {
    /**
     * @return array{blocked:bool,grace:bool,code:string,reason:string}
     */
    function compte_arret_check_activite($roleattribut, $activite, $userole, $gare_id = null)
    {
        $ok = ['blocked' => false, 'grace' => false, 'code' => 'ok', 'reason' => ''];
        $hours = compte_arret_hours_limit($gare_id, $userole);

        $checks = [
            'ticket' => [
                'unclosed' => 'compte_arret_unclosed_ticket',
                'pending' => 'compte_arret_pending_ticket',
                'unclosed_msg' => 'Des ventes ticket des jours précédents ne sont pas arrêtées. Utilisez le bouton COMPTE.',
                'expired_msg' => 'Votre arrêt de compte ticket dépasse ' . $hours . ' h sans validation chef guichet. Contactez le chef guichet.',
                'grace_msg' => 'Arrêt ticket en attente de validation chef guichet (délai ' . $hours . ' h).',
            ],
            'bagage' => [
                'unclosed' => 'compte_arret_unclosed_bagage',
                'pending' => 'compte_arret_pending_bagage',
                'unclosed_msg' => 'Des ventes bagage des jours précédents ne sont pas arrêtées. Utilisez le bouton COMPTE.',
                'expired_msg' => 'Votre arrêt de compte bagage dépasse ' . $hours . ' h sans validation. Contactez le chef guichet.',
                'grace_msg' => 'Arrêt bagage en attente de validation (délai ' . $hours . ' h).',
            ],
            'courrier' => [
                'unclosed' => 'compte_arret_unclosed_courrier',
                'pending' => 'compte_arret_pending_courrier',
                'unclosed_msg' => 'Des envois courrier des jours précédents ne sont pas arrêtés. Utilisez le bouton COMPTE.',
                'expired_msg' => 'Votre arrêt de compte courrier dépasse ' . $hours . ' h sans validation. Contactez le chef guichet.',
                'grace_msg' => 'Arrêt courrier en attente de validation (délai ' . $hours . ' h).',
            ],
        ];

        if (!isset($checks[$activite])) {
            return $ok;
        }

        $cfg = $checks[$activite];

        if ($cfg['unclosed']($roleattribut, $gare_id)) {
            return [
                'blocked' => true,
                'grace' => false,
                'code' => 'unclosed_' . $activite,
                'reason' => $cfg['unclosed_msg'],
            ];
        }

        $pending = $cfg['pending']($roleattribut, $hours);
        if (!$pending) {
            return $ok;
        }

        if ((int) $pending->expired === 1) {
            $msg = recette_role_is_saisie($userole)
                ? str_replace('chef guichet', 'caissier', $cfg['expired_msg'])
                : $cfg['expired_msg'];

            return [
                'blocked' => true,
                'grace' => false,
                'code' => 'expired_' . $activite,
                'reason' => $msg,
            ];
        }

        return [
            'blocked' => false,
            'grace' => true,
            'code' => 'grace_' . $activite,
            'reason' => $cfg['grace_msg'],
        ];
    }
}

if (!function_exists('compte_arret_status')) {
    /**
     * @param string|null $activite ticket|bagage|courrier|null (toutes)
     * @return array{blocked:bool,only_compte:bool,grace:bool,reason:string,code:string,warnings:array}
     */
    function compte_arret_status($userole, $roleattribut, $gare_id = null, $activite = null, $cpuser_id = null)
    {
        $open = [
            'blocked' => false,
            'only_compte' => false,
            'grace' => false,
            'reason' => '',
            'code' => 'ok',
            'warnings' => [],
        ];

        $chef_status = compte_arret_chef_pending_status(
            $userole,
            $roleattribut,
            $gare_id
        );
        if (!empty($chef_status['blocked'])) {
            return $chef_status;
        }

        $vendeur_restr = compte_arret_vendeur_restriction_applies($userole, $gare_id);
        if (!compte_arret_rules_enabled() && !$vendeur_restr) {
            return array_merge($open, [
                'code' => 'rules_disabled',
                'reason' => '',
            ]);
        }

        if ($vendeur_restr) {
            // Restriction page : vendeurs guichet uniquement (6/10/12/15/17).
        } elseif (!compte_arret_role_vendeur($userole)) {
            return $open;
        }

        if (in_array((string) $userole, compte_arret_admin_roles(), true)) {
            return $open;
        }

        if ($cpuser_id === null && function_exists('get_instance')) {
            $CI =& get_instance();
            if ($CI->session->userdata('agent')) {
                $cpuser_id = (int) $CI->session->agent->cpuser_id;
            }
        }

        if ($cpuser_id && compte_arret_has_admin_override($cpuser_id)) {
            return array_merge($open, [
                'code' => 'admin_override',
                'reason' => 'Dérogation administrateur active.',
            ]);
        }

        $roleattribut = (int) $roleattribut;
        if ($roleattribut <= 0) {
            return $open;
        }

        $activites = $activite ? [(string) $activite] : ['ticket', 'bagage', 'courrier'];
        $warnings = [];
        $blocked = null;

        foreach ($activites as $act) {
            $r = compte_arret_check_activite($roleattribut, $act, $userole, $gare_id);
            if ($r['blocked']) {
                $blocked = $r;
                break;
            }
            if ($r['grace']) {
                $warnings[] = $r['reason'];
            }
        }

        if ($blocked) {
            return [
                'blocked' => true,
                'only_compte' => true,
                'grace' => false,
                'reason' => $blocked['reason'],
                'code' => $blocked['code'],
                'warnings' => [],
            ];
        }

        if (!empty($warnings)) {
            return [
                'blocked' => false,
                'only_compte' => false,
                'grace' => true,
                'reason' => implode(' ', $warnings),
                'code' => 'grace',
                'warnings' => $warnings,
            ];
        }

        return $open;
    }
}

if (!function_exists('compte_arret_is_blocked')) {
    function compte_arret_is_blocked($userole, $roleattribut, $gare_id = null, $activite = null, $cpuser_id = null)
    {
        return compte_arret_status($userole, $roleattribut, $gare_id, $activite, $cpuser_id)['blocked'];
    }
}

if (!function_exists('compte_arret_guard_sale')) {
    /**
     * Vérifie si la vente est autorisée pour une activité.
     *
     * @return false|string false si OK, message d'erreur si bloqué
     */
    function compte_arret_guard_sale($activite, $roleattribut = null, $gare_id = null)
    {
        $CI =& get_instance();
        if (!$CI->session->userdata('agent')) {
            return false;
        }

        $agent = $CI->session->agent;
        if ($roleattribut === null || $roleattribut === '') {
            $roleattribut = !empty($agent->roleattribut) ? (string) $agent->roleattribut : '';
        }

        $status = compte_arret_status(
            $agent->userole,
            $roleattribut,
            $gare_id,
            $activite,
            (int) $agent->cpuser_id
        );

        if (!$status['blocked']) {
            compte_arret_track_activity((int) $agent->cpuser_id);
            return false;
        }

        return $status['reason'];
    }
}

if (!function_exists('compte_arret_redirect_guichet')) {
    function compte_arret_redirect_guichet($roleattribut, $gare_id, $sgare_id, $message = null)
    {
        $CI =& get_instance();
        if (!$CI->session->userdata('company')) {
            redirect('login/ins');
            return;
        }

        $url = 'gares/' . $CI->session->company->ekey . '/gTc/' . $gare_id . '/compte/'
            . $roleattribut . '/' . $sgare_id . '/' . mdate('%d/%m/%Y', now('UTC'));

        if ($message !== null && $message !== '') {
            if (session_status() === PHP_SESSION_ACTIVE) {
                $CI->session->set_flashdata('sale_error', $message);
            }
            $url .= '?sale_error=' . rawurlencode($message);
        }

        redirect($url);
    }
}

if (!function_exists('compte_arret_compte_card_status')) {
    /**
     * Statut affiché sur la fiche compte utilisateur.
     *
     * @param object $row compte_user + champs jointés
     * @return array{label:string,class:string,motif:string,actif:bool}
     */
    function compte_arret_compte_card_status($row)
    {
        if (empty($row->cpuser_id)) {
            return [
                'label' => 'Sans compte',
                'class' => 'secondary',
                'motif' => 'Aucun login guichet créé.',
                'actif' => false,
            ];
        }

        $actif = !isset($row->activer) || (string) $row->activer === '0';

        if (!compte_arret_rules_enabled()) {
            return [
                'label' => $actif ? 'Actif' : 'Désactivé',
                'class' => $actif ? 'success' : 'danger',
                'motif' => $actif ? 'Connexion autorisée.' : 'Compte désactivé manuellement.',
                'actif' => $actif,
            ];
        }

        if ($actif) {
            $motif = 'Connexion et vente autorisées.';
            if (!empty($row->autorisation_vente_forcee) && (string) $row->autorisation_vente_forcee === '1') {
                $motif = 'Dérogation vente active.';
            }

            return [
                'label' => 'Actif',
                'class' => 'success',
                'motif' => $motif,
                'actif' => true,
            ];
        }

        if (!empty($row->exempt_desactivation_auto) && (string) $row->exempt_desactivation_auto === '1') {
            return [
                'label' => 'Désactivé',
                'class' => 'danger',
                'motif' => 'Compte exempté du cron — désactivation manuelle administrateur.',
                'actif' => false,
            ];
        }

        if (!empty($row->desactivation_motif)) {
            return [
                'label' => 'Désactivé',
                'class' => 'danger',
                'motif' => (string) $row->desactivation_motif,
                'actif' => false,
            ];
        }

        $jours = (int) compte_arret_desactivation_jours();
        if (!empty($row->derniere_activite_at)) {
            $last = strtotime($row->derniere_activite_at);
            if ($last && $last < time() - ($jours * 86400)) {
                return [
                    'label' => 'Désactivé',
                    'class' => 'danger',
                    'motif' => 'Inactivité > ' . $jours . ' j (dernière activité : '
                        . $row->derniere_activite_at . ').',
                    'actif' => false,
                ];
            }
        }

        if (!empty($row->date_deconect)) {
            return [
                'label' => 'Désactivé',
                'class' => 'danger',
                'motif' => 'Désactivation manuelle (depuis ' . $row->date_deconect . ').',
                'actif' => false,
            ];
        }

        return [
            'label' => 'Désactivé',
            'class' => 'danger',
            'motif' => 'Désactivation manuelle administrateur.',
            'actif' => false,
        ];
    }
}

if (!function_exists('compte_arret_resolve_roleattribut')) {
    /**
     * roleattribut effectif pour arrêt / validation compte (URL + POST + session).
     */
    function compte_arret_resolve_roleattribut($ekey, $gare_id, $url_hint)
    {
        $CI =& get_instance();

        if (function_exists('auth_session_vendor_ignores_post_hints')
            && auth_session_vendor_ignores_post_hints()) {
            return auth_sale_roleattribut($ekey, $gare_id);
        }

        $post_hint = trim((string) $CI->input->post('userconnected'));
        $hint = ($post_hint !== '' && $post_hint !== '0') ? $post_hint : $url_hint;

        if ($hint === '' || $hint === '0') {
            $hint = roleattribut_guard_post_hint($ekey, 'gareconnect', 'userconnected');
        }

        $op = roleattribut_guard_operateur($ekey, $gare_id, $hint);

        return (int) $op['roleattribut'];
    }
}

if (!function_exists('compte_arret_bind_operateur')) {
    /**
     * Résout l'opérateur cible pour un arrêt de compte (affichage ou action).
     *
     * @return array{roleattribut:int,conex:object|null,userole:string|null}
     */
    function compte_arret_bind_operateur($ekey, $gare_id, $url_hint)
    {
        if (function_exists('auth_session_vendor_ignores_post_hints')
            && auth_session_vendor_ignores_post_hints()) {
            $ra = auth_sale_roleattribut($ekey, $gare_id);
            $op = roleattribut_guard_operateur($ekey, $gare_id, null);

            return array(
                'roleattribut' => $ra > 0 ? $ra : (int) $op['roleattribut'],
                'conex' => $op['conex'],
                'userole' => $op['userole'],
            );
        }

        $post_hint = trim((string) get_instance()->input->post('userconnected'));
        $hint = ($post_hint !== '' && $post_hint !== '0') ? $post_hint : $url_hint;

        if ($hint === '' || $hint === '0') {
            $hint = roleattribut_guard_post_hint($ekey, 'gareconnect', 'userconnected');
        }

        return roleattribut_guard_operateur($ekey, $gare_id, $hint);
    }
}

if (!function_exists('compte_arret_redirect_if_foreign_url')) {
    /**
     * Redirige vers le bon compte si l'URL compte/XX ne correspond pas au vendeur connecté.
     *
     * @param string|callable $redirect_url_for_resolved URL finale ou callable(int $roleattribut): string
     * @return array{roleattribut:int,conex:object|null,userole:string|null}
     */
    function compte_arret_redirect_if_foreign_url($redirect_url_for_resolved, $ekey, $gare_id, $url_hint)
    {
        $requested = (int) $url_hint;
        $op = compte_arret_bind_operateur($ekey, $gare_id, $url_hint);
        $resolved = (int) $op['roleattribut'];

        $redirect_url = $redirect_url_for_resolved;
        if (is_callable($redirect_url_for_resolved)) {
            $redirect_url = call_user_func($redirect_url_for_resolved, $resolved);
        }

        roleattribut_guard_redirect_if_url_mismatch($redirect_url, $requested, $resolved);

        return $op;
    }
}

if (!function_exists('compte_arret_track_activity_safe')) {
    /**
     * Met à jour l'activité du compte connecté (ignore compconnected POST pour les vendeurs).
     */
    function compte_arret_track_activity_safe()
    {
        $CI =& get_instance();
        if (!$CI->session->userdata('agent')) {
            return;
        }

        $cp = (int) $CI->session->agent->cpuser_id;
        if ($cp > 0) {
            compte_arret_track_activity($cp);
        }
    }
}

if (!function_exists('validerecette_vendeur_useroles')) {
    /** Rôles guichet vendeur (pas chef 5/16 ni caissier 4/18). */
    function validerecette_vendeur_useroles()
    {
        return array('6', '10', '12', '15', '17');
    }
}

if (!function_exists('validerecette_is_vendeur_userole')) {
    function validerecette_is_vendeur_userole($userole)
    {
        return in_array((string) $userole, validerecette_vendeur_useroles(), true);
    }
}

if (!function_exists('validerecette_chef_roleattribut_on_gare')) {
    /**
     * Chef guichet prioritaire sur une gare (activeattrib desc).
     *
     * @return int|null
     */
    function validerecette_chef_roleattribut_on_gare($gare_id)
    {
        $CI =& get_instance();
        $gare_id = $CI->db->escape_str((string) $gare_id);

        $row = $CI->db->query(
            "SELECT ar.roleattribut FROM attributions_role ar
            JOIN user_login ul ON ar.idgestcompte = ul.uid_login
            WHERE ul.guser = '{$gare_id}'
            AND ar.userole IN (5, 16)
            AND ar.activer_role = 0
            AND ul.comptactif = 0
            ORDER BY ar.activeattrib DESC, ar.roleattribut ASC
            LIMIT 1"
        )->row();

        return ($row && !empty($row->roleattribut)) ? (int) $row->roleattribut : null;
    }
}

if (!function_exists('validerecette_resolve_idopera')) {
    /**
     * idopera pour recette créée lors de la validation d'arrêt vendeur par le chef.
     * Ne doit jamais être le roleattribut du vendeur arrêté (compt_id URL).
     *
     * @param string $ekey
     * @param string $gare_id
     * @param int|string $vendor_roleattribut compt_id (vendeur validé)
     * @return int
     */
    function validerecette_resolve_idopera($ekey, $gare_id, $vendor_roleattribut)
    {
        $vendor_roleattribut = (int) $vendor_roleattribut;
        $CI =& get_instance();
        $form_hint = trim((string) $CI->input->post('userconnected'));
        $candidates = array();

        if ($form_hint !== '' && $form_hint !== '0') {
            $candidates[] = roleattribut_guard_operateur($ekey, $gare_id, $form_hint);
        }
        $candidates[] = roleattribut_guard_operateur($ekey, $gare_id, null);

        foreach ($candidates as $op) {
            $ra = (int) $op['roleattribut'];
            if ($ra <= 0) {
                continue;
            }
            $role = recette_role_userole_for_attribut($ra, $op['conex']);
            if (recette_role_is_saisie($role) && $ra !== $vendor_roleattribut) {
                return $ra;
            }
            if (recette_role_is_validateur_principal($role) || recette_role_is_validateur_adjoint($role)) {
                return $ra;
            }
            if (roleattribut_guard_is_supervisor() && $ra !== $vendor_roleattribut) {
                return $ra;
            }
        }

        $chef = validerecette_chef_roleattribut_on_gare($gare_id);
        if ($chef !== null && $chef !== $vendor_roleattribut) {
            return $chef;
        }

        $fallback = (int) roleattribut_guard_post_hint($ekey);
        if ($fallback > 0 && $fallback !== $vendor_roleattribut) {
            $role = recette_role_userole_for_attribut($fallback);
            if (!validerecette_is_vendeur_userole($role)) {
                return $fallback;
            }
        }

        return $chef !== null ? $chef : $fallback;
    }
}

if (!function_exists('validerecette_operavalid_caissier')) {
    /**
     * roleattribut du caissier connecté pour operavalid (≠ idopera chef).
     *
     * @return int|null
     */
    function validerecette_operavalid_caissier($ekey, $gare_id)
    {
        $CI =& get_instance();
        if (!$CI->session->userdata('agent') || (string) $CI->session->agent->userole !== '4') {
            return null;
        }

        $op = roleattribut_guard_operateur($ekey, $gare_id, null);
        $ra = (int) $op['roleattribut'];

        return $ra > 0 ? $ra : null;
    }
}

if (!function_exists('compte_arret_desactivation_motif_sql')) {
    /**
     * Motif stocké en base pour une désactivation auto (expression SQL).
     *
     * @param int $jours
     * @return string
     */
    function compte_arret_desactivation_motif_sql($jours)
    {
        $jours = (int) $jours;
        return "CONCAT("
            . "'Désactivation automatique : aucune activité depuis {$jours} jour(s)"
            . " (dernière activité : ',"
            . " IFNULL(DATE_FORMAT(cu.derniere_activite_at, '%Y-%m-%d %H:%i:%s'), 'jamais'),"
            . " ').'"
            . ")";
    }
}

if (!function_exists('compte_arret_run_session_deconnexion')) {
    /**
     * Déconnecte les sessions sans activité depuis N minutes (cron / CLI).
     *
     * @param mysqli|null $mysqli Si fourni (cron CLI), sinon CI->db
     * @return int
     */
    function compte_arret_run_session_deconnexion($mysqli = null)
    {
        if (!compte_arret_session_deconnexion_enabled()) {
            return 0;
        }

        $minutes = (int) compte_arret_session_idle_minutes();

        $sql = "UPDATE compte_user cu
            SET cu.is_conect = 0,
                cu.date_deconect = NOW(),
                cu.session_token = NULL
            WHERE cu.is_conect = 1
            AND (
                cu.derniere_activite_at IS NULL
                OR cu.derniere_activite_at < DATE_SUB(NOW(), INTERVAL {$minutes} MINUTE)
            )";

        if ($mysqli instanceof mysqli) {
            if (!$mysqli->query($sql)) {
                return 0;
            }
            $n = (int) $mysqli->affected_rows;

            $mysqli->query(
                "UPDATE attributions_role ar
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                SET ar.activeattrib = 0
                WHERE cu.is_conect = 0 AND ar.activeattrib = 1"
            );

            return $n;
        }

        $CI =& get_instance();
        $CI->db->query($sql);
        $n = (int) $CI->db->affected_rows();
        $CI->db->query(
            "UPDATE attributions_role ar
            JOIN user_login ul ON ar.idgestcompte = ul.uid_login
            JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
            SET ar.activeattrib = 0
            WHERE cu.is_conect = 0 AND ar.activeattrib = 1"
        );

        return $n;
    }
}

if (!function_exists('compte_arret_run_inactivite_desactivation')) {
    /**
     * Désactive les comptes sans activité depuis N jours (cron), avec motif.
     *
     * @param mysqli|null $mysqli
     * @return int nombre de comptes désactivés
     */
    function compte_arret_run_inactivite_desactivation($mysqli = null)
    {
        if (!compte_arret_inactivite_cron_enabled()) {
            return 0;
        }

        $jours = (int) compte_arret_desactivation_jours();
        $admin_in = implode(',', array_map('intval', compte_arret_admin_roles()));
        $motif_sql = compte_arret_desactivation_motif_sql($jours);

        $has_motif = true;
        if ($mysqli instanceof mysqli) {
            $chk = $mysqli->query("SHOW COLUMNS FROM compte_user LIKE 'desactivation_motif'");
            $has_motif = ($chk && $chk->num_rows > 0);
        } elseif (function_exists('get_instance')) {
            $CI =& get_instance();
            $has_motif = $CI->db->field_exists('desactivation_motif', 'compte_user');
        }

        $set_extra = $has_motif
            ? ", cu.desactivation_motif = {$motif_sql}, cu.desactivation_at = NOW()"
            : '';

        // Rôles en mode ciblé (caissier / sup. agence) : exclus du cut global.
        $scoped = compte_arret_restriction_scoped_roles();
        $scoped_sql = '';
        if (!empty($scoped)) {
            $scoped_in = implode(',', array_map('intval', $scoped));
            $scoped_sql = " AND NOT EXISTS (
                SELECT 1 FROM attributions_role arx
                JOIN user_login ulx ON arx.idgestcompte = ulx.uid_login
                WHERE ulx.uid_usercpte = cu.cpuser_id
                AND arx.userole IN ({$scoped_in})
                AND arx.activer_role = 0
            )";
        }

        $sql = "UPDATE compte_user cu
            LEFT JOIN (
                SELECT DISTINCT ul.uid_usercpte AS cpuser_id
                FROM attributions_role ar
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                WHERE ar.userole IN ({$admin_in}) AND ar.activer_role = 0
            ) adm ON adm.cpuser_id = cu.cpuser_id
            SET cu.activer = 1,
                cu.is_conect = 0,
                cu.date_deconect = NOW(),
                cu.session_token = NULL
                {$set_extra}
            WHERE cu.activer = 0
            AND cu.exempt_desactivation_auto = 0
            AND adm.cpuser_id IS NULL
            AND cu.derniere_activite_at IS NOT NULL
            AND cu.derniere_activite_at < DATE_SUB(NOW(), INTERVAL {$jours} DAY)
            {$scoped_sql}";

        $n = 0;
        if ($mysqli instanceof mysqli) {
            if ($mysqli->query($sql)) {
                $n = (int) $mysqli->affected_rows;
            }
        } else {
            $CI =& get_instance();
            $CI->db->query($sql);
            $n = (int) $CI->db->affected_rows();
        }

        $n += compte_arret_run_inactivite_desactivation_scoped($mysqli, $jours);

        return $n;
    }
}

if (!function_exists('compte_arret_run_inactivite_desactivation_scoped')) {
    /**
     * Désactive les attributions (activer_role=1 + login gare) des rôles restreints
     * (caissier 4, sup. agence 13, vendeurs 6/10/12/15/17)
     * uniquement sur les gares cochées (sous-gares incluses via guser=idengare).
     *
     * @param mysqli|null $mysqli
     * @param int $jours
     * @return int
     */
    function compte_arret_run_inactivite_desactivation_scoped($mysqli = null, $jours = null)
    {
        if (!compte_arret_inactivite_cron_enabled()) {
            return 0;
        }

        $jours = $jours !== null ? (int) $jours : (int) compte_arret_desactivation_jours();
        $total = 0;

        foreach (compte_arret_restriction_role_map() as $userole => $cfg) {
            if (!(bool) compte_arret_config_item($cfg['enabled'], false)) {
                continue;
            }
            $gares = compte_arret_config_item($cfg['gares'], array());
            if (!is_array($gares)) {
                $gares = compte_arret_param_cast($cfg['gares'], $gares);
            }

            $gare_sql = '';
            if (!empty($gares)) {
                $escaped = array();
                foreach ($gares as $g) {
                    $g = preg_replace('/[^A-Za-z0-9_-]/', '', (string) $g);
                    if ($g === '') {
                        continue;
                    }
                    if ($mysqli instanceof mysqli) {
                        $escaped[] = "'" . $mysqli->real_escape_string($g) . "'";
                    } else {
                        $CI =& get_instance();
                        $escaped[] = $CI->db->escape($g);
                    }
                }
                if (empty($escaped)) {
                    continue;
                }
                $gare_sql = ' AND ul.guser IN (' . implode(',', $escaped) . ')';
            }

            $role = (int) $userole;
            $sql = "UPDATE attributions_role ar
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                SET ar.activer_role = 1,
                    ul.comptactif = 1
                WHERE ar.userole = {$role}
                AND ar.activer_role = 0
                AND cu.activer = 0
                AND cu.exempt_desactivation_auto = 0
                AND cu.derniere_activite_at IS NOT NULL
                AND cu.derniere_activite_at < DATE_SUB(NOW(), INTERVAL {$jours} DAY)
                {$gare_sql}";

            if ($mysqli instanceof mysqli) {
                if ($mysqli->query($sql)) {
                    $total += (int) $mysqli->affected_rows;
                }
            } else {
                $CI =& get_instance();
                $CI->db->query($sql);
                $total += (int) $CI->db->affected_rows();
            }
        }

        return $total;
    }
}

if (!function_exists('caissier_validation_rdd_redirect')) {
    /**
     * Retour liste recettes/dépenses/dépôts non validés (Caisses/optionscaisse).
     */
    function caissier_validation_rdd_redirect($ekey, $gare_id, $caisse_id, $chef_ra, $caissier_ra, $idsg, $type)
    {
        $gare_id = roleattribut_guard_normalize_gare_id($ekey, $gare_id);
        $allowed = array('validation_recettes', 'validation_depenses', 'validation_depots');
        if (!in_array($type, $allowed, true)) {
            $type = 'validation_recettes';
        }

        $date = mdate('%d/%m/%Y', now('UTC'));
        redirect(site_url(
            'caisses/' . $ekey . '/RdD/' . $gare_id . '/'
            . (int) $caisse_id . '/' . (int) $chef_ra . '/'
            . $type . '/' . (int) $caissier_ra . '/'
            . (int) $idsg . '/' . $date
        ));
        exit;
    }
}

if (!function_exists('caissier_validation_viewcaissier_redirect')) {
    /**
     * Retour page arrêt compte chef (Utilisateurs/viewcaissier).
     */
    function caissier_validation_viewcaissier_redirect($ekey, $gare_id, $caisse_id, $chef_ra, $caissier_ra, $idsg)
    {
        $gare_id = roleattribut_guard_normalize_gare_id($ekey, $gare_id);
        $date = mdate('%d/%m/%Y', now('UTC'));
        redirect(site_url(
            'utilisateurs/' . $ekey . '/caissier/' . $gare_id . '/'
            . (int) $caisse_id . '/' . (int) $chef_ra . '/'
            . (int) $caissier_ra . '/' . (int) $idsg . '/' . $date
        ));
        exit;
    }
}

if (!function_exists('caissier_validation_bind_fail_redirect')) {
    /**
     * Retour caisse ou page VALIDATION si le bind chef/caissier échoue.
     */
    function caissier_validation_bind_fail_redirect($ekey, $gare_id, $caissier_hint = null, array $context = array())
    {
        $CI =& get_instance();
        $CI->load->model('Compte_user_model', 'm_compte_user_validation');
        $gare_id = roleattribut_guard_normalize_gare_id($ekey, $gare_id);
        $caissier_ra = (int) $CI->m_compte_user_validation->roleattribut_hint_on_gare($gare_id, $caissier_hint, $ekey);
        if ($caissier_ra <= 0 && $CI->session->userdata('agent') && !empty($CI->session->agent->roleattribut)) {
            $caissier_ra = (int) $CI->session->agent->roleattribut;
        }

        $date = mdate('%d/%m/%Y', now('UTC'));
        $idcai = isset($context['idcai']) ? (int) $context['idcai'] : 0;
        $idsg = isset($context['idsg']) ? (int) $context['idsg'] : 0;
        $type = isset($context['type']) ? (string) $context['type'] : '';

        if ($idcai > 0 && $type !== '') {
            $chef_ra = isset($context['chef_ra']) ? (int) $context['chef_ra'] : 0;
            if ($chef_ra <= 0 && !empty($context['chef_hint'])) {
                $chef_ra = (int) $CI->m_compte_user_validation->roleattribut_hint_on_gare(
                    $gare_id,
                    $context['chef_hint'],
                    $ekey
                );
            }
            caissier_validation_rdd_redirect($ekey, $gare_id, $idcai, $chef_ra, $caissier_ra, $idsg, $type);
        }

        if ($idcai > 0) {
            redirect(site_url(
                'caisses/' . $ekey . '/gTv/' . $gare_id . '/' . $idcai
                . '/validation/' . $caissier_ra . '/' . $idsg . '/' . $date
            ));
        } else {
            redirect(site_url(
                'gares/' . $ekey . '/gTv/' . $gare_id . '/cais/' . $caissier_ra . '/0/' . $date
            ));
        }
        exit;
    }
}

if (!function_exists('caissier_validation_bind_operateurs')) {
    /**
     * Lie caissier (session) et chef guichet (URL) pour validation arrêt de compte.
     *
     * @param array $fail_context idcai, idsg (retour page VALIDATION si bind échoue)
     * @return array{chef_ra:int,caissier_ra:int,chef_userole:string,caissier_conex:object|null}
     */
    function caissier_validation_bind_operateurs($ekey, $gare_id, $chef_hint, $caissier_hint = null, array $fail_context = array())
    {
        $CI =& get_instance();
        $CI->load->model('Compte_user_model', 'm_compte_user_validation');
        $gare_id = roleattribut_guard_normalize_gare_id($ekey, $gare_id);

        $chef_hint_ra = (int) $CI->m_compte_user_validation->roleattribut_hint_on_gare($gare_id, $chef_hint, $ekey);
        $chef = roleattribut_guard_chef_on_gare($ekey, $gare_id, $chef_hint_ra > 0 ? $chef_hint_ra : $chef_hint);
        if (!$chef && (int) $chef_hint > 0 && (int) $chef_hint !== $chef_hint_ra) {
            $chef = roleattribut_guard_chef_on_gare($ekey, $gare_id, (int) $chef_hint);
        }

        if (!$chef || !recette_role_is_saisie($chef->userole)) {
            caissier_validation_bind_fail_redirect($ekey, $gare_id, $caissier_hint, array_merge($fail_context, array(
                'chef_hint' => $chef_hint,
            )));
        }

        $chef_ra = (int) $chef->roleattribut;

        $caissier_hint_ra = (int) $CI->m_compte_user_validation->roleattribut_hint_on_gare($gare_id, $caissier_hint, $ekey);
        if (roleattribut_guard_is_supervisor()) {
            $caissier_ra = $caissier_hint_ra > 0 ? $caissier_hint_ra : (int) $caissier_hint;
            $caissier_conex = $CI->m_compte_user_validation->getusergare($ekey, $gare_id, $caissier_ra);
            if (!$caissier_conex) {
                $caissier_conex = $CI->m_compte_user_validation->usget1($caissier_ra, $gare_id);
            }
            $caissier = array(
                'roleattribut' => $caissier_ra,
                'conex' => $caissier_conex,
                'userole' => ($caissier_conex && !empty($caissier_conex->userole)) ? (string) $caissier_conex->userole : null,
            );
        } else {
            $caissier = roleattribut_guard_operateur(
                $ekey,
                $gare_id,
                $caissier_hint_ra > 0 ? $caissier_hint_ra : $caissier_hint
            );
            if (!recette_role_is_validateur_principal($caissier['userole'])
                && !recette_role_is_validateur_adjoint($caissier['userole'])) {
                redirect('login/ins');
                exit;
            }
            if (!roleattribut_guard_assert_conex($caissier['conex'])) {
                caissier_validation_bind_fail_redirect($ekey, $gare_id, $caissier_hint, $fail_context);
            }
        }

        return array(
            'chef_ra' => $chef_ra,
            'caissier_ra' => (int) $caissier['roleattribut'],
            'chef_userole' => (string) $chef->userole,
            'caissier_conex' => $caissier['conex'],
        );
    }
}

if (!function_exists('caissier_arret_pending_map')) {
    /**
     * Totaux recettes/dépenses/dépôts en attente de validation caissier, par chef guichet.
     *
     * @return array<int,object>
     */
    function caissier_arret_pending_map($ekey, $gid, $idcais = null)
    {
        $CI =& get_instance();
        $gid = roleattribut_guard_normalize_gare_id($ekey, $gid);
        $idcais = ($idcais !== null && (int) $idcais > 0) ? (int) $idcais : null;
        $today = mdate('%Y-%m-%d', now());
        $caisse_sql = $idcais !== null ? ' AND cs.id_caiss = ' . $idcais : '';
        $map = array();

        $init = function ($ra) use (&$map) {
            $ra = (int) $ra;
            if (!isset($map[$ra])) {
                $map[$ra] = (object) array(
                    'roleattribut' => $ra,
                    'total_recettes' => 0.0,
                    'total_depenses' => 0.0,
                    'total_depots' => 0.0,
                    'nb_recettes' => 0,
                    'nb_depenses' => 0,
                    'nb_depots' => 0,
                );
            }
        };

        // File arrêt masse (active_*=0) + file ligne RdD (active_*=1, is_actif*=0).
        $rec_rows = $CI->db->query(
            "SELECT r.idopera AS roleattribut, COUNT(*) AS nb, COALESCE(SUM(r.montant_recet), 0) AS total
            FROM recette r
            JOIN attributions_role ar ON r.idopera = ar.roleattribut
            JOIN user_login ul ON ar.idgestcompte = ul.uid_login
            JOIN caisse cs ON r.idcaisse = cs.id_caiss
            JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
            JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
            JOIN entreprise e ON c.id_entrep = e.id_entreprise
            WHERE e.ekey = ?
            AND ul.guser = ?
            AND ar.userole IN (5, 16)
            AND r.is_actifrecet = 0
            AND r.actif_rect = 0
            AND r.type_recet <> 'Courrier'
            AND (
                (r.active_recet = 0 AND r.is_validerecet = 0 AND r.date_recet <= ?)
                OR (r.active_recet = 1)
            )
            {$caisse_sql}
            GROUP BY r.idopera",
            array($ekey, $gid, $today)
        )->result();

        foreach ($rec_rows as $row) {
            $init($row->roleattribut);
            $map[(int) $row->roleattribut]->total_recettes = (float) $row->total;
            $map[(int) $row->roleattribut]->nb_recettes = (int) $row->nb;
        }

        $dep_rows = $CI->db->query(
            "SELECT d.idop_dep AS roleattribut, COUNT(*) AS nb, COALESCE(SUM(d.montant_depens), 0) AS total
            FROM depense d
            JOIN attributions_role ar ON d.idop_dep = ar.roleattribut
            JOIN user_login ul ON ar.idgestcompte = ul.uid_login
            JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
            JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
            JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
            JOIN entreprise e ON c.id_entrep = e.id_entreprise
            WHERE e.ekey = ?
            AND ul.guser = ?
            AND ar.userole IN (5, 16)
            AND d.is_actifdep = 0
            AND d.actif_deps = 0
            AND d.type_depense <> 'Courrier'
            AND (
                (d.active_dep = 0 AND d.is_validedep = 0 AND d.date_depens <= ?)
                OR (d.active_dep = 1 AND d.ferme_caisdep = 0)
            )
            {$caisse_sql}
            GROUP BY d.idop_dep",
            array($ekey, $gid, $today)
        )->result();

        foreach ($dep_rows as $row) {
            $init($row->roleattribut);
            $map[(int) $row->roleattribut]->total_depenses = (float) $row->total;
            $map[(int) $row->roleattribut]->nb_depenses = (int) $row->nb;
        }

        $depo_rows = $CI->db->query(
            "SELECT d.idop_depot AS roleattribut, COUNT(*) AS nb, COALESCE(SUM(d.montant_depot), 0) AS total
            FROM depot d
            JOIN attributions_role ar ON d.idop_depot = ar.roleattribut
            JOIN user_login ul ON ar.idgestcompte = ul.uid_login
            JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
            JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
            JOIN compagnies c ON d.compkey_depo = c.cle_compagnie
            JOIN entreprise e ON c.id_entrep = e.id_entreprise
            WHERE e.ekey = ?
            AND ul.guser = ?
            AND ar.userole IN (5, 16)
            AND d.arret_caisdepo = 0
            AND d.is_actifdepo = 0
            AND d.is_validdepo = 0
            AND d.actif_depo = 0
            AND d.type_depot <> 'Courrier'
            AND d.datedepot <= ?
            {$caisse_sql}
            GROUP BY d.idop_depot",
            array($ekey, $gid, $today)
        )->result();

        foreach ($depo_rows as $row) {
            $init($row->roleattribut);
            $map[(int) $row->roleattribut]->total_depots = (float) $row->total;
            $map[(int) $row->roleattribut]->nb_depots = (int) $row->nb;
        }

        return $map;
    }
}

if (!function_exists('caissier_validation_chef_pending_totals')) {
    /**
     * Totaux encore en attente de validation caissier pour un chef (diminuent après chaque valid).
     *
     * @return object{total_recettes:float,total_depenses:float,total_depots:float,solde:float}
     */
    function caissier_validation_chef_pending_totals($ekey, $gid, $idcais, $chef_ra)
    {
        $pending = caissier_arret_pending_for_chef(
            caissier_arret_pending_map($ekey, $gid, $idcais),
            $chef_ra
        );

        return (object) array(
            'total_recettes' => (float) $pending->total_recettes,
            'total_depenses' => (float) $pending->total_depenses,
            'total_depots' => (float) $pending->total_depots,
            'solde' => (float) $pending->total_recettes
                + (float) $pending->total_depots
                - (float) $pending->total_depenses,
        );
    }
}

if (!function_exists('caissier_arret_pending_for_chef')) {
    function caissier_arret_pending_for_chef(array $map, $roleattribut)
    {
        $ra = (int) $roleattribut;
        if (!isset($map[$ra])) {
            return (object) array(
                'total_recettes' => 0.0,
                'total_depenses' => 0.0,
                'total_depots' => 0.0,
                'has_pending' => false,
            );
        }

        $p = $map[$ra];
        $p->has_pending = ($p->total_recettes > 0 || $p->total_depenses > 0 || $p->total_depots > 0);

        return $p;
    }
}
