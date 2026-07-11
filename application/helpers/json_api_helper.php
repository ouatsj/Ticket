<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Réponse JSON légère (libère le verrou session avant l'envoi).
 *
 * @param mixed $data
 */
function json_api_response($data)
{
    $CI =& get_instance();
    session_release_lock();

    $CI->output
        ->set_content_type('application/json')
        ->set_output(json_encode(
            $data,
            JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG
        ))
        ->_display();

    exit;
}
