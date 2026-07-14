<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
/**
 * @deprecated Utiliser scripts_bundle.php via scripts_helper.
 */
$bundle_js = array(
    'addventeticket.js',
    'addventeticketfi.js',
    'addventemobile.js',
    'addconfirme.js',
    'addreprogramme.js',
    'addreprogrammetransit.js',
    'addreprogadmin.js',
    'addconfirmadmin.js',
    'addconfirmadmintran.js',
    'addreserve.js',
    'addretour.js',
    'addrecu.js',
    'addbon.js',
    'addcarte.js',
    'addconfirmbon.js',
);
$this->load->view('_layouts/scripts_bundle', array('bundle_js' => $bundle_js));
