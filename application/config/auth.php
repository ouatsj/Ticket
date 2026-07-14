<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Contrôleurs / routes publics (sans session agent + company).
 */
return array(
  'public_controllers' => array(
    'login',
    'welcome',
    'render',
    'upload',
    'company',
    'cdnhealth',
  ),

  /**
   * class/method (minuscules). Méthode * = toutes.
   * home/go : route publique mais protégée par login_pending dans Home::go().
   */
  'public_methods' => array(
    'home/go',
    'ticket/*',
    'tick/*',
    'rapport/*',
  ),
);
