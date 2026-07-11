<?php
/**
 * Bootstrap minimal pour tests CLI auth (sans CodeIgniter, sans MySQL, sans HTTP).
 */
if (!defined('BASEPATH')) {
    define('BASEPATH', dirname(__DIR__, 2) . '/system/');
}

if (!function_exists('get_instance')) {
    function &get_instance()
    {
        return AuthLoginFlowTestHarness::$ci;
    }
}

if (!function_exists('redirect')) {
    function redirect($uri = '', $method = 'auto', $code = null)
    {
        throw new AuthLoginFlowRedirectException((string) $uri);
    }
}

if (!function_exists('site_url')) {
    function site_url($uri = '')
    {
        return 'http://test/' . ltrim((string) $uri, '/');
    }
}

if (!function_exists('log_message')) {
    function log_message($level, $message)
    {
    }
}

if (!function_exists('mdate')) {
    function mdate($format, $time = null)
    {
        return date(str_replace('%Y-%m-%d %H:%i:%s', 'Y-m-d H:i:s', $format), $time ?: time());
    }
}

if (!function_exists('now')) {
    function now($timezone = null)
    {
        return time();
    }
}

class AuthLoginFlowRedirectException extends RuntimeException
{
}

class AuthLoginFlowMockSession
{
    public $session_id = 'test-session-id';
    private $data = array();
    private $flash = array();

    public function set_userdata($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->data[$k] = $v;
            }
            return;
        }

        $this->data[$key] = $value;
    }

    public function userdata($key = null)
    {
        if ($key === null) {
            return $this->data;
        }

        return array_key_exists($key, $this->data) ? $this->data[$key] : null;
    }

    public function unset_userdata($key)
    {
        unset($this->data[$key]);
    }

    public function set_flashdata($key, $value)
    {
        $this->flash[$key] = $value;
    }

    public function flashdata($key)
    {
        return array_key_exists($key, $this->flash) ? $this->flash[$key] : null;
    }

    public function sess_destroy()
    {
        $this->data = array();
    }
}

class AuthLoginFlowMockDb
{
    public $session_token_exists = true;

    public function field_exists($field, $table)
    {
        return $field === 'session_token' && $table === 'compte_user' && $this->session_token_exists;
    }

    public function where($field, $value)
    {
        return $this;
    }

    public function update($table, $data)
    {
        return true;
    }
}

class AuthLoginFlowMockCi
{
    /** @var AuthLoginFlowMockSession */
    public $session;

    /** @var AuthLoginFlowMockDb */
    public $db;

    /** @var object|null */
    public $m_compte_user;

    public function __construct()
    {
        $this->session = new AuthLoginFlowMockSession();
        $this->db = new AuthLoginFlowMockDb();
    }

    public function load($what, $name = null, $return = false)
    {
        if ($what === 'model' && $name === 'Compte_user_model') {
            $this->m_compte_user = new AuthLoginFlowMockCompteUserModel();
        }
    }
}

class AuthLoginFlowMockCompteUserModel
{
    public function active_gare_for_role($cpuser_id, $userole)
    {
        return (object) array(
            'garenom' => 'BOB1',
            'guser' => 'BOB1',
            'idengare' => 'BOB1',
        );
    }
}

class AuthLoginFlowTestHarness
{
    /** @var AuthLoginFlowMockCi */
    public static $ci;

    public static function reset()
    {
        self::$ci = new AuthLoginFlowMockCi();
    }

    public static function loadHelper()
    {
        static $loaded = false;
        if ($loaded) {
            return;
        }

        require dirname(__DIR__, 2) . '/application/helpers/auth_session_helper.php';
        $loaded = true;
    }
}

AuthLoginFlowTestHarness::reset();
AuthLoginFlowTestHarness::loadHelper();
