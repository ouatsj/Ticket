<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Contrôleur de base : charge uniquement les modèles du contrôleur courant.
 */
class MY_Controller extends CI_Controller
{
    /** @var array|null */
    private static $_model_map;

    /** @var array|null */
    private static $_controller_models;

    /**
     * @return array
     */
    public static function model_map()
    {
        if (self::$_model_map === null) {
            self::$_model_map = require APPPATH . 'config/model_map.php';
        }

        return self::$_model_map;
    }

    /**
     * @return array
     */
    protected static function controller_models()
    {
        if (self::$_controller_models === null) {
            self::$_controller_models = require APPPATH . 'config/controller_models.php';
        }

        return self::$_controller_models;
    }

    public function __construct()
    {
        parent::__construct();
        $this->_enforce_auth();
        $this->_enforce_roleattribut_uri();
        $this->load->helper('session');
        $this->_load_controller_models();
        session_release_lock_on_shutdown();
    }

    /**
     * Bloque l'accès guichet via URL d'un autre compte (toutes gares / sous-gares).
     */
    protected function _enforce_roleattribut_uri()
    {
        if (!$this->session->userdata('agent') || !$this->session->userdata('company')) {
            return;
        }

        roleattribut_guard_enforce_uri_segments();
    }

    /**
     * Redirige vers login si session agent/company absente (sauf routes publiques).
     */
    protected function _enforce_auth()
    {
        $class = strtolower($this->router->fetch_class());
        $method = strtolower($this->router->fetch_method());
        $auth = require APPPATH . 'config/auth.php';

        if (in_array($class, $auth['public_controllers'], true)) {
            return;
        }

        foreach ($auth['public_methods'] as $route) {
            $parts = explode('/', $route, 2);
            $rc = $parts[0];
            $rm = isset($parts[1]) ? $parts[1] : '*';
            if ($rc === $class && ($rm === '*' || $rm === $method)) {
                return;
            }
        }

        if (!$this->session->userdata('agent') || !$this->session->userdata('company')) {
            if ($this->input->is_ajax_request()) {
                $this->output->set_status_header(401);
                $this->output->set_content_type('application/json');
                $this->output->set_output(json_encode(array('error' => 'auth_required')));
                exit;
            }
            redirect('login/ins');
        }

        auth_session_validate_or_logout();

        $class = strtolower($this->router->fetch_class());
        $method = strtolower($this->router->fetch_method());
        if ($class === 'gares' && in_array($method, array('options', 'optiongare'), true)) {
            auth_session_send_nocache_headers();
        }
    }

    /**
     * roleattribut sécurisé pour vente guichet (session serveur, bloc si <= 0).
     *
     * @param string $ekey
     * @param string|null $gare_id
     * @return int
     */
    protected function _auth_sale_roleattribut($ekey, $gare_id = null)
    {
        return auth_sale_require_roleattribut($ekey, $gare_id);
    }

    /**
     * Charge les modèles déclarés pour le contrôleur actif.
     */
    protected function _load_controller_models()
    {
        $class = $this->_resolve_controller_key($this->router->fetch_class());
        $aliases = isset(self::controller_models()[$class])
            ? self::controller_models()[$class]
            : array();
        $map = self::model_map();

        foreach ($aliases as $alias) {
            if (isset($map[$alias]) && !isset($this->$alias)) {
                $this->load->model($map[$alias], $alias);
            }
        }
    }

    /**
     * Résout le nom du contrôleur (URI en minuscules → clé config).
     *
     * @param string $class
     * @return string
     */
    protected function _resolve_controller_key($class)
    {
        $models = self::controller_models();
        $candidate = ucfirst($class);

        if (isset($models[$candidate])) {
            return $candidate;
        }

        foreach (array_keys($models) as $key) {
            if (strcasecmp($key, $class) === 0) {
                return $key;
            }
        }

        return $candidate;
    }

    /**
     * Résout le roleattribut réel (ignore un hint URL étranger) et charge conex.
     * Met à jour $roleattribut_hint par référence.
     *
     * @param int|string $roleattribut_hint
     * @param string $ekey
     * @param string $gare_id
     * @return object|null
     */
    protected function _roleattribut_guard_bind(&$roleattribut_hint, $ekey, $gare_id)
    {
        $op = roleattribut_guard_operateur($ekey, $gare_id, $roleattribut_hint);
        $roleattribut_hint = (int) $op['roleattribut'];
        if ($roleattribut_hint <= 0) {
            return null;
        }
        if (!empty($op['conex'])) {
            return $op['conex'];
        }

        return $this->m_compte_user->getusergare($ekey, $gare_id, $roleattribut_hint);
    }

    /**
     * Roleattribut POST sécurisé (gareconnect + userconnected par défaut).
     *
     * @param string $ekey
     * @param string|null $gare_post_key
     * @param string|null $hint_post_key
     * @return string
     */
    protected function _roleattribut_guard_post_id($ekey, $gare_post_keys = 'gareconnect', $hint_post_keys = 'userconnected')
    {
        return roleattribut_guard_post_hint($ekey, $gare_post_keys, $hint_post_keys);
    }
}
