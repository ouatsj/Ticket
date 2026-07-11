<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Routeur léger — le contenu guichet est dans guichet/role_{userole}.php
 * (découpage de l'ancien fichier monolithique ~2,2 Mo).
 */
$this->load->helper('scripts');
$this->load->view('beagle/pages/' . guichet_page_for_role($this->session->agent->userole));
