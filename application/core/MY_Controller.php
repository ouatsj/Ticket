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

    /** @var array */
    private static $_helpers_loaded = array();

    /**
     * Charge des helpers une seule fois par requête.
     *
     * @param string[] $names
     */
    protected function _load_helpers(array $names)
    {
        $batch = array();
        foreach ($names as $name) {
            if (!isset(self::$_helpers_loaded[$name])) {
                self::$_helpers_loaded[$name] = true;
                $batch[] = $name;
            }
        }
        if ($batch !== array()) {
            $this->load->helper($batch);
        }
    }

    /**
     * Helpers requis par MY_Controller (auth, garde-fous, arrêts).
     */
    protected function _load_core_helpers()
    {
        $this->_load_helpers(array('auth_session', 'roleattribut_guard', 'compte_arret'));
    }

    /**
     * Helpers métier chargés selon le contrôleur / config (évite autoload global).
     */
    protected function _load_controller_helpers()
    {
        $class = strtolower((string) $this->router->fetch_class());
        $helpers = array();

        if ($class === 'login') {
            $helpers[] = 'passwordhash';
        }
        if (in_array($class, array('programmes', 'historique_passagers'), true)) {
            $helpers[] = 'historique_modif_ticket';
        }
        if (in_array($class, array('super_administration', 'caisses'), true)) {
            $helpers[] = 'super_admin';
        }
        if ($this->config->item('sales_price_controls_enabled')
            || $this->config->item('fraud_controls_mode') !== 'off') {
            $helpers[] = 'sales_price';
        }
        if ($this->session->userdata('agent') && $this->session->userdata('company')) {
            $helpers = array_merge($helpers, array('retour', 'recette_role', 'ticket_prix', 'url_safe'));
        }

        $this->_load_helpers(array_values(array_unique($helpers)));
    }

    public function __construct()
    {
        parent::__construct();
        $this->_load_core_helpers();
        $this->_load_controller_helpers();
        $this->_enforce_auth();
        $this->_enforce_roleattribut_uri();
        $this->_enforce_chef_arret_deadline();
        $this->_enforce_caissier_arret_deadline();
        $this->_enforce_sup_agence_validation_deadline();
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
     * Après 36 h, un chef guichet avec des opérations non envoyées au caissier
     * ne peut accéder qu'à son accueil et au parcours d'arrêt de compte.
     */
    protected function _enforce_chef_arret_deadline()
    {
        if (!$this->session->userdata('agent') || !$this->session->userdata('company')) {
            return;
        }

        $agent = $this->session->agent;
        if (!in_array((string) $agent->userole, compte_arret_chef_roles(), true)) {
            return;
        }

        $gare_id = !empty($agent->guser) ? (string) $agent->guser : '';
        $roleattribut = !empty($agent->roleattribut) ? (int) $agent->roleattribut : 0;
        $status = compte_arret_chef_pending_status(
            $agent->userole,
            $roleattribut,
            $gare_id
        );
        if (empty($status['blocked'])) {
            return;
        }

        $class = strtolower((string) $this->router->fetch_class());
        $method = strtolower((string) $this->router->fetch_method());
        $allowed = array(
            'caisses' => array('arcompte'),
            'login' => array('lout'),
        );

        if (isset($allowed[$class])
            && (in_array('*', $allowed[$class], true)
                || in_array($method, $allowed[$class], true))) {
            return;
        }

        // Accueil minimal du compte bloqué : il affiche uniquement le bouton ARRÊT DE COMPTE.
        if ($class === 'gares'
            && $method === 'options'
            && in_array('compte', $this->uri->segment_array(), true)) {
            return;
        }

        // Soumissions nécessaires à la régularisation, uniquement en POST.
        if (($class === 'caisses' && $method === 'valide')
            || ($class === 'arretcaisses' && $method === 'unstop')) {
            if (strtoupper((string) $this->input->method()) === 'POST') {
                return;
            }
        }

        // Page intermédiaire indispensable pour transmettre recettes/dépenses/dépôts au caissier.
        if ($class === 'caisses'
            && $method === 'options'
            && in_array('arretcaisse_adjoint', $this->uri->segment_array(), true)) {
            return;
        }

        if ($this->input->is_ajax_request()) {
            $this->output->set_status_header(403);
            $this->output->set_content_type('application/json');
            $this->output->set_output(json_encode(array(
                'error' => 'chef_arret_overdue',
                'message' => $status['reason'],
            )));
            exit;
        }

        $sg = $this->db->query(
            'SELECT idsousgare FROM sousgare WHERE gareprinceid = ? ORDER BY idsousgare ASC LIMIT 1',
            array($gare_id)
        )->row();
        if (!$sg || empty($sg->idsousgare)) {
            redirect('home');
            exit;
        }

        $this->session->set_flashdata('sale_error', $status['reason']);
        redirect(
            'gares/' . $this->session->company->ekey
            . '/gTc/' . $gare_id
            . '/compte/' . $roleattribut
            . '/' . (int) $sg->idsousgare
            . '/' . mdate('%d/%m/%Y', now('UTC'))
        );
        exit;
    }

    /**
     * Caissier (4) : après le jour N du mois suivant, blocage si arrêt de caisse non fait.
     */
    protected function _enforce_caissier_arret_deadline()
    {
        if (!$this->session->userdata('agent') || !$this->session->userdata('company')) {
            return;
        }

        $agent = $this->session->agent;
        if ((string) $agent->userole !== '4') {
            return;
        }

        $gare_id = !empty($agent->guser) ? (string) $agent->guser : '';
        $roleattribut = !empty($agent->roleattribut) ? (int) $agent->roleattribut : 0;
        $status = compte_arret_caissier_pending_status(
            $agent->userole,
            $roleattribut,
            $gare_id
        );
        if (empty($status['blocked'])) {
            return;
        }

        $class = strtolower((string) $this->router->fetch_class());
        $method = strtolower((string) $this->router->fetch_method());

        // Accès minimal : espace caisse / arrêt / validation / déconnexion.
        $allowed = array(
            'login' => array('lout'),
            'caisses' => array(
                'viewcaisprinc', 'options', 'opts', 'optionscaisse',
                'arretcaisseprincipale', 'valversement', 'rejetversement',
            ),
            'utilisateurs' => array(
                'profilcaisse', 'recettecaisse', 'depensecaisse',
                'depotcaisse', 'versemetcaisse',
                'recettecaissecptable', 'depensecaissecptable',
            ),
            'arretcaisses' => array('*'),
        );

        if (isset($allowed[$class])
            && (in_array('*', $allowed[$class], true)
                || in_array($method, $allowed[$class], true))) {
            return;
        }

        if ($class === 'gares'
            && in_array($method, array('options', 'opts', 'optiongare'), true)) {
            return;
        }

        if ($this->input->is_ajax_request()) {
            $this->output->set_status_header(403);
            $this->output->set_content_type('application/json');
            $this->output->set_output(json_encode(array(
                'error' => $status['code'],
                'message' => $status['reason'],
            )));
            exit;
        }

        $this->session->set_flashdata('sale_error', $status['reason']);
        redirect(
            'gares/' . $this->session->company->ekey
            . '/gTv/' . $gare_id
            . '/cais/' . $roleattribut
            . '/0/'
            . mdate('%d/%m/%Y', now('UTC'))
        );
        exit;
    }

    /**
     * Superviseur d'agence (13) : après le jour N, blocage si validations caissier en retard.
     */
    protected function _enforce_sup_agence_validation_deadline()
    {
        if (!$this->session->userdata('agent') || !$this->session->userdata('company')) {
            return;
        }

        $agent = $this->session->agent;
        if ((string) $agent->userole !== '13') {
            return;
        }

        $gare_id = !empty($agent->guser) ? (string) $agent->guser : '';
        $roleattribut = !empty($agent->roleattribut) ? (int) $agent->roleattribut : 0;
        $status = compte_arret_sup_agence_pending_status(
            $agent->userole,
            $roleattribut,
            $gare_id
        );
        if (empty($status['blocked'])) {
            return;
        }

        $class = strtolower((string) $this->router->fetch_class());
        $method = strtolower((string) $this->router->fetch_method());

        $allowed = array(
            'login' => array('lout'),
            'caisses' => array('viewcaisprinc'),
            'utilisateurs' => array(
                'profilcaisse', 'recettecaisse', 'depensecaisse',
                'depotcaisse', 'versemetcaisse',
                'recettecaissecptable', 'depensecaissecptable',
            ),
            'arretcaisses' => array('*'),
        );

        if (isset($allowed[$class])
            && (in_array('*', $allowed[$class], true)
                || in_array($method, $allowed[$class], true))) {
            return;
        }

        if ($class === 'gares'
            && in_array($method, array('options', 'opts', 'optiongare'), true)) {
            return;
        }

        // POST de validation comptable (Caisses).
        if ($class === 'caisses'
            && in_array($method, array(
                'valversement', 'rejetversement',
                'valrecette', 'rejetrecette',
                'valdepense', 'rejetdepense',
                'valdepot', 'rejetdepot',
            ), true)
            && strtoupper((string) $this->input->method()) === 'POST') {
            return;
        }

        if ($this->input->is_ajax_request()) {
            $this->output->set_status_header(403);
            $this->output->set_content_type('application/json');
            $this->output->set_output(json_encode(array(
                'error' => $status['code'],
                'message' => $status['reason'],
            )));
            exit;
        }

        $this->session->set_flashdata('sale_error', $status['reason']);
        $sg = (!empty($agent->idsousgare) || (isset($agent->idsousgare) && (string) $agent->idsousgare === '0'))
            ? (string) $agent->idsousgare
            : '0';
        redirect(
            'caisses/caissieres/' . $this->session->company->ekey
            . '/' . $roleattribut
            . '/' . $gare_id
            . '/' . $sg
        );
        exit;
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
