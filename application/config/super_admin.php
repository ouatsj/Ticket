<?php defined('BASEPATH') OR exit('No direct script access allowed');

/*
 * Interrupteur de sécurité.
 *
 * TRUE  : les comptes SuperAdmin et les permissions granulaires sont actifs.
 * FALSE : l'application conserve les règles historiques des rôles.
 *
 * Activé uniquement sur essaiticket ; reste FALSE en production.
 */
$ci_http_host = isset($_SERVER['HTTP_HOST']) ? strtolower((string) $_SERVER['HTTP_HOST']) : '';
$config['super_admin_enabled'] = ($ci_http_host === 'essaiticket.rakietabus.com');
