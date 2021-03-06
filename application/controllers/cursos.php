<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class cursos extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('curso_model');
        $this->load->helper('file');
    }

    public function index() {
        $datos = array('num_cursos' => $this->curso_model->paginacion_contar_cursos());

    	$this->load->view('template/header');
        $this->load->view('template/menu');
        $this->load->view('cursos', $datos);
        $this->load->view('template/footer');
    }

    public function nuevo_curso() {
        $this->load->view('template/header');
        $this->load->view('template/menu');
        $this->load->view('nuevo_curso');
        $this->load->view('template/footer');
    }

    public function nuevo_evento() {
        $this->load->view('template/header');
        $this->load->view('template/menu');
        $this->load->view('nuevo_evento');
        $this->load->view('template/footer');
    }

    function registrar_curso() {
        if (!empty($_POST)) {

            $respuesta_temario = $this->subir_temario();

            if ($_FILES['curso_flyer']['size'] == 0) {
                $respuesta_flyer = "";
            }else{
                $respuesta_flyer = $this->subir_flyer();
            }

            if($respuesta_temario != "error_subida" && $respuesta_flyer != "error_subida") {
                $nuevo_curso = array(
                    'curso_titulo'              => $this->input->post('curso_titulo'),
                    'curso_flyer'               => $respuesta_flyer,
                    'curso_tipo'                => $this->input->post('curso_tipo'),
                    'curso_descripcion'         => $this->input->post('curso_descripcion'),
                    'curso_objetivos'           => $this->input->post('curso_objetivos'),
                    'curso_temario'             => $respuesta_temario,
                    'curso_fecha_inicio'        => $this->input->post('curso_fecha_inicio'),
                    'curso_fecha_fin'           => $this->input->post('curso_fecha_fin'),
                    'curso_hora_inicio'         => $this->input->post('curso_hora_inicio'),
                    'curso_hora_fin'            => $this->input->post('curso_hora_fin'),
                    'curso_cupo'                => $this->input->post('curso_cupo'),
                    'curso_ubicacion'           => $this->input->post('curso_ubicacion'),
                    'curso_mapa_url'            => $this->input->post('curso_url_ubicacion'),
                    'curso_telefono'            => $this->input->post('curso_telefono'),
                    'curso_telefono_extension'  => $this->input->post('curso_telefono_extension'),
                    'curso_estatus'             => 2,
                    'curso_evento'              => $this->input->post('curso_evento'),
                    'curso_modalidad'           => $this->input->post('curso_modalidad'),
                    'curso_entidad'             => $this->input->post('curso_entidad'),
                    'curso_costo'               => $this->input->post('curso_costo')
                );

                if ($nuevo_curso['curso_cupo'] == "") {
                    $nuevo_curso['curso_cupo'] = 0;
                }

                $id_curso_creado = $this->curso_model->registrar_curso($nuevo_curso);

                if ($this->input->post('curso_instructor')) {
                    foreach ($this->input->post('curso_instructor') as $key => $value) {
                        $this->curso_model->registrar_instructor_curso($id_curso_creado, $value);
                    }
                }

                $_POST = array();

                $enviar = array('id_curso' => $id_curso_creado);

                $this->editar($enviar);
            }
        }else{
            redirect(site_url("error404"));
        }
    }

    function subir_temario()
    {
        $config['upload_path'] = './assets/temarios_cursos/';
        $config['allowed_types'] = 'pdf';
        $config['max_size'] = '5120';
        $config['encrypt_name'] = TRUE;

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload("curso_temario"))
        {
            echo $this->upload->display_errors();
            return "error_subida";
        }else{
            $datos = $this->upload->data();
            return $datos["file_name"];
        }
    }

    function subir_flyer()
    {
        $config['upload_path'] = './assets/flyers_cursos/';
        $config['allowed_types'] = 'gif|jpg';
        $config['max_size'] = '2048';
        $config['encrypt_name'] = TRUE;

        $this->load->library('upload', $config);

        $this->upload->initialize($config);

        if (!$this->upload->do_upload("curso_flyer"))
        {
            echo $this->upload->display_errors();
            return "error_subida";
        }else{
            $datos = $this->upload->data();
            return $datos["file_name"];
        }
    }

    function consulta_cursos()
    {
        $cursos = $this->curso_model->consulta_cursos();

        if ($cursos) {
            foreach($cursos as $llave => &$curso)
            {
                if ($curso['curso_tipo'] == '0')
                {
                    $curso['curso_tipo'] = 'Interno';
                }else{
                    $curso['curso_tipo'] = 'Externo';
                }

                if ($curso['curso_cupo'] == '0') {
                    $curso['curso_cupo'] = "No se registró cupo";
                    $curso['curso_cupo_disponible'] = "No se registró cupo";
                }else{
                    $curso_inscritos = $this->curso_model->contar_inscritos($curso['id_curso']);
                    $curso['curso_cupo_disponible'] = $curso['curso_cupo'] - $curso_inscritos;
                }

                $curso['curso_instructor'] = $this->curso_model->consulta_instructores_nombre_curso($curso['id_curso']);
            }
        }
        print_r(json_encode($cursos));
    }

    function eliminar($id_curso)
    {
        $nombre_archivos = $this->curso_model->eliminar($id_curso);

        if ($nombre_archivos['curso_flyer'] != "") {
            $ruta_flyer = 'assets/flyers_cursos/'.$nombre_archivos['curso_flyer'];
            unlink($ruta_flyer);
        }

        if ($nombre_archivos['curso_temario'] != "") {
            $ruta_temario = 'assets/temarios_cursos/'.$nombre_archivos['curso_temario'];
            unlink($ruta_temario);
        }

        redirect('cursos');
    }

    function buscar()
    {
        if (!empty($_POST)) {

            $parametros_busqueda = array(
                'nombre_curso'      => $this->input->post('nombre_curso'),
                'tipo_curso'        => $this->input->post('tipo_curso'),
                'estatus_curso'     => $this->input->post('estatus_curso'),
                'inicio_curso'      => $this->input->post('inicio_curso'),
                'fin_curso'         => $this->input->post('fin_curso'),
                'modalidad_curso'   => $this->input->post('modalidad_curso')
            );

            $nombre_completo = $this->input->post('instructor_curso');
            if ($nombre_completo != "") {
                $arreglo_nombre = explode(" ", $nombre_completo);
                $tamano_arreglo = count($arreglo_nombre);

                switch ($tamano_arreglo) {
                    case 1:
                        $parametros_busqueda['nombre_instructor'] = $arreglo_nombre[0];
                        $parametros_busqueda['paterno_instructor'] = "";
                        $parametros_busqueda['materno_instructor'] = "";
                        break;

                    case 2:
                        $parametros_busqueda['nombre_instructor'] = $arreglo_nombre[0];
                        $parametros_busqueda['paterno_instructor'] = $arreglo_nombre[1];
                        $parametros_busqueda['materno_instructor'] = "";
                        break;

                    case 3:
                        $parametros_busqueda['nombre_instructor'] = $arreglo_nombre[0];
                        $parametros_busqueda['paterno_instructor'] = $arreglo_nombre[1];
                        $parametros_busqueda['materno_instructor'] = $arreglo_nombre[2];
                        break;

                    case 4:
                        $parametros_busqueda['nombre_instructor'] = $arreglo_nombre[0]." ".$arreglo_nombre[1];
                        $parametros_busqueda['paterno_instructor'] = $arreglo_nombre[2];
                        $parametros_busqueda['materno_instructor'] = $arreglo_nombre[3];
                        break;
                    
                    default:
                        $parametros_busqueda['nombre_instructor'] = "";
                        $parametros_busqueda['paterno_instructor'] = "";
                        $parametros_busqueda['materno_instructor'] = "";
                        break;
                }
            } else {
                $parametros_busqueda['nombre_instructor'] = "";
                $parametros_busqueda['paterno_instructor'] = "";
                $parametros_busqueda['materno_instructor'] = "";
            }

            $cursos = $this->curso_model->buscar($parametros_busqueda);

            if ($cursos) {
                foreach($cursos as $llave => &$curso)
                {
                    if ($curso['curso_tipo'] == '0')
                    {
                        $curso['curso_tipo'] = 'Interno';
                    }else{
                        $curso['curso_tipo'] = 'Externo';
                    }

                    $curso['curso_instructor'] = $this->curso_model->consulta_instructores_nombre_curso($curso['id_curso']);

                    if ($curso['curso_cupo'] == '0') {
                        $curso['curso_cupo'] = "No se registró cupo";
                        $curso['curso_cupo_disponible'] = "No se registró cupo";
                    }else{
                        $curso_inscritos = $this->curso_model->contar_inscritos($curso['id_curso']);
                        $curso['curso_cupo_disponible'] = $curso['curso_cupo'] - $curso_inscritos;
                    }
                }
            }
            print_r(json_encode($cursos));
        }
    }

    function editar($id_curso)
    {
        if (empty($_POST)) {

            if (is_array($id_curso)) {
                $var_id = $id_curso;
                $var_id["mensaje_guardado"] = 1;
            }else{
                $var_id = array('id_curso' => $id_curso, 'mensaje_guardado' => 0);
            }

            $this->load->view('template/header');
            $this->load->view('template/menu');
            $this->load->view('editar_curso', $var_id);
            $this->load->view('template/footer');
        }else{

            $nombre_archivos_antiguos = $this->curso_model->eliminar_archivos($id_curso);

            if ($_FILES['curso_temario']['size'] == 0) {
                $respuesta_temario = $this->input->post('temario_anterior');
            }else{
                if ($nombre_archivos_antiguos['curso_temario'] != "") {
                    $ruta_temario = 'assets/temarios_cursos/'.$nombre_archivos_antiguos['curso_temario'];
                    unlink($ruta_temario);
                }
                $respuesta_temario = $this->subir_temario();
            }

            if ($_FILES['curso_flyer']['size'] == 0) {
                $respuesta_flyer = $this->input->post('flyer_anterior');
            }else{
                if ($nombre_archivos_antiguos['curso_flyer'] != "") {
                    $ruta_flyer = 'assets/flyers_cursos/'.$nombre_archivos_antiguos['curso_flyer'];
                    unlink($ruta_flyer);
                }
                $respuesta_flyer = $this->subir_flyer();
            }

            $editar_curso = array(
                'curso_titulo'              => $this->input->post('curso_titulo'),
                'curso_flyer'               => $respuesta_flyer,
                'curso_tipo'                => $this->input->post('curso_tipo'),
                'curso_descripcion'         => $this->input->post('curso_descripcion'),
                'curso_objetivos'           => $this->input->post('curso_objetivos'),
                'curso_temario'             => $respuesta_temario,
                'curso_fecha_inicio'        => $this->input->post('curso_fecha_inicio'),
                'curso_fecha_fin'           => $this->input->post('curso_fecha_fin'),
                'curso_hora_inicio'         => $this->input->post('curso_hora_inicio'),
                'curso_hora_fin'            => $this->input->post('curso_hora_fin'),
                'curso_cupo'                => $this->input->post('curso_cupo'),
                'curso_ubicacion'           => $this->input->post('curso_ubicacion'),
                'curso_mapa_url'            => $this->input->post('curso_url_ubicacion'),
                'curso_telefono'            => $this->input->post('curso_telefono'),
                'curso_telefono_extension'  => $this->input->post('curso_telefono_extension'),
                'curso_evento'              => $this->input->post('curso_evento'),
                'curso_modalidad'           => $this->input->post('curso_modalidad'),
                'curso_entidad'             => $this->input->post('curso_entidad'),
                'curso_costo'               => $this->input->post('curso_costo')
            );

            $editar_curso['id_curso'] = $id_curso;

            $this->curso_model->editar_curso($editar_curso);

            $this->curso_model->borrar_instructor_curso($id_curso);

            foreach ($this->input->post('curso_instructor') as $key => $value) {
                $this->curso_model->registrar_instructor_curso($id_curso, $value);
            }

            $_POST = array();

            $enviar = array('id_curso' => $id_curso);

            $this->editar($enviar);
        }
    }

    function consulta_curso($id_curso)
    {
        $curso = $this->curso_model->consulta_curso($id_curso);

        print_r(json_encode($curso));
    }

    function consulta_instructores()
    {
        $instructores = $this->curso_model->consulta_instructores();

        print_r(json_encode($instructores));
    }

    function consulta_contactos()
    {
        $parametros = array(
            'correo'    => $this->input->post('correo'),
            'instancia' => $this->input->post('instancia'),
            'id_curso'  => $this->input->post('id_curso')
        );

        $nombre_completo = $this->input->post('nombre');

        if ($nombre_completo != "") {
            $arreglo_nombre = explode(" ", $nombre_completo);
            $tamano_arreglo = count($arreglo_nombre);

            switch ($tamano_arreglo) {
                case 1:
                    $parametros['nombre_contacto'] = $arreglo_nombre[0];
                    $parametros['paterno_contacto'] = "";
                    $parametros['materno_contacto'] = "";
                    break;

                case 2:
                    $parametros['nombre_contacto'] = $arreglo_nombre[0];
                    $parametros['paterno_contacto'] = $arreglo_nombre[1];
                    $parametros['materno_contacto'] = "";
                    break;

                case 3:
                    $parametros['nombre_contacto'] = $arreglo_nombre[0];
                    $parametros['paterno_contacto'] = $arreglo_nombre[1];
                    $parametros['materno_contacto'] = $arreglo_nombre[2];
                    break;

                case 4:
                    $parametros['nombre_contacto'] = $arreglo_nombre[0]." ".$arreglo_nombre[1];
                    $parametros['paterno_contacto'] = $arreglo_nombre[2];
                    $parametros['materno_contacto'] = $arreglo_nombre[3];
                    break;
                
                default:
                    $parametros['nombre_contacto'] = "";
                    $parametros['paterno_contacto'] = "";
                    $parametros['materno_contacto'] = "";
                    break;
            }
        } else {
            $parametros['nombre_contacto'] = "";
            $parametros['paterno_contacto'] = "";
            $parametros['materno_contacto'] = "";
        }

        $resultado_total = $this->curso_model->consulta_contactos($parametros);

        $invitados = $this->curso_model->consulta_invitado_curso($parametros['id_curso']);

        if (!is_null($invitados) && !is_null($resultado_total)) {
            foreach ($invitados as $key_invitados => $id_invitado) {
                foreach ($resultado_total as $key => &$contacto) {
                    if ($id_invitado['invitado_id'] == $contacto['id_contacto']) {
                        unset($resultado_total[$key]);
                    }
                }
            }
        }

        print_r(json_encode($resultado_total));
    }

    function agrega_invitados()
    {
        var_dump($_POST);
        $id_curso = $this->input->post('id_curso');

        $this->curso_model->borrar_invitado_tipo($id_curso);

        if ($this->input->post('tecnico')) {
            $this->curso_model->registrar_invitado_tipo($id_curso, 0);
        }

        if ($this->input->post('comunicacion')) {
            $this->curso_model->registrar_invitado_tipo($id_curso, 1);
        }

        if ($this->input->post('invitados')) {
            foreach ($this->input->post('invitados') as $key => $value) {
                $this->curso_model->registrar_invitado_contacto($id_curso, $value);
            }
        }
    }

    function consulta_invitado_tipo($id_curso)
    {
        $resultado = $this->curso_model->consulta_invitado_tipo($id_curso);

        print_r(json_encode($resultado));
    }

    function consulta_invitado_contacto($id_curso)
    {
        $resultado = $this->curso_model->consulta_invitado_contacto($id_curso);

        print_r(json_encode($resultado));
    }

    function consulta_instructores_curso($id_curso)
    {
        $instructores = $this->curso_model->consulta_instructores_curso($id_curso);

        print_r(json_encode($instructores));
    }

    function borrar_invitado_contacto(){
        $id_curso = $this->input->post('curso');
        $id_contacto = $this->input->post('contacto');

        $this->curso_model->borrar_invitado_contacto($id_curso, $id_contacto);
    }

    function detalle_curso($id_curso)
    {
        $curso = $this->curso_model->consulta_detalle_curso($id_curso);

        if (($curso['curso_flyer']) != "") {
            $tag_img = "<img src=".base_url('assets/flyers_cursos/')."/".$curso['curso_flyer']." width='200px'>";
            $curso['curso_flyer'] = $tag_img;
        }

        if ($curso['curso_tipo'] == 0) {
            $curso['curso_tipo'] = "Interno";
        }else{
            $curso['curso_tipo'] = "Externo";
        }

        if ($curso['curso_modalidad'] == 0) {
            $curso['curso_modalidad'] = "Presencial";
        }else{
            $curso['curso_modalidad'] = "En línea";
        }

        if ($curso['curso_costo'] == 0) {
            $curso['curso_costo'] = "No";
        }else{
            $curso['curso_costo'] = "Sí";
        }

        $tag_a = "<a href=".base_url('assets/temarios_cursos/')."/".$curso['curso_temario']." target='_blank'>Ver temario</a>";
        $curso['curso_temario'] = $tag_a;

        if (($curso['curso_mapa_url']) != "") {
            $tag_url = "<a href=".$curso['curso_mapa_url']." class='conf_contacto_valores' target='_blank'>Ver en Google Maps</a>";
            $curso['curso_mapa_url'] = $tag_url;
        }

        $fecha_inicio_bd = explode("-", $curso['curso_fecha_inicio']);
        $curso['curso_fecha_inicio'] = $fecha_inicio_bd[2]."/".$fecha_inicio_bd[1]."/".$fecha_inicio_bd[0];

        $fecha_fin_bd = explode("-", $curso['curso_fecha_fin']);
        $curso['curso_fecha_fin'] = $fecha_fin_bd[2]."/".$fecha_fin_bd[1]."/".$fecha_fin_bd[0];

        $curso['profesor'] = $this->curso_model->consulta_instructores_nombre_curso($curso['id_curso']);

        $curso['invitados_tipo'] = $this->curso_model->consulta_invitado_tipo_detalle($curso['id_curso']);

        $curso['contactos_estado_curso'] = $this->curso_model->consulta_contactos_detalle_curso($curso['id_curso']);

        foreach ($curso as $campo => $valor) {
            if ($curso[$campo] == "") {
                $curso[$campo] = "-";
            }
        }

        $curso['registro'] = $this->curso_model->consulta_registro_curso($curso['id_curso']);

        if (!is_null($curso['registro'])) {
            foreach ($curso['registro'] as $campo => $valor) {
                if ($valor == "1") {
                    $curso['registro'][$campo] = "Visible";
                }elseif($valor == "0"){
                    $curso['registro'][$campo] = "No visible";
                }
            }
        }

        $this->load->view('template/header');
        $this->load->view('template/menu');
        $this->load->view('detalle_curso', $curso);
        $this->load->view('template/footer');
    }

    function consulta_instancias()
    {
        $instancias = $this->curso_model->consulta_instancias();

        print_r(json_encode($instancias));
    }

    function registra_configuracion(){

        $parametros = array(
            'registro_curso_titulo'             => $this->input->post('configuracion_curso_titulo'),
            'registro_curso_flyer'              => $this->input->post('configuracion_curso_flyer'),
            'registro_curso_tipo'               => $this->input->post('configuracion_curso_tipo'),
            'registro_curso_descripcion'        => $this->input->post('configuracion_curso_descripcion'),
            'registro_curso_objetivos'          => $this->input->post('configuracion_curso_objetivos'),
            'registro_curso_temario'            => $this->input->post('configuracion_curso_temario'),
            'registro_curso_fecha'              => $this->input->post('configuracion_curso_fecha'),
            'registro_curso_horario'            => $this->input->post('configuracion_curso_horario'),
            'registro_curso_cupo'               => $this->input->post('configuracion_curso_cupo'),
            'registro_curso_instructor'         => $this->input->post('configuracion_curso_instructor'),
            'registro_curso_ubicacion'          => $this->input->post('configuracion_curso_ubicacion'),
            'registro_curso_mapa_url'           => $this->input->post('configuracion_curso_mapa'),
            'registro_curso_telefono'           => $this->input->post('configuracion_curso_telefono'),
            'registro_curso_telefono_extension' => $this->input->post('configuracion_curso_telefono'),
            'registro_vigencia_inicio'          => $this->input->post('configuracion_fecha_inicio'),
            'registro_vigencia_fin'             => $this->input->post('configuracion_fecha_fin'),
            'registro_visibilidad'              => $this->input->post('configuracion_ocultar_registro'),
            'registro_texto_registro'           => $this->input->post('configuracion_texto_registro'),
            'registro_texto_confirmacion'       => $this->input->post('configuracion_texto_confirmacion'),
            'registro_texto_agradecimientos'    => $this->input->post('configuracion_texto_agradecimientos'),
            'registro_curso_id'                 => $this->input->post('configuracion_curso_id'),
            'registro_curso_modalidad'          => $this->input->post('configuracion_curso_modalidad'),
            'registro_curso_entidad'            => $this->input->post('configuracion_curso_entidad'),
            'registro_curso_costo'              => $this->input->post('configuracion_curso_costo')
        );

        foreach ($parametros as $key => $value) {
            if ($value == "true") {
                $parametros[$key] = 1;
            } elseif ($value == "false") {
                $parametros[$key] = 0;
            }
        }

        $this->curso_model->registrar_configuracion_registro($parametros);
    }

    function consulta_registro_curso($id_curso)
    {
        $registro_curso = $this->curso_model->consulta_registro_curso($id_curso);

        unset($registro_curso["id_registro"]);
        unset($registro_curso["registro_curso_id"]);

        foreach ($registro_curso as $key => $value) {
            if ($value == "1") {
                $registro_curso[$key] = true;
            }elseif($value == "0"){
                $registro_curso[$key] = false;
            }
        }

        print_r(json_encode($registro_curso));
    }

    function lista_asistencia($id_curso)
    {
        $curso = $this->curso_model->consulta_lista_asistencia($id_curso);
        $curso['profesor'] = $this->curso_model->consulta_instructores_nombre_curso($id_curso);

        $fecha_inicio = explode("-", $curso['curso_fecha_inicio']);
        $curso['curso_fecha_inicio'] = $fecha_inicio[2]."/".$fecha_inicio[1]."/".$fecha_inicio[0];

        $fecha_fin = explode("-", $curso['curso_fecha_fin']);
        $curso['curso_fecha_fin'] = $fecha_fin[2]."/".$fecha_fin[1]."/".$fecha_fin[0];

        $this->load->view('template/header');
        $this->load->view('template/menu');
        $this->load->view('lista_asistencia', $curso);
        $this->load->view('template/footer');
    }

    function usuarios_lista($id_curso)
    {
        $usuarios = $this->curso_model->consulta_autorizados_lista($id_curso);

        print_r(json_encode($usuarios));
    }

    function agregar_material($id_curso, $mensaje_confirmacion = 0)
    {
        $this->load->helper(array('form', 'url'));

        $consulta_material = $this->curso_model->consultar_material($id_curso);
        $material = array();

        if ($consulta_material) {
            foreach ($consulta_material as $llave => $valor) {
                $liga_material = $valor["material_curso_url"];
                if (preg_match('#^https?://#i', $liga_material) === 1) {
                    $liga_material = "<a href='".$liga_material."' target='_blank'>".$liga_material."</a>";
                }else{
                    $liga_material = "<a href='".base_url('assets/material_cursos/')."/".$liga_material."' target='_blank'>".$liga_material."</a>";
                }
                array_push($material, $liga_material);
            }
        }
        

        $datos_material = array('id_curso' => $id_curso,
                            'material' => $material,
                            'mensaje_confirmacion' => $mensaje_confirmacion);

        $this->load->view('template/header');
        $this->load->view('template/menu');
        $this->load->view('curso_material', $datos_material);
        $this->load->view('template/footer');
    }

    function guardar_material(){
        if (!empty($_POST)) {

            $nuevo_material = array('curso_id' => $this->input->post('id_curso'));

            if ($_FILES) {
                $respuesta_material = $this->subir_material();
                if ($respuesta_material != "error_subida") {
                    $nuevo_material['material_curso_url'] = $respuesta_material;
                }
            } else {
                $nuevo_material['material_curso_url'] = $this->input->post('material_url');
            }

            $this->curso_model->registrar_material($nuevo_material);
        }else{
            redirect(site_url("error404"));
        }

        $this->agregar_material($nuevo_material["curso_id"], 1);
    }

    function subir_material()
    {
        $config['upload_path'] = './assets/material_cursos/';
        $config['allowed_types'] = 'pdf';
        $config['max_size'] = '5120';

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload("material_file"))
        {
            echo $this->upload->display_errors();
            return "error_subida";
        }else{
            $datos = $this->upload->data();
            return $datos["file_name"];
        }
    }

    function autorizar_contacto(){
        $id_contacto = $this->input->post('id_contacto');
        $this->curso_model->autorizar_contacto($id_contacto);
    }

    function paginacion(){
        $cursos = $this->curso_model->cursos_paginacion($this->input->post('num_despliegue'), $this->input->post('num_pagina'));

        if ($cursos) {
            foreach($cursos as $llave => &$curso)
            {
                if ($curso['curso_tipo'] == '0')
                {
                    $curso['curso_tipo'] = 'Interno';
                }else{
                    $curso['curso_tipo'] = 'Externo';
                }

                if ($curso['curso_cupo'] == '0') {
                    $curso['curso_cupo'] = "No se registró cupo";
                    $curso['curso_cupo_disponible'] = "No se registró cupo";
                }else{
                    $curso_inscritos = $this->curso_model->contar_inscritos($curso['id_curso']);
                    $curso['curso_cupo_disponible'] = $curso['curso_cupo'] - $curso_inscritos;
                }

                $curso['curso_instructor'] = $this->curso_model->consulta_instructores_nombre_curso($curso['id_curso']);
            }
        }
        print_r(json_encode($cursos));
    }
}