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
            $CI->load->helper('scripts');
            $CI->load->helper('retour');
            retour_page_remember();
            if (function_exists('session_release_lock')) {
                session_release_lock();
            }
            $pdata = scripts_resolve_layout($pdata);

            if (function_exists('auth_session_is_guichet_page')
                && auth_session_is_guichet_page($page)) {
                auth_session_send_nocache_headers();
            } elseif (function_exists('auth_session_show_guichet_banner')
                && auth_session_show_guichet_banner($page)) {
                auth_session_send_nocache_headers();
            }

            $params['cfl'] = $CI->load->view($this->theme . '/pages/' .
                $page, $pdata, TRUE);
            $params['scripts_layout'] = $pdata['scripts_layout'];
            $params['bundle_js'] = isset($pdata['bundle_js']) && is_array($pdata['bundle_js'])
                ? $pdata['bundle_js']
                : array();
            $params['bundle_optional_js'] = isset($pdata['bundle_optional_js']) && is_array($pdata['bundle_optional_js'])
                ? $pdata['bundle_optional_js']
                : array();
            $params['bundle_datatables'] = !empty($pdata['bundle_datatables']);
            $params['title'] = isset($pdata['title']) ? $pdata['title'] : '';
            $params['layout_minimal'] = !empty($pdata['layout_minimal']);
            $params['layout_page'] = $page;
            $params['layout_guichet_banner'] = function_exists('auth_session_show_guichet_banner')
                && auth_session_show_guichet_banner($page);
            $CI->load->view($this->theme . '/use', $params);
        }
    }
    
    /* End of file: Layout.php */
    /* File location: application/libraries/Layout.php */