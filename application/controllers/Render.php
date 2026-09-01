<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Render extends MY_Controller
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
        
        
        public function Barcode($codep = '')
        {
            $codep = rawurldecode((string) $codep);
            $codep = trim($codep);
            if ($codep === '') {
                show_404();
                return;
            }

            // Évite BOM / notices avant le PNG (sinon image cassée à l'impression).
            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            $this->zend->load('Zend/Barcode');
            // Texte sous le code retiré : déjà imprimé sur le ticket.
            // Taille compacte pour ticket thermique, encore scannable.
            Zend_Barcode::render('code128', 'image', array(
                'text' => $codep,
                'barHeight' => 35,
                'factor' => 1,
                'drawText' => false,
                'withQuietZones' => true,
            ));
            exit;
        }

    }
    
    /** End of file: Render.php **/
    /** File location: application/controllers/Render.php **/
