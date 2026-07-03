<?php

class Upload extends CI_Controller {

        public function __construct()
        {
                parent::__construct();
                $this->load->helper(array('form', 'url'));
        }

        public function index()
        {
                $this->load->view('upload_form', array('error' => ' ' ));
                	//$this->do_upload();
         
        }

        public function do_upload()
        {
                $config['upload_path']          = '././image';
                $config['allowed_types']        = 'gif|jpg|png';
                $config['max_size']             = 1000;
                $config['max_width']            = 1024;
                $config['max_height']           = 768;
                $config['file_name']            ='ma_boutique';
                $config['overwrite']                    =TRUE;
                $config['encrypt_name']         =TRUE;

                $this->load->library('upload', $config);
                $this->upload->initialize($config);

                if ( ! $this->upload->do_upload('userfile'))
                {
                        $error = array('error' => $this->upload->display_errors());

                        $this->load->view('upload_form', $error);
                }
                else
                {
                        $data = array('upload_data' => $this->upload->data('file_name'));

                        $this->load->view('upload_success', $data);
                        echo $data['upload_data'];
                }
        }
}
?>