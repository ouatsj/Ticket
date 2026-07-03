<?php
    
    defined('BASEPATH') OR exit('No direct script access allowed');
    
    class Layout
    {
        protected $theme = 'beagle';
        
        public function set_theme($t)
        {
            $this->theme = $t;
        }
        
        public function view($page, array $pdata)
        {
            $CI =& get_instance();
            $params['cfl'] = $CI->load->view($this->theme . '/pages/' .
                $page, $pdata, TRUE);
            $CI->load->view($this->theme . '/use', $params);
        }
    }
    
    /* End of file: Layout.php */
    /* File location: application/libraries/Layout.php */