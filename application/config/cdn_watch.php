<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Seuils anti rate-limit hCDN (Hostinger) — trop de requêtes JS → 403.
 */
return array(
    // Scripts /assets/ chargés sur une page (hors inline)
    'script_warn' => 20,
    'script_critical' => 28,

    // Rôles guichet sans bundle fusionné (sources vides dans scripts_bundles_guichet_sources.php)
    'guichet_roles_without_bundle' => array('3', '9', '10', '12', '16', '17'),

    // Taille max d'une ligne de log client (octets)
    'max_log_payload' => 2048,
);
