<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Bundles JS par profil — réduit les requêtes HTTP (évite rate-limit hCDN / 403).
 * Classe CSS bouton = nom du fichier (ex. addventeticket → addventeticket.js).
 */
return array(
    'accueil' => array(),

    'historique' => array('bundles/historique.js'),

    'admin' => array(
        'addperso.js',
        'user-list-filter.js',
        'filtre_arrivee_compagnie.js',
    ),

    'confirmation' => array('bundles/confirmation.js'),

    'bagage' => array('bundles/bagage.js'),

    'program' => array('bundles/program.js'),

    // Fichier fusionné (1 requête HTTP). Sources : scripts_bundles_module_sources.php
    // Regénérer : php scripts/build_module_bundles.php
    'caisse' => array('bundles/caisse.js'),

    // Fichiers fusionnés (1 requête HTTP / rôle). Sources : scripts_bundles_guichet_sources.php
    // Regénérer : php scripts/build_guichet_bundles.php
    'guichet' => array(
        '1' => array('bundles/guichet-1.js'),
        '2' => array('bundles/guichet-2.js'),
        '3' => array(),
        '4' => array('bundles/guichet-4.js'),
        '5' => array('bundles/guichet-5.js'),
        '6' => array('bundles/guichet-6.js'),
        '7' => array('bundles/guichet-7.js'),
        '8' => array('bundles/guichet-8.js'),
        '9' => array(),
        '10' => array(),
        '11' => array('bundles/guichet-11.js'),
        '12' => array(),
        '13' => array('bundles/guichet-13.js'),
        '14' => array('bundles/guichet-14.js'),
        '15' => array('bundles/guichet-15.js'),
        '16' => array(),
        '17' => array(),
        '18' => array('bundles/guichet-18.js'),
        'default' => array('bundles/guichet-default.js'),
    ),
);
