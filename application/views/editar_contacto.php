<script>
    $(document).ready(function(){
        $("#frm_editar_contacto").validationEngine({promptPosition: "centerRight"});

        $("#menu_contactos").addClass("seleccion_menu");

        $.ajax("<?= site_url('contactos/consulta_contacto').'/'.$id_contacto?>", {
			dataType: 'json',
			type: 'post',
			success: function(resultado)
			{
				if (resultado != null) {

					if (resultado['contacto_rol'] == 0) {
						$("input[name='tipo_contacto']").prop('disabled', true);
					}else{
						$("input[name=tipo_contacto][value="+resultado['contacto_tipo']+"]").prop('checked', true);
					}

					$('#contacto_nombre').val(resultado['contacto_nombre']);
					$('#contacto_paterno').val(resultado['contacto_ap_paterno']);
					$('#contacto_materno').val(resultado['contacto_ap_materno']);
					$('#contacto_adscripcion').val(resultado['contacto_adscripcion']);
					$('#contacto_funciones').val(resultado['contacto_funciones']);
					$('#contacto_correoinst').val(resultado['contacto_correo_inst']);
					$('#contacto_correopers').val(resultado['contacto_correo_per']);
					$('#contacto_telefono').val(resultado['contacto_telefono']);
					$('#contacto_ext').val(resultado['contacto_extension']);
					$('#contacto_instancias').val(resultado['instancia_nombre']);
					$('#id_instancia').val(resultado['contacto_instancia']);
					$("input[name=estatus_contacto][value="+resultado['contacto_estatus']+"]").prop('checked', true);
					$("input[name=instructor_candidato][value="+resultado['contacto_instructor']+"]").prop('checked', true);
					$("input[name=comunicacion_contacto][value="+resultado['contacto_comunicacion']+"]").prop('checked', true);
					$('#contacto_idu').val(resultado['contacto_IDU']);
					$('#contacto_idu_hidden').val(resultado['contacto_IDU']);
					$("input[name=rol_contacto][value="+resultado['contacto_rol']+"]").prop('checked', true);

					$('#contacto_avatar_old').val(resultado['contacto_avatar']);

					if (resultado['contacto_avatar'] != "") {
						url = "<?= base_url('assets/img_avatar') ?>";
						url += "/"+resultado['contacto_avatar'];
						$('#contacto_imagen').attr('src', url);
						$("#contacto_imagen").after($("<button>", {
							text 	: "Borrar imagen",
							id		: "borrar_imagen",
							type	: "button"
						}));
					}
				}
			}
		});

        $.ajax("<?= site_url('contactos/consulta_instancias')?>", {
			dataType: 'json',
			type: 'post',
			success: function(resultado)
			{
				if (resultado != null) {

					var instancias = [];

					$.each(resultado, function( index, value ) {
						instancias.push({
							label : value['instancia_nombre'],
							value : value['id_instancia']
						});
					});

					$('#contacto_instancias').autocomplete({
						source: instancias,
						autoFocus: false,
						change: function(event, ui) {
							if(!ui.item){
								$("#contacto_instancias").val("");
							}
						},
						focus: function(event, ui) {
							return false;
						},
						select: function(event, ui) {
							$("#contacto_instancias").val( ui.item.label );
							$("#id_instancia").val( ui.item.value );

							return false;
						}
					});
				}
			}
		});

		$(document).tooltip();

		$("#estado_idu").hide();

		$("#contacto_avatar").change(function(){
	        if (this.files && this.files[0]) {
	            var reader = new FileReader();
	            reader.onload = function (e) {
	                $('#contacto_imagen').attr('src', e.target.result);
	            }
	            reader.readAsDataURL(this.files[0]);
	        }
	    });

	    $("#contenedor_imagen").on("click", "#borrar_imagen", function(){
	    	url = "<?= base_url('assets/img/avatar.png') ?>";
			$('#contacto_imagen').attr('src', url);
			$("#borrar_imagen").hide();
			$("#contacto_avatar_old").val("");
	    });

	    $("#contacto_telefono").keypress(function(){
	    	$("#contacto_telefono").validationEngine('validate');
	    });

	    $("input[name='rol_contacto']").change(function(){
			if ($(this).val() == 0) {
				$("input[name='tipo_contacto']").prop('disabled', true);
				$("input[name='tipo_contacto']").val("");
			} else{
				$("input[name='tipo_contacto']").prop('disabled', false);
			}
		});
    });
</script>
<!-- inicia contenido -->
<div class="contenido_dinamico">
	<div id="migaDePan">
		<a href="<?= base_url()?>">Inicio</a> > 
		<a href="<?= site_url('contactos')?>">Administrar Contactos</a> > Editar contacto
	</div>

	<form id="frm_editar_contacto" action="<?= site_url('contactos/editar').'/'.$id_contacto?>" method="POST" enctype="multipart/form-data">
		<fieldset>
		<legend>Editar contacto</legend>
		<div class="contenedor_seccion_formulario">
			<p>Los datos marcados con asterisco son obligatorios.</p>
			<br>
			<label class="etiqueta_frm" for="contacto_idu">Identificador Universitario (IDU)</label>
			<input type="text" maxlength="50" id="contacto_idu" disabled>
			<input type="hidden" maxlength="50" id="contacto_idu_hidden" name="contacto_idu">
			<img src="<?= base_url('assets/img/loading.gif') ?>" width="20" height="20" id="estado_idu">
			<br>
			<div id="contenedor_imagen">
				<img src="<?= base_url('assets/img/avatar.png') ?>" id="contacto_imagen">
				<input type="file" id="contacto_avatar" name="contacto_avatar" class="validate[checkFileType[jpg|jpeg|gif|JPG|JPEG|GIF]]" data-prompt-position="topLeft">
				<br><span>Formatos permitidos .jpg y .gif</span>
				<input type="hidden" id="contacto_avatar_old" name="contacto_avatar_old">
			</div>
			<p class="encabezado_form_nuevo_contacto">Datos Generales</p>
			<label class="etiqueta_frm" for="contacto_nombre">* Nombre</label>
			<input type="text" maxlength="50" id="contacto_nombre" name="contacto_nombre" class="validate[required]">
			<br>
			<label class="etiqueta_frm" for="contacto_paterno">* Apellido paterno</label>
			<input type="text" maxlength="50" id="contacto_paterno" name="contacto_apaterno" class="input_frm_nuevo validate[required]">
			<br>
			<label class="etiqueta_frm" for="contacto_materno">* Apellido materno</label>
			<input type="text" maxlength="50" id="contacto_materno" name="contacto_amaterno" class="validate[required]">
			<br><br>
			<label>* Rol</label>
			<br>
			<input type="radio" name="rol_contacto" value="0" id="rol_adm" class="validate[required]">
			<label for="rol_adm">Administrador</label>
			<br>
			<input type="radio" name="rol_contacto" value="1" id="rol_tec" class="validate[required]">
			<label for="rol_tec">Responsable</label>
			<br><br>
			<label>Estatus</label>
			<img src="<?= base_url('assets/img/icono_tooltip.gif')?>" title="Indica el estado en el que se encuentra la cuenta del usuario." class="icon_tooltip">
			<input type="radio" name="estatus_contacto" value="1" id="estatus_activo">
			<label for="estatus_activo">Activo</label>
			<input type="radio" name="estatus_contacto" value="0" id="estatus_inactivo">
			<label for="estatus_inactivo">Inactivo</label>
		</div>
		<div class="contenedor_seccion_formulario">
			<label>* Tipo de usuario</label>
			<img src="<?= base_url('assets/img/icono_tooltip.gif')?>" title="Indica el rol que tiene el contacto dentro de la instancia a la que pertenece.">
			<br>
			<input type="radio" name="tipo_contacto" value="0" id="tipo_tec" class="validate[required]">
			<label for="tipo_tec">Responsable t&eacute;cnico</label>
			<br>
			<input type="radio" name="tipo_contacto" value="1" id="tipo_com" class="validate[required]">
			<label for="tipo_com">Responsable de comunicaci&oacute;n</label>
			<br>
		</div>
		
		<label>Instructor candidato</label>
		<img src="<?= base_url('assets/img/icono_tooltip.gif')?>" title="Indica si un contacto ha sido o se ha postulado como instructor de un determinado curso." class="icon_tooltip">
		<input type="radio" name="instructor_candidato" value="1" id="instructor_si">
		<label for="instructor_si">S&iacute;</label>
		<input type="radio" name="instructor_candidato" value="0" id="instructor_no">
		<label for="instructor_no">No</label>
		<br>
		<p class="encabezado_form_nuevo_contacto">Datos Institucionales</p>
		<label for="contacto_instancias">* Instancia</label>
		<img src="<?= base_url('assets/img/icono_tooltip.gif')?>" title="Se refiere a la entidad o dependencia a la que pertenece el contacto.">
		<input type="search" name="contacto_instancia_nombre" id="contacto_instancias" class="input_frm_nuevo validate[required]" size="73" maxlength="255">
		<input type="hidden" name="contacto_instancia" id="id_instancia" class="input_frm_nuevo validate[required]">
		<br><label for="contacto_adscripcion">&Aacute;rea de adscripci&oacute;n</label>
		<img src="<?= base_url('assets/img/icono_tooltip.gif')?>" title="Indica el área de la instancia a la que pertenece.">
		<input type="text" maxlength="255" id="contacto_adscripcion" name="contacto_adscripcion" size="67">
		<br><br><br>
		<label for="contacto_funciones" class="label_funciones">Descripci&oacute;n de funciones</label>
		<textarea id="contacto_funciones" name="contacto_funciones" cols="50" rows="4" maxlength="255" placeholder="Ingrese una breve descripción de las actividades que desempeña el contacto dentro de su instancia o bien describa las actividades acorde a su rol."></textarea>
		<br>
		<p class="encabezado_form_nuevo_contacto">Datos de Contacto</p>

		<div class="contenedor_seccion_formulario">
			<label for="contacto_telefono">* T&eacute;lefono</label>
			<input type="text" id="contacto_telefono" name="contacto_telefono" size="10" maxlength="10" class="input_frm_nuevo validate[required,custom[numero]]">
			<label for="contacto_ext" id="etiqueta_ext">ext.</label>
			<input type="text" id="contacto_ext" name="contacto_extension" size="5" maxlength="5" class="validate[custom[numero]]">
		</div>
		<label for="contacto_correoinst">Correo electr&oacute;nico institucional</label>
		<img src="<?= base_url('assets/img/icono_tooltip.gif')?>" title="Correo electrónico institucional con la que cuenta el usuario.">
		<input type="text" maxlength="100" id="contacto_correoinst" name="contacto_correoinst" class="validate[groupRequired[correo],custom[email],custom[email_unam]]">
		<br><br>
		<label for="contacto_correopers">Correo electr&oacute;nico adicional</label>
		<img src="<?= base_url('assets/img/icono_tooltip.gif')?>" title="Es un correo electrónico alterno al institucional.">
		<input type="text" maxlength="100" id="contacto_correopers" name="contacto_correopers" class="validate[groupRequired[correo],custom[email]]">
		<br><br>
		<label>* Medio de comunicaci&oacute;n preferente</label>
		<img src="<?= base_url('assets/img/icono_tooltip.gif')?>" title="Se refiere al medio de comunicación que el contacto prefiere.">
		<br><br>
		<input type="radio" name="comunicacion_contacto" value="0" id="comunicacion_tel" class="validate[required]">
		<label for="comunicacion_tel">V&iacute;a telef&oacute;nica</label>
		<br>
		<input type="radio" name="comunicacion_contacto" value="1" id="comunicacion_email" class="validate[required]">
		<label for="comunicacion_email">V&iacute;a correo electr&oacute;nico</label>
		<br>
		<div id="botones_envio">
			<input type="submit" id="btn_guardar" value="Guardar">
			<a href="<?= site_url('contactos') ?>">
				<button type="button">Cancelar</button>
			</a>
		</div>
		</fieldset>
	</form>
	

</div>
<!-- termina contenido -->