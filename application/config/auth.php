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
   */
  'public_methods' => array(
    'home/go',
    'home/main1',
    'ticket/*',
    'tick/*',
    'rapport/*',
  ),
);
