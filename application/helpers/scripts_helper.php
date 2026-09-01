<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @return array
 */
function scripts_bundles_config()
{
    static $config = null;

    if ($config === null) {
        $config = require APPPATH . 'config/scripts_bundles.php';
    }

    return $config;
}

/**
 * @param string $bundle  accueil|program|caisse|guichet
 * @param string|null $role
 * @return array
 */
function scripts_bundle_modules($bundle, $role = null)
{
    $config = scripts_bundles_config();

    if (!isset($config[$bundle])) {
        return array();
    }

    if ($bundle === 'guichet') {
        $role = (string) $role;
        $guichet_boot = array('guichet-defer-dom.js', 'guichet-load-scheduler.js');

        if (isset($config['guichet'][$role])) {
            return array_merge($guichet_boot, $config['guichet'][$role]);
        }

        return array_merge($guichet_boot, $config['guichet']['default']);
    }

    return $config[$bundle];
}

/**
 * @param string $bundle
 * @param string|null $role
 * @return array scripts_layout + bundle_js
 */
function scripts_bundle_property($bundle, $role = null, $datatables = false)
{
    return array(
        'scripts_layout' => 'scripts_bundle',
        'bundle_js' => scripts_bundle_modules($bundle, $role),
        'bundle_datatables' => (bool) $datatables,
    );
}

/**
 * Vue guichet allégée par rôle (découpage de l'ancien index.php monolithique).
 *
 * @param int|string $role
 * @return string chemin relatif sous beagle/pages/
 */
function guichet_page_for_role($role)
{
    $role = (int) $role;
    $page = 'guichet/role_' . $role;
    $path = APPPATH . 'views/beagle/pages/' . $page . '.php';

    if (is_file($path)) {
        return $page;
    }

    return 'guichet/role_default';
}

/**
 * Applique le bundle JS par défaut du contrôleur si non déjà défini.
 *
 * @param array $pdata
 * @return array
 */
function scripts_resolve_layout(array $pdata)
{
    if (isset($pdata['scripts_layout'])) {
        return $pdata;
    }

    $CI =& get_instance();
    $map = require APPPATH . 'config/controller_scripts.php';
    $class = strtolower($CI->router->fetch_class());
    $spec = isset($map[$class]) ? $map[$class] : $map['_default'];

    if ($spec === false || $spec === null) {
        return array_merge(array(
            'scripts_layout' => 'scripts_bundle',
            'bundle_js' => array(),
            'bundle_datatables' => false,
        ), $pdata);
    }

    if (is_string($spec)) {
        $spec = array('bundle' => $spec);
    }

    $bundle = $spec['bundle'];
    $role = null;
    if (!empty($spec['use_role']) && $CI->session->userdata('agent')) {
        $role = $CI->session->agent->userole;
    }

    $resolved = scripts_bundle_property(
        $bundle,
        $role,
        !empty($spec['datatables'])
    );

    return array_merge($resolved, $pdata);
}
