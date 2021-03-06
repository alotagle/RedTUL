<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class inicio extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('inicio_model');
    }

    public function index() {

    	$this->load->view('template/header');
        $this->load->view('template/menu');
        $this->load->view('inicio');
        $this->load->view('template/footer');
    }

    function consulta_cursos()
    {
        $cursos = $this->inicio_model->consulta_cursos();

        if ($cursos) {
            foreach($cursos as $llave => &$curso)
            {
                if ($curso['curso_tipo'] == '0')
                {
                    $curso['curso_tipo'] = 'Presencial';
                }else{
                    $curso['curso_tipo'] = 'En línea';
                }

                if ($curso['curso_cupo'] != '0') {
                    $curso_inscritos = $this->inicio_model->contar_inscritos($curso['id_curso']);
                    $curso['curso_cupo_disponible'] = $curso['curso_cupo'] - $curso_inscritos;
                    $curso['curso_inscritos'] = $curso_inscritos;
                }else{
                    $curso['curso_cupo_disponible'] = "No se registró cupo";
                    $curso['curso_inscritos'] = "No se registró cupo";
                }
            }
        }

        print_r(json_encode($cursos));
    }

    function consulta_contactos()
    {
        $contactos = $this->inicio_model->consulta_contactos();

        print_r(json_encode($contactos));
    }
}
