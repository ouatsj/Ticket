<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Render extends CI_Controller
    {
        public $property = array(
            'title' => 'Historiques',
            'UPDATE_SUCCESS' => FALSE,
            'INSERT_SUCCESS' => FALSE,
        );
        public $company;
        
        
        public function __construct()
        {
            parent::__construct();
            setlocale(LC_TIME, 'fr_FR', 'fra');
            $this->load->library('Zend');
            $this->property['pagetitle'] = utf8_encode(strftime("%d %b %G", now()));
        }
        
        /**
         *
         */
        
        
        public function Barcode($codep)
        {
            $this->zend->load('Zend/Barcode');
            Zend_Barcode::render('code128', 'image', array('text' => $codep));

        }

    }
    
    /** End of file: Render.php **/
    /** File location: application/controllers/Render.php **/
