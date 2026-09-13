<?php
/**
 * Harness CI minimal pour appeler Chemins_programmes_vente / modèles en CLI.
 * Usage via scripts/tests/reprog_matrix_test.php --allow-remote
 */
if (!defined('BASEPATH')) {
    define('BASEPATH', dirname(__DIR__, 2) . '/system/');
}
if (!defined('APPPATH')) {
    define('APPPATH', dirname(__DIR__, 2) . '/application/');
}
if (!defined('ENVIRONMENT')) {
    define('ENVIRONMENT', 'development');
}

require_once BASEPATH . 'database/DB.php';
require_once BASEPATH . 'core/Model.php';

if (!function_exists('log_message')) {
    function log_message($level, $message)
    {
    }
}
if (!function_exists('get_config')) {
    function &get_config(array $replace = array())
    {
        static $config = array();
        return $config;
    }
}
if (!function_exists('config_item')) {
    function config_item($item)
    {
        return null;
    }
}
if (!function_exists('show_error')) {
    function show_error($message, $status_code = 500, $heading = 'Error')
    {
        throw new RuntimeException((string) $message);
    }
}
if (!function_exists('mdate')) {
    function mdate($format, $time = '')
    {
        if ($time === '') {
            $time = time();
        }
        $format = str_replace(
            array('%Y', '%m', '%d', '%H', '%i', '%s'),
            array('Y', 'm', 'd', 'H', 'i', 's'),
            $format
        );
        return date($format, (int) $time);
    }
}
if (!function_exists('now')) {
    function now($timezone = null)
    {
        return time();
    }
}

class ReprogTestLoader
{
    /** @var object */
    public $ci;

    public function __construct($ci)
    {
        $this->ci = $ci;
    }

    public function model($name, $alias = null)
    {
        $file = APPPATH . 'models/' . $name . '.php';
        require_once $file;
        $class = $name;
        if (!class_exists($class, false)) {
            // CodeIgniter models are named Xxx_model
            $class = $name;
        }
        $obj = new $class();
        $key = $alias ? $alias : strtolower($name);
        // Convention CI : Passager_model → m_passager sometimes via map; here use alias.
        $this->ci->$key = $obj;
        return $obj;
    }

    public function library($name, $params = null, $object_name = null)
    {
        $file = APPPATH . 'libraries/' . ucfirst($name) . '.php';
        if (!is_file($file)) {
            $file = APPPATH . 'libraries/' . $name . '.php';
        }
        require_once $file;
        $class = ucfirst($name);
        if (!class_exists($class, false)) {
            $class = $name;
        }
        $obj = new $class();
        $key = $object_name ? $object_name : strtolower($name);
        $this->ci->$key = $obj;
        return $obj;
    }

    public function helper($name)
    {
        $file = APPPATH . 'helpers/' . $name . '_helper.php';
        if (is_file($file)) {
            require_once $file;
        }
    }
}

class ReprogTestCI
{
    /** @var CI_DB_mysqli_driver|object */
    public $db;
    /** @var ReprogTestLoader */
    public $load;
    public $session;
    public $config;
    public $input;

    public function __construct($db)
    {
        $this->db = $db;
        $this->load = new ReprogTestLoader($this);
        $this->session = (object) array(
            'company' => (object) array('ekey' => '1000'),
            'agent' => (object) array('userole' => '1', 'cpuser_id' => 1),
        );
        $this->config = new class {
            public function item($k)
            {
                return null;
            }
        };
        $this->input = new class {
            public function get_post($k)
            {
                return null;
            }
            public function post($k)
            {
                return null;
            }
            public function get($k)
            {
                return null;
            }
        };
    }
}

function &get_instance()
{
    global $REPROG_TEST_CI;
    return $REPROG_TEST_CI;
}

function reprog_test_boot_ci(mysqli $mysqli)
{
    global $REPROG_TEST_CI;
    // Adapter mysqli → faux CI db (query retourne objet avec result/row).
    $db = new class($mysqli) {
        private $m;
        public function __construct(mysqli $m)
        {
            $this->m = $m;
        }
        public function escape_str($s)
        {
            return $this->m->real_escape_string((string) $s);
        }
        public function escape($s)
        {
            if ($s === null) {
                return 'NULL';
            }
            if (is_bool($s)) {
                return $s ? 1 : 0;
            }
            if (is_int($s) || is_float($s)) {
                return $s;
            }
            return "'" . $this->m->real_escape_string((string) $s) . "'";
        }
        public function query($sql, $binds = false)
        {
            if (is_array($binds)) {
                foreach ($binds as $b) {
                    if ($b === null) {
                        $rep = 'NULL';
                    } elseif (is_int($b) || is_float($b)) {
                        $rep = (string) $b;
                    } else {
                        $rep = "'" . $this->m->real_escape_string((string) $b) . "'";
                    }
                    $sql = preg_replace('/\?/', $rep, $sql, 1);
                }
            }
            $res = $this->m->query($sql);
            if ($res === false) {
                return new class($this->m->error) {
                    private $err;
                    public function __construct($e)
                    {
                        $this->err = $e;
                    }
                    public function result()
                    {
                        return array();
                    }
                    public function row()
                    {
                        return null;
                    }
                    public function result_array()
                    {
                        return array();
                    }
                };
            }
            if ($res === true) {
                return true;
            }
            return new class($res) {
                private $res;
                public function __construct($r)
                {
                    $this->res = $r;
                }
                public function result()
                {
                    $out = array();
                    while ($row = $this->res->fetch_object()) {
                        $out[] = $row;
                    }
                    return $out;
                }
                public function row()
                {
                    $row = $this->res->fetch_object();
                    return $row ? $row : null;
                }
                public function result_array()
                {
                    $out = array();
                    while ($row = $this->res->fetch_assoc()) {
                        $out[] = $row;
                    }
                    return $out;
                }
                public function num_rows()
                {
                    return $this->res->num_rows;
                }
            };
        }
        public function insert_id()
        {
            return $this->m->insert_id;
        }
        public function affected_rows()
        {
            return $this->m->affected_rows;
        }
    };

    $REPROG_TEST_CI = new ReprogTestCI($db);
    // Helpers utilisés par les libs
    if (is_file(APPPATH . 'helpers/ticket_prix_helper.php')) {
        require_once APPPATH . 'helpers/ticket_prix_helper.php';
    }
    $REPROG_TEST_CI->load->model('Programme_model', 'm_programme');
    $REPROG_TEST_CI->load->model('Programme_correspondance_model', 'm_programme_correspondance');
    $REPROG_TEST_CI->load->library('Chemins_programmes_vente', null, 'chemins_programmes_vente');
    return $REPROG_TEST_CI;
}
