<?php defined('BASEPATH') OR exit('No direct script access allowed');

/*
 * Interrupteur de sécurité.
 *
 * TRUE  : les comptes SuperAdmin et les permissions granulaires sont actifs.
 * FALSE : l'application conserve les règles historiques des rôles.
 *
 * En production, ce fichier sera livré avec FALSE puis activé après validation.
 */
$config['super_admin_enabled'] = TRUE;

