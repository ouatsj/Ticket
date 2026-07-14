<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Libère le verrou session en fin de requête (évite les conflits multi-onglets).
 */
function session_release_lock()
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_write_close();
    }
}

/**
 * Enregistre la libération du verrou session à la fin de la requête HTTP.
 */
function session_release_lock_on_shutdown()
{
    static $registered = false;

    if ($registered) {
        return;
    }

    $registered = true;
    register_shutdown_function(function () {
        session_release_lock();

        if (function_exists('get_instance')) {
            $CI =& get_instance();
            if ($CI && isset($CI->db) && is_object($CI->db) && method_exists($CI->db, 'close')) {
                $CI->db->close();
            }
        }
    });
}
