//FUNCION PARA RESPONDER LA SOLICITUD
function ActualizarActividad() {
    ingeniero = $('#slcRespoonsable').val();
    ingeniero2 = $('#slcRespoonsable2').val();
    ingeniero3 = $('#slcRespoonsable3').val();
    ot = $('#txtOT').val();
    automovil = $('#slcAutomovil').val();
    fechaActividad = $('#datefechaCierre').val();
    idActividad = $('#idActividad').val();
    estatus = $('#slcEstatus').val();
    comment = $('#txtComment').val();

    $.ajax({
        url: 'acciones_solicitud.php',
        method: 'POST',
        dataType: 'json',
        data: {
            opcion: 'actualizarActividad',
            ingeniero: ingeniero,
            ingeniero2: ingeniero2,
            ingeniero3: ingeniero3,
            ot: ot,
            automovil: automovil,
            fechaActividad: fechaActividad,
            idActividad: idActividad,
            estatus: estatus,
            comment: comment
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
    var ing = $('#filtro-ingeniero').val();
    var area = $('#filtro-area').val();
    var ciudad = $('#filtro-ciudad').val();
    var estatus = $('#filtro-estatus').val();

    $.ajax({
        url: 'acciones_solicitud.php',
        method: 'POST',
        dataType: 'json',
        data: { opcion, ing, area, ciudad, estatus },
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
    //    const tabla = $(selectorTabla);
    var table = $(selectorTabla).closest('table').DataTable();
    table.clear().draw();
    data.forEach(function(solicitud) {

        // Determinar el estatus con su respectivo badge
        estatus = '';
        if (solicitud.estatus == 'Pendientedeinformacion') {
            estatus = '<span class="badge text-bg-warning">Pendiente de información</span>';
        }
        if (solicitud.estatus == 'Programadasinconfirmar') {
            estatus = '<span class="badge text-bg-primary">Programada sin confirmar</span>';
        }
        if (solicitud.estatus == "Servicioconfirmadoparasuejecucion") {
            estatus = '<span class="badge text-bg-success">Servicio confirmado para ejecución</span>';
        }
        if (solicitud.estatus == 'Fechareservadasininformación') {
            estatus = '<span class="badge text-bg-orange">Fecha reservada sin información</span>';
        }
        if (solicitud.estatus == 'Cancelada') {
            estatus = '<span class="badge text-bg-danger">Cancelada</span>';
        }
        if (solicitud.estatus == 'Cerrada') {
            estatus = '<span class="badge text-bg-dark">Cerrada</span>';
        }


        // Construir el nombre completo con íconos
        nombre2 = '';
        nombre3 = '';
        if (solicitud.nombre2 != '') {
            nombre2 = '<br><i class="fas fa-user"></i>' + solicitud.nombre2;
        }
        if (solicitud.nombre3 != '') {
            nombre3 = '<br><i class="fas fa-user"></i>' + solicitud.nombre3;
        }

        //Limpiar comentarios
        const comentarioLimpio = escapeForHtmlAttribute(solicitud.comment);
        const comentarioLimpioLogistico = escapeForHtmlAttribute(solicitud.comment_logistic);
        //verifica solicitud de logistica      
        var estatusLogistica = '';      
        if(solicitud.estatus_logistic === 'Solicitado'){
            if (solicitud.capturo === 'SI'){
                estatusLogistica = `
                        <button type="button" class="btn btn-warning" onclick="responderSolicitudLogistica('${solicitud.id}')">
                            <i class="fas fa-hand-paper" style="font-size:12px;"></i>
                        </button>
                `;
            }else{
                estatusLogistica = `
                        <button type="button" class="btn btn-warning">
                            <i class="fas fa-hand-paper" style="font-size:12px;"></i>
                        </button>
                `;
            }
        }else{
            if(solicitud.estatus_logistic === 'aceptada'){
                var estatusLogistica = `
                            <button type="button" class="btn btn-success" onclick="mostrarComentarios('${solicitud.order_code}', '${comentarioLimpioLogistico}')">
                                <i class="fas fa-hand-paper" style="font-size:12px;"></i>
                            </button>`;
            }else if(solicitud.estatus_logistic === 'rechazada'){
                var estatusLogistica = `
                            <button type="button" class="btn btn-danger" onclick="mostrarComentarios('${solicitud.order_code}', '${comentarioLimpioLogistico}')">
                                <i class="fas fa-hand-paper" style="font-size:12px;"></i>
                            </button>`;
            }
        }
        // Determinar las acciones disponibles de acuer
        var accion = '';
        var noEmpleado = getCookie('noEmpleado');
        if (solicitud.capturo === 'SI'){
            
                accion = `
                    <div class="btn-group" role="group">       
                        <button type="button" class="btn btn-light" onclick="mostrarComentarios('${solicitud.order_code}','${comentarioLimpio}')">
                            <i class="fas fa-comment fa-sm fa-fw mr-0 text-gray-800"></i>
                        </button>
                        <button id="btnSolicitar" type="button" class="btn btn-primary" 
                            onclick="modalactualizarActividad('${solicitud.engineer}', '${solicitud.engineer2}', '${solicitud.engineer3}', '${solicitud.order_code}', '${solicitud.vehiculo}', '${solicitud.start_date}', '${solicitud.id}', '${solicitud.estatus}', '${comentarioLimpio}')">
                            <i class="fas fa-edit"></i>
                        </button>
                        ${estatusLogistica}
                    </div>
                    `;
            
            
        } else {
            if(['42', '276', '290', '183'].includes(noEmpleado)) {
                accion = `
                    <div class="btn-group" role="group">       
                        <button type="button" class="btn btn-light" onclick="mostrarComentarios('${solicitud.order_code}','${comentarioLimpio}')">
                            <i class="fas fa-comment fa-sm fa-fw mr-0 text-gray-800"></i>
                        </button>
                        ${estatusLogistica}
                    </div>
                    `;
            }else{
                accion = `
                    <button type="button" class="btn btn-light" onclick="mostrarComentarios('${solicitud.order_code}','${comentarioLimpio}')">
                        <i class="fas fa-comment fa-sm fa-fw mr-0 text-gray-800"></i>
                    </button>
                `;
            }
        }

        


        let fechaActividad = '';
        const startDate = new Date(solicitud.start_date);
        if (!isNaN(startDate.getTime()) && startDate.getTime() < Date.now() && (solicitud.estatus != 'Cerrada' && solicitud.estatus != 'Cancelada')) {
            fechaActividad = `<h6 style="color: red; font-size:13px;">${solicitud.start_date}</h6>`;
        } else {
            fechaActividad = `<h6 style="color: black; font-size:13px;">${solicitud.start_date}</h6>`;
        }
        // Si no hay datos, mostrar 'S/R'
        const durationhr = solicitud.durationhr && solicitud.durationhr.trim() !== '' ? solicitud.durationhr : 'S/R';
        const travelhr = solicitud.travelhr && solicitud.travelhr.trim() !== '' ? solicitud.travelhr : 'S/R';
        var fila = [
            `<i class="fas fa-user"></i>${solicitud.nombre + nombre2 + nombre3}`,
            solicitud.area,
            solicitud.order_code,
            fechaActividad + `<h6 style="color: black; font-size:13px;"><i class="fas fa-tools"></i> ${durationhr} hrs` + `<br>` + `<i class="fas fa-car"></i> ${travelhr} hrs</h6>`,
            solicitud.ds_cliente,
            solicitud.city,
            solicitud.vehiculo,
            estatus,
            accion
        ];
        table.row.add(fila);
    });
    table.draw();
}

function escapeForHtmlAttribute(text) {
    if (!text) return '';
    // 1. Replace all single quotes with an escaped version \'
    // 2. Replace all double quotes with an escaped version \"
    // 3. Replace all line breaks (\n or \r) with spaces or escape sequences
    return text.toString()
        .replace(/'/g, "\\'")     // Escape single quotes
        .replace(/"/g, '\"')      // Escape double quotes (optional, but good practice)
        .replace(/(\r\n|\n|\r)/g, ' '); // Replace line breaks with a single space
}   

// FUNCION PARA MOSTRAR COMENTARIOS
function mostrarComentarios(ot, comentario) {
    Swal.fire({
        icon: "info",
        title: "Comentarios " + ot,
        text: comentario,
        draggable: true
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
function modalactualizarActividad(ingeniero, ingeniero2, ingeniero3, ot, vehiculo, fechaActividad, idActividad, estatus, comment) {
    $('#Divsolicita2').show();
    $('#Divsolicita3').show();

    $('#slcRespoonsable').val(ingeniero);
    $('#slcRespoonsable2').val(ingeniero2);
    $('#slcRespoonsable3').val(ingeniero3);

    $('#txtOT').val(ot);
    $('#slcAutomovil').val(vehiculo);
    $('#datefechaCierre').val(fechaActividad);
    $('#idActividad').val(idActividad);
    $('#slcEstatus').val(estatus);
    $('#txtComment').val(comment);

    if (ingeniero2 == '0' || ingeniero2 == '') {
        $('#Divsolicita2').hide();
    }
    if (ingeniero3 == '0' || ingeniero3 == '') {
        $('#Divsolicita3').hide();
    }

    // Inicializa Select2 en el campo de responsable
    $('#slcRespoonsable').select2({
        dropdownParent: $('#actualizarActividadModal'),
        placeholder: "Seleccione...",
        width: '100%'
    });
    $('#slcRespoonsable2').select2({
        dropdownParent: $('#actualizarActividadModal'),
        placeholder: "Seleccione...",
        width: '100%'
    });
    $('#slcRespoonsable3').select2({
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

//FUNCION PARA AGREGAR O ELIMINAR DIVS DE INGENIEROS
function divsIng(accion) {
    if (accion === 'agrega') {
        if ($('#Divsolicita2').is(':hidden')) {
            $('#Divsolicita2').show();
        } else if ($('#Divsolicita3').is(':hidden')) {
            $('#Divsolicita3').show();
        } else {
            Swal.fire({
                title: "Solo puedes agregar hasta 3 ingenieros",
                icon: "warning",
                draggable: true
            });
        }
    } else if (accion === 'elimina') {
        if ($('#Divsolicita3').is(':visible')) {
            $('#Divsolicita3').hide();
            $('#slcRespoonsable3').val('0');
        } else if ($('#Divsolicita2').is(':visible')) {
            $('#Divsolicita2').hide();
            $('#slcRespoonsable2').val('0');
        } else {
            Swal.fire({
                title: "No hay más ingenieros para eliminar",
                icon: "warning",
                draggable: true
            });
        }
    }
}

//FUNCION PARA CARGAR INFORMACIÓN DE LAS CIUDADES 
function cargarCiudades() {
    $.ajax({
        type: "POST",
        url: "acciones_solicitud.php",
        data: { opcion: "consultarCiudades" },
        dataType: "json",
        success: function(respuesta) {
            var select = $("#filtro-ciudad");
            var i = 0;
            respuesta.forEach(function(ciudad) {
                if (i == 0) {
                    var option = `<option value="">Selecciona...</option>`;
                    select.append(option);
                }
                var option = `<option value="${ciudad.ciudad}"><b>${ciudad.estado}</b>  -  ${ciudad.ciudad}</option>`;
                select.append(option);
                i++;
            });
        },
        error: function(xhr, status, error) {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "Hubo un problema al cargar los datos.",
                confirmButtonText: "Aceptar"
            });
        }
    });
}

//FUNCION PARA CARGAR INFORMACIÓN DE LOS ESTATUS
function cargarEstatus() {
    $.ajax({
        type: "POST",
        url: "acciones_solicitud.php",
        data: { opcion: "consultarEstatus" },
        dataType: "json",
        success: function(respuesta) {
            var select = $("#filtro-estatus");
            var i = 0;
            respuesta.forEach(function(estatus) {
                if (i == 0) {
                    var option = `<option value="">Selecciona...</option>`;
                    select.append(option);
                }
                var option = `<option value="${estatus.id}"><b>${estatus.nombre}</b></option>`;
                select.append(option);
                i++;
            });
        },
        error: function(xhr, status, error) {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "Hubo un problema al cargar los datos.",
                confirmButtonText: "Aceptar"
            });
        }
    });
}

// FUNCION PARA RESPONDER LA SOLICITUD DE LOGISTICA
function responderSolicitudLogistica(idActividad) {

    Swal.fire({
        icon: "question",
        title: "¿Confirmas que deseas aceptar esta solicitud de apoyo?",
        showDenyButton: true,
        showCancelButton: false,
        denyButtonText: "Rechazar",
        confirmButtonText: "Aceptar",             
    }).then((result) => {
        // Al usar result.isConfirmed o result.isDenied, ya sabemos la acción.
        if (result.isConfirmed) {            
            modalSolicitarApoyoLogistica(idActividad, 'aceptada'); 
        } else if (result.isDenied) {
            modalSolicitarApoyoLogistica(idActividad, 'rechazada');
        }
    });
}

function modalSolicitarApoyoLogistica(idActividad, accion) {
    $('#idActividadLogistica').val(idActividad);
    $('#accionLogistica').val(accion);
    
    if (accion === 'aceptada') {          
        actualizarInsigniaJQuery('Solicitud Aceptada', 'text-bg-success');
        $('#txtCommentLogistica').attr('placeholder', 'Por favor indica los horarios de salida para que se pueda coordinar el apoyo');
    } else if (accion === 'rechazada') {     
        actualizarInsigniaJQuery('Solicitud Rechazada', 'text-bg-danger');
        $('#txtCommentLogistica').attr('placeholder', 'Por favor indica el motivo del rechazo de la solicitud');
    }

    $('#responderSolicitudLogisticaModal').modal('show');
}

function actualizarInsigniaJQuery(nuevoTexto, nuevaClaseBootstrap) {
    const $insignia = $('#estado-badge');
    
    $insignia.removeClass(function(index, className) {
        return (className.match(/\btext-bg-\S+/g) || []).join(' ');
    });
    
    $insignia.addClass(nuevaClaseBootstrap);
    $insignia.text(nuevoTexto);
}

function enviarRespuestaLogistica() {
    var idActividad = $('#idActividadLogistica').val();
    var accion = $('#accionLogistica').val();
    var commentLogistica = $('#txtCommentLogistica').val(); 
    $.ajax({
        url: 'acciones_solicitud.php',
        method: 'POST',
        dataType: 'json',
        data: {
            opcion: 'responderSolicitudLogistica',
            idActividad: idActividad,
            accion: accion,
            commentLogistica: commentLogistica
        },
        success: function(data) {
            $('#responderSolicitudLogisticaModal').modal('hide');   
            Swal.fire({
                title: "Respuesta enviada con éxito!",
                icon: "success",
                draggable: true
            }).then(() => {
                // Recargar la tabla de solicitudes abiertas
                SolicitudesAbiertas();
                enviaNotificacionResp(idActividad, commentLogistica, accion);
            });
        },
        error: function(jqXHR, textStatus, errorThrown) {
            $('#responderSolicitudLogisticaModal').modal('hide');
            Swal.fire({
                title: "La respuesta no se pudo enviar!",
                icon: "error",
                draggable: true
            });
        }
    });
}

function enviaNotificacionResp(idActividad, commentLogistica, accion) {
    $.ajax({
        url: 'enviaNotificacionLogResp.php', // La URL del script PHP que obtendra los datos
        method: 'POST',
        dataType: 'json',
        data: {
            servicio: idActividad,
            commentLogistica: commentLogistica,
            accion: accion
        },
        success: function(data) {
            
        },
        error: function(jqXHR, textStatus, errorThrown) {                    

        }
    });
}