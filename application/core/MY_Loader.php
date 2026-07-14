<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Précharge les modèles référencés dans une vue avant son rendu.
 */
class MY_Loader extends CI_Loader
{
    /** @var array */
    private static $_view_model_cache = array();

    /**
     * @param string $view
     * @param array  $vars
     * @param bool   $return
     * @return mixed
     */
    public function view($view, $vars = array(), $return = FALSE)
    {
        $this->_preload_view_models($view);

        return parent::view($view, $vars, $return);
    }

    /**
     * @param string $view
     */
    protected function _preload_view_models($view)
    {
        $_ci_file = pathinfo($view, PATHINFO_EXTENSION) === ''
            ? $view . '.php'
            : $view;

        foreach ($this->_ci_view_paths as $view_path => $cascade) {
            $file = $view_path . $_ci_file;

            if (file_exists($file)) {
                $this->_preload_models_from_file($file);
                return;
            }

            if (!$cascade) {
                break;
            }
        }
    }

    /**
     * @param string $file
     */
    protected function _preload_models_from_file($file)
    {
        if (isset(self::$_view_model_cache[$file])) {
            return;
        }

        self::$_view_model_cache[$file] = true;

        if (!is_readable($file)) {
            return;
        }

        if (!$this->_file_contains_model_refs($file)) {
            return;
        }

        $content = file_get_contents($file);

        if (!preg_match_all('/\$this->(m_[a-z0-9_]+)/', $content, $matches)) {
            return;
        }

        $map = MY_Controller::model_map();
        $CI =& get_instance();

        foreach (array_unique($matches[1]) as $alias) {
            if (isset($map[$alias]) && !isset($CI->$alias)) {
                $CI->load->model($map[$alias], $alias);
            }
        }
    }

    /**
     * Détecte $this->m_* sans charger tout le fichier en mémoire (vues > 2 Mo).
     *
     * @param string $file
     * @return bool
     */
    protected function _file_contains_model_refs($file)
    {
        $handle = fopen($file, 'rb');

        if ($handle === false) {
            return false;
        }

        while (!feof($handle)) {
            $chunk = fread($handle, 65536);

            if ($chunk !== false && strpos($chunk, '$this->m_') !== false) {
                fclose($handle);
                return true;
            }
        }

        fclose($handle);

        return false;
    }
}
