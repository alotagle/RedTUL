<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class contacto_model extends CI_Model{

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function registrar_contacto($nuevo_contacto)
    {
		$this->db->insert('contacto', $nuevo_contacto);
    }

    public function consulta_instancias()
    {
        $this->db->from('instancia');

        $this->db->order_by('instancia_nombre', 'asc');

		$query = $this->db->get();

		if ($query -> num_rows() > 0)
		{
            return $query->result_array();
        } else {
            return null;
        }
    }

    public function eliminar($id_contacto)
    {
        $this->db->select('contacto_avatar');
        $this->db->from('contacto');
        $this->db->where('id_contacto', $id_contacto);

        $query = $this->db->get();

        $nombre_archivo = $query->row_array();

        $this->db->delete('contacto', array('id_contacto' => $id_contacto));

        return $nombre_archivo;
    }

    public function eliminar_archivos($id_contacto)
    {
        $this->db->select('contacto_avatar');
        $this->db->from('contacto');
        $this->db->where('id_contacto', $id_contacto);

        $query = $this->db->get();

        $nombre_archivo = $query->row_array();

        return $nombre_archivo;
    }

    public function consulta_contacto($id_contacto)
    {
        $this->db->where('contacto.id_contacto', $id_contacto);
        $this->db->from('contacto');
        $this->db->join('instancia', 'contacto.contacto_instancia = instancia.id_instancia');

        $query = $this->db->get();

        if ($query -> num_rows() > 0)
        {
            return $query->row();
        } else {
            return null;
        }
    }

    public function editar_contacto($contacto)
    {
        $this->db->where('id_contacto', $contacto['id_contacto']);
        unset($contacto['id_contacto']);

        $this->db->update('contacto', $contacto);
    }

    public function buscar($parametros_busqueda)
    {
        $this->db->select('contacto.id_contacto,
                            contacto.contacto_nombre,
                            contacto.contacto_ap_paterno,
                            contacto.contacto_ap_materno,
                            tipo_contacto.tipo_contacto_descripcion,
                            contacto.contacto_estatus,
                            instancia.instancia_nombre,
                            contacto.contacto_correo_inst,
                            contacto.contacto_correo_per,
                            contacto.contacto_IDU,
                            rol_contacto.rol_contacto_descripcion');

        $this->db->from('contacto');

        $this->db->join('tipo_contacto', 'contacto.contacto_tipo = tipo_contacto.id_tipo_contacto');
        $this->db->join('instancia', 'contacto.contacto_instancia = instancia.id_instancia');
        $this->db->join('rol_contacto', 'contacto.contacto_rol = rol_contacto.id_rol_contacto');

        $this->db->order_by("contacto.contacto_ap_paterno", "asc"); 

        if ($parametros_busqueda['nombre_contacto'] != "") {
            $this->db->like('contacto.contacto_nombre', $parametros_busqueda['nombre_contacto']);
        }

        if ($parametros_busqueda['paterno_contacto'] != "") {
            $this->db->like('contacto.contacto_ap_paterno', $parametros_busqueda['paterno_contacto']);
        }

        if ($parametros_busqueda['materno_contacto'] != "") {
            $this->db->like('contacto.contacto_ap_materno', $parametros_busqueda['materno_contacto']);
        }

        if ($parametros_busqueda['correo_contacto'] != "") {
            $correo = $parametros_busqueda['correo_contacto'];
            $this->db->where("(contacto.contacto_correo_inst LIKE '%$correo%' || contacto.contacto_correo_per LIKE '%$correo%')");
        }

        if ($parametros_busqueda['tipo_contacto'] != "") {
            $this->db->where('contacto.contacto_tipo', $parametros_busqueda['tipo_contacto']);
        }

        if ($parametros_busqueda['instancia_contacto'] != "") {
            $this->db->like('instancia.instancia_nombre', $parametros_busqueda['instancia_contacto']);
        }

        if ($parametros_busqueda['instructor_contacto'] != "") {
            $this->db->where('contacto.contacto_instructor', $parametros_busqueda['instructor_contacto']);
        }

        $query = $this->db->get();

        if ($query -> num_rows() > 0)
        {
            return $query->result_array();
        } else {
            return null;
        }
    }

    public function consulta_detalle_contacto($id_contacto)
    {
        $this->db->from('contacto');
        $this->db->join('instancia', 'contacto.contacto_instancia = instancia.id_instancia');
        $this->db->join('tipo_contacto', 'contacto.contacto_tipo = tipo_contacto.id_tipo_contacto', 'left');
        $this->db->join('rol_contacto', 'contacto.contacto_rol = rol_contacto.id_rol_contacto');
        $this->db->where('contacto.id_contacto', $id_contacto);

        $query = $this->db->get();

        if ($query -> num_rows() > 0)
        {
            return $query->row_array();
        } else {
            return null;
        }
    }

    public function consulta_identificador($identificador){
        $this->db->from('contacto');
        $this->db->where('contacto_IDU', $identificador);

        $query = $this->db->get();

        if($query->num_rows() > 0){
            return $query->row();
        }else{
            return null;
        }
    }

    public function paginacion_contar_contactos()
    {
        $this->db->from('contacto');

        $query = $this->db->count_all_results();

        return $query;
    }

    public function contactos_paginacion($limite, $inicio_resultado)
    {
        $this->db->select('contacto.id_contacto,
                            contacto.contacto_nombre,
                            contacto.contacto_ap_paterno,
                            contacto.contacto_ap_materno,
                            tipo_contacto.tipo_contacto_descripcion,
                            contacto.contacto_estatus,
                            instancia.instancia_nombre,
                            contacto.contacto_correo_inst,
                            contacto.contacto_correo_per,
                            contacto.contacto_IDU,
                            rol_contacto.rol_contacto_descripcion');

        $this->db->from('contacto');

        $this->db->join('tipo_contacto', 'contacto.contacto_tipo = tipo_contacto.id_tipo_contacto', 'left');
        $this->db->join('instancia', 'contacto.contacto_instancia = instancia.id_instancia');
        $this->db->join('rol_contacto', 'contacto.contacto_rol = rol_contacto.id_rol_contacto');

        $this->db->order_by("contacto.contacto_ap_paterno", "asc");

        $this->db->limit($limite, $inicio_resultado * $limite - $limite);

        $query = $this->db->get();

        if ($query -> num_rows() > 0)
        {
            return $query->result_array();
        } else {
            return null;
        }
    }
}