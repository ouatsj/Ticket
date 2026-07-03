<?php defined('BASEPATH') OR exit('No direct script access allowed');
    
    /**
     * admin.rakieta - json.php
     *
     * An API for RAKIETA ERP Platform
     *
     * This content is released under the MIT License (MIT)
     *
     * Copyright (c) 2020, NET SOLUTIONS
     *
     * Permission is hereby granted, free of charge, to any person obtaining a copy
     * of this software and associated documentation files (the "Software"), to deal
     * in the Software without restriction, including without limitation the rights
     * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
     * copies of the Software, and to permit persons to whom the Software is
     * furnished to do so, subject to the following conditions:
     *
     * The above copyright notice and this permission notice shall be included in
     * all copies or substantial portions of the Software.
     *
     * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
     * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
     * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
     * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
     * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
     * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
     * THE SOFTWARE.
     *
     * @package    rakieta.transports
     * @author     Mandiss 
     * @author     NET SOLUTIONS
     * @copyright  Copyright (c) 2017 - 2020, NET SOLUTIONS, S.A.R.L. (https://www.netsolutionsbf.com/)
     * @license    GPL
     * @link       https://www.rakietabus.net
     * @since      Version 1.0.0
     */
    
    $this->output->set_content_type('application/json');
    
    $this->output
    ->set_output(json_encode(
        $json,
        JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG
    ))
    ->_display();

exit;
