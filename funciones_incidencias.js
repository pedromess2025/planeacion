//FUNCION PARA RESPONDER LA SOLICITUD
function ActualizarActividad() {
    ingeniero = $('#slcRespoonsable').val();
    ot = $('#txtOT').val();    
    automovil =$('#slcAutomovil').val();
    fechaActividad = $('#datefechaCierre').val();
    idActividad = $('#idActividad').val();
    estatus = $('#slcEstatus').val();

    $.ajax({
        url: 'acciones_solicitud.php',
        method: 'POST',
        dataType: 'json',
        data: {
            opcion: 'actualizarActividad',
            ingeniero: ingeniero,
            ot: ot,
            automovil: automovil,
            fechaActividad: fechaActividad,
            idActividad: idActividad
        },
        success: function(data) {
            $('#actualizarActividadModal').modal('hide');
            Swal.fire({
                title: "Actividad actualizada con éxito!",
                icon: "success",
                draggable: true
            }).then(() => {
                // Recargar la tabla de solicitudes abiertas
                SolicitudesAbiertas();
            });
        },
        error: function(jqXHR, textStatus, errorThrown) {
            $('#responderIncidenciaModal').modal('hide');
            Swal.fire({
                title: "La actividad no se pudo actualizar!",
                icon: "error",
                draggable: true
            });
        }
    });
}

//FUNCION PARA MOSTRAR LAS SOLICITUDES ABIERTAS
function SolicitudesAbiertas() {
    
    manejarVisibilidadDeTablas("#TSolAbiertas_wrapper");
    obtenerYRenderizarSolicitudes("solicitudesAbiertas", "#TSolAbiertas tbody");

}

// FUNCIÓN PARA MANEJAR LA VISIBILIDAD DE LAS TABLAS
function manejarVisibilidadDeTablas(tablaAMostrar) {
    // Oculta todas las tablas
    $("#TSolAbiertas_wrapper, #TSolAceptadas_wrapper, #TSolEnProceso_wrapper, #TSolCerradas_wrapper, #TSolRechazadas_wrapper").hide();
    
    // Muestra solo la tabla deseada
    $(tablaAMostrar).show();
}

// FUNCIÓN PARA OBTENER Y RENDERIZAR LAS SOLICITUDES
function obtenerYRenderizarSolicitudes(opcion, tablaSeleccionada) {
    $.ajax({
        url: 'acciones_solicitud.php',
        method: 'POST',
        dataType: 'json',
        data: {opcion},
        success: function(data) {
            // Lógica de Renderizado: Procesa los datos y los inserta en la tabla
            renderizarTabla(tablaSeleccionada, data);
        },
        error: function(jqXHR, textStatus, errorThrown) {
            // Lógica de Manejo de Errores
            mostrarMensajeDeError();
        }
    });
}

// FUNCIÓN PARA RENDERIZAR LA TABLA
function renderizarTabla(selectorTabla, data) {
    const tabla = $(selectorTabla);
    tabla.empty(); // Limpia el contenido actual de la tabla    

    data.forEach(function (solicitud) {
        estatus = '';
        if(solicitud.estatus == 'Pendientedeinformacion'){
            estatus = '<span class="badge text-bg-primary">Pendiente de información</span>';
        }
        if(solicitud.estatus == 'Programadasinconfirmar'){
            estatus = '<span class="badge text-bg-info">Programada sin confirmar</span>';
        }
        if(solicitud.estatus == "Sevicioconfirmadoparasuejecucion"){
            estatus = '<span class="badge text-bg-warning">Servicio confirmado para ejecución</span>';
        }
        if(solicitud.estatus == 'Fechareservadasininformación'){
            estatus = '<span class="badge text-bg-dark">Fecha reservada sin información</span>';
        }
        const fila = `
            <tr>
                <td>${solicitud.nombre}</td>
                <td>${solicitud.area}</td>
                <td>${solicitud.order_code}</td>
                <td>${solicitud.start_date}</td>
                <td>${solicitud.ds_cliente}</td>
                <td>${solicitud.city}</td>
                <td>${solicitud.vehiculo}</td>
                <td>${estatus}</td>
                <td><button id="btnSolicitar" type="button" class="btn btn-success" 
                onclick="modalactualizarActividad('${solicitud.engineer}', '${solicitud.order_code}', '${solicitud.vehiculo}', '${solicitud.start_date}', '${solicitud.id}', '${solicitud.estatus}')">Actualizar</button></td>
            </tr>`;
        tabla.append(fila);
    });
}

// FUNCION PARA MOSTRAR MENSAJE DE ERROR
function mostrarMensajeDeError() {
    Swal.fire({
        title: "La solicitud no se pudo procesar!",
        icon: "error",
        draggable: true
    });
}

//FUNCION PARA ABRIR EL MODAL PARA RESPONDER LA SOLICITUD
function modalactualizarActividad(ingeniero, ot, vehiculo, fechaActividad, idActividad, estatus) {    
    
    $('#slcRespoonsable').val(ingeniero);
    $('#txtOT').val(ot);    
    $('#slcAutomovil').val(vehiculo);
    $('#datefechaCierre').val(fechaActividad);
    $('#idActividad').val(idActividad);
    $('#slcEstatus').val(estatus);

    // Inicializa Select2 en el campo de responsable
        $('#slcRespoonsable').select2({
            dropdownParent: $('#actualizarActividadModal'),
            placeholder: "Seleccione...",
            width: '100%'
        });
        $('#slcAreas').select2({
            dropdownParent: $('#actualizarActividadModal'), 
            placeholder: "Seleccione...",
            width: '100%'
        });
        $('#slcCiudad').select2({
            dropdownParent: $('#actualizarActividadModal'),
            placeholder: "Seleccione...",
            width: '100%'
        });
        $('#slcAutomovil').select2({
            dropdownParent: $('#actualizarActividadModal'),
            placeholder: "Seleccione...",
            width: '100%'
        });
        $('#slcEstatus').select2({
            dropdownParent: $('#actualizarActividadModal'),
            placeholder: "Seleccione...",
            width: '100%'
        });

        $('#actualizarActividadModal').modal('show');
}

//FUNCION PARA OBTENER EL VALOR DE LA COOKIE
function getCookie(name) {
    let value = "; " + document.cookie;
    let parts = value.split("; " + name + "=");
    if (parts.length === 2) return parts.pop().split(";").shift();
}

