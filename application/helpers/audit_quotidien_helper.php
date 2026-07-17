<?php defined('BASEPATH') OR exit('No direct script access allowed');

/*
 * L'environnement essaiticket réutilise temporairement le moteur d'audit déjà
 * présent dans ticket. Ce fichier de pont ne sera pas déployé en production :
 * production possède son propre helper complet.
 */
$productionHelper = dirname(dirname(dirname(__DIR__)))
    . '/ticket/application/helpers/audit_quotidien_helper.php';

if (!is_file($productionHelper)) {
    show_error('Le moteur d’audit de référence est introuvable.', 503);
}

require_once $productionHelper;

