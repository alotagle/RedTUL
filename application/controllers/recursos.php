<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class recursos extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('instancia_model');
    }

    public function index() {
    	$this->load->view('template/header');
        $this->load->view('template/menu');
        $this->load->view('recursos');
        $this->load->view('template/footer');
    }

}