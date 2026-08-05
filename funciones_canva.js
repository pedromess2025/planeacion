$(document).ready(function() {
    let fechaActualPivot = new Date();
    
    cargarLaboratorios();
    cargarTableroSemana(fechaActualPivot);

    $('#btn-semana-anterior').click(function() {
        fechaActualPivot.setDate(fechaActualPivot.getDate() - 7);
        cargarTableroSemana(fechaActualPivot);
    });

    $('#btn-semana-actual').click(function() {
        fechaActualPivot = new Date();
        cargarTableroSemana(fechaActualPivot);
    });

    $('#btn-semana-siguiente').click(function() {
        fechaActualPivot.setDate(fechaActualPivot.getDate() + 7);
        cargarTableroSemana(fechaActualPivot);
    });

    $('#filtro-laboratorio').change(function() {
        cargarTableroSemana(fechaActualPivot);
    });

    // Función para poblar el select del DOM
    function cargarLaboratorios() {
        $.ajax({
            url: 'acciones_canva.php',
            type: 'POST',
            dataType: 'json',
            data: { accion: 'obtener_laboratorios' },
            success: function(response) {
                if (response.status === 'success') {
                    let select = $('#filtro-laboratorio');
                    // Mantenemos la opción por defecto y limpiamos el resto
                    select.html('<option value="TODOS">Todos los Laboratorios</option>');
                    
                    response.data.forEach(lab => {
                        select.append(`<option value="${lab}">${lab}</option>`);
                    });
                }
            }
        });
    }


    function cargarTableroSemana(pivotDate) {
        let diaDeLaSemana = pivotDate.getDay();
        let diferenciaAlLunes = pivotDate.getDate() - diaDeLaSemana + (diaDeLaSemana === 0 ? -6 : 1);
        let lunes = new Date(pivotDate.setDate(diferenciaAlLunes));
        
        let fechasSemana = [];
        for (let i = 0; i < 7; i++) {
            let d = new Date(lunes);
            d.setDate(lunes.getDate() + i);
            fechasSemana.push(d.toISOString().split('T')[0]);
        }

        let fechaInicioStr = fechasSemana[0];
        let fechaFinStr = fechasSemana[6];
        $('#rango-semana-titulo').text(`Equipos recibidos entre el ${fechaInicioStr} y el ${fechaFinStr}`);

        let labSeleccionado = $('#filtro-laboratorio').val();

        $.ajax({
            url: 'acciones_canva.php',
            type: 'POST',
            dataType: 'json',
            data: { accion: 'cargar_tablero', fecha_inicio: fechaInicioStr, fecha_fin: fechaFinStr, laboratorio: labSeleccionado },
            success: function(response) {
                if (response.status === 'success') dibujarTablero(response.data, fechasSemana);
            }
        });
    }

    function dibujarTablero(dataFechas, fechasSemana) {
        let tbody = $('#kanban-tbody');
        tbody.empty();

        let totales = { RECEPCION: 0, TRANSFERENCIA: 0, LABORATORIO: 0, TERMINADO: 0, CERRADO: 0 };
        let nombresDias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];

        fechasSemana.forEach((fechaISO, index) => {
            let tr = $('<tr></tr>');
            
            // Fila: Día de la semana de entrada
            tr.append(`<td class="align-middle text-center bg-light">
                <strong class="text-dark d-block">${nombresDias[index]}</strong>
                <small class="text-muted">${fechaISO}</small>
            </td>`);

            let tds = {
                RECEPCION: $('<td></td>'),
                TRANSFERENCIA: $('<td></td>'),
                LABORATORIO: $('<td></td>'),
                TERMINADO: $('<td></td>'),
                CERRADO: $('<td></td>')
            };

            // Buscar si hay equipos que entraron ese día
            let diaData = dataFechas.find(item => item.fecha_recepcion === fechaISO);
            
            if(diaData) {
                diaData.registros.forEach(reg => {
                    // Ahora la OV aparece como una etiqueta dentro de la tarjeta
                    
                let cardHtml = `
                    <div style="background: #fff; border: 1px solid #d1d3e2; border-left: 3px solid #4e73df; padding: 6px; margin-bottom: 6px; border-radius: 4px; font-size: 0.75rem; text-align: left;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 2px;">
                            <strong style="color: #3a3b45;">${reg.folio}</strong>                            
                        </div>
                        <div style="margin-bottom: 2px;">
                            <span style="background: #f6c23e; color: #fff; padding: 1px 4px; border-radius: 3px; font-size: 0.65rem;">${reg.orden_venta}</span>
                        </div>
                        <div style="margin-bottom: 2px;">
                            <span style="background: #36b9cc; color: #fff; padding: 1px 4px; border-radius: 3px; font-size: 0.65rem;">${reg.ot}</span>
                        </div>
                        <div style="color: #6c757d; font-size: 0.7rem; margin-bottom: 2px;"><i class="fas fa-flask"></i> ${reg.laboratorio}</div>
                        <div style="color: #1cc88a; font-weight: bold; font-size: 0.75rem;">$${reg.valor_usd.toLocaleString('en-US', {minimumFractionDigits: 2})} USD</div>
                    </div>
                `;
                    
                    totales[reg.columna] += reg.valor_usd;
                    tds[reg.columna].append(cardHtml);
                });
            }

            tr.append(tds.RECEPCION, tds.TRANSFERENCIA, tds.LABORATORIO, tds.TERMINADO, tds.CERRADO);
            tbody.append(tr);
        });

        $('#tot-recepcion').text(`$${totales.RECEPCION.toLocaleString('en-US', {minimumFractionDigits: 2})}`);
        $('#tot-transferencia').text(`$${totales.TRANSFERENCIA.toLocaleString('en-US', {minimumFractionDigits: 2})}`);
        $('#tot-laboratorio').text(`$${totales.LABORATORIO.toLocaleString('en-US', {minimumFractionDigits: 2})}`);
        $('#tot-terminado').text(`$${totales.TERMINADO.toLocaleString('en-US', {minimumFractionDigits: 2})}`);
        $('#tot-cerrado').text(`$${totales.CERRADO.toLocaleString('en-US', {minimumFractionDigits: 2})}`);
    }


    // Evento para abrir el modal de rezagados y detalle
    $('#btn-abrir-modal-rezago').click(function() {
        // Obtenemos el lunes de la semana actual que ya calcula el tablero
        let labSeleccionado = $('#filtro-laboratorio').val();
        
        // Calculamos la fecha de inicio actual basada en el título o la variable activa
        // (O puedes reutilizar la variable de la semana que tengas en tu script)
        let fechaInicioStr = $('#th-dia-0').attr('data-fecha') || new Date().toISOString().split('T')[0];

        $.ajax({
            url: 'acciones_canva.php',
            type: 'POST',
            dataType: 'json',
            data: {
                accion: 'obtener_detalle_rezagos',
                fecha_inicio: fechaInicioStr,
                laboratorio: labSeleccionado
            },
            success: function(response) {
                if (response.status === 'success') {
                    let tbody = $('#tbody-modal-detalle');
                    tbody.empty();

                    if (response.data.length === 0) {
                        tbody.html('<tr><td colspan="8" class="text-center py-3 text-muted">No hay registros rezagados ni pendientes.</td></tr>');
                    } else {
                        response.data.forEach(item => {
                            let badgeTipo = item.tipo_registro === 'REZAGADO' 
                                ? '<span class="badge bg-danger">Rezagado</span>' 
                                : '<span class="badge bg-success">Actual</span>';

                            let tr = `<tr>
                                <td><strong>${item.folio_registro}</strong><br>${badgeTipo}</td>
                                <td>${item.orden_venta}</td>
                                <td><span class="badge bg-info text-dark">${item.ot}</span></td>
                                <td>${item.cliente || 'N/D'}<br><small class="text-muted">${item.laboratorio}</small></td>
                                <td class="text-center">
                                    <span class="badge ${item.clase_badge}">${item.etapa_rezago}</span><br>
                                    <small class="text-muted">${item.dias_transcurridos} días en empresa</small>
                                </td>
                                <td>${item.fecha_recepcion || 'N/D'}</td>
                                <td>${item.fecha_limite_cierre_ot || 'N/D'}</td>
                                <td class="text-success fw-bold">$${parseFloat(item.valor_ov_usd || 0).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                            </tr>`;
                            tbody.append(tr);
                        });
                    }

                    // Mostrar el modal usando Bootstrap nativo
                    let modal = new bootstrap.Modal(document.getElementById('modalDetalleRezago'));
                    modal.show();
                }
            }
        });
    });

// Abrir modal de rastreo
    $('#btn-abrir-modal-rastreo').click(function() {
        $('#input-buscar-re').val('');
        $('#resultado-rastreo').hide();
        $('#mensaje-busqueda').show().text('Ingresa un folio de recepción para ver su historial completo.');
        let modal = new bootstrap.Modal(document.getElementById('modalRastreoRE'));
        modal.show();
    });

    // Ejecutar búsqueda al dar clic o presionar Enter
    $('#btn-ejecutar-rastreo').click(ejecutarBusquedaRastreo);
    $('#input-buscar-re').keypress(function(e) {
        if (e.which === 13) { ejecutarBusquedaRastreo(); }
    });

    function ejecutarBusquedaRastreo() {
        let folio = $('#input-buscar-re').val().trim();
        if (folio === '') return;

        $.ajax({
            url: 'acciones_canva.php',
            type: 'POST',
            dataType: 'json',
            data: { accion: 'rastrear_equipo_re', folio_re: folio },
            success: function(response) {
                if (response.status === 'success') {
                    let d = response.data;
                    $('#lbl-rastreo-re').text(d.folio_registro);
                    $('#lbl-rastreo-ov').text(d.orden_venta);
                    $('#lbl-rastreo-ot').text(d.ot);
                    $('#lbl-rastreo-cliente').text(d.cliente || 'N/D');
                    $('#lbl-rastreo-lab').text(d.laboratorio || 'N/D');

                    // Construir la línea de tiempo (Timeline)
                    let timelineHtml = '';

                    // Paso 1: Recepción
                    timelineHtml += crearPasoTimeline('1. Recepción en Empresa', d.fecha_recepcion, true, `Medio / Estatus: ${d.status}`);

                    // Paso 2: Transferencia
                    let transCompletada = d.fecha_transferencia != null;
                    timelineHtml += crearPasoTimeline('2. Transferencia al Área', d.fecha_transferencia, transCompletada, transCompletada ? 'Transferido exitosamente' : 'Pendiente de transferir');

                    // Paso 3: Laboratorio / Asignación OT
                    let labCompletado = d.fecha_asignacion_ot != null;
                    timelineHtml += crearPasoTimeline('3. Asignación a Laboratorio', d.fecha_asignacion_ot, labCompletado, `Laboratorio: ${d.laboratorio}`);

                    // Paso 4: Término de OT
                    let terminoCompletado = d.fecha_termino_ot != null;
                    timelineHtml += crearPasoTimeline('4. Proceso Técnico Terminado', d.fecha_termino_ot, terminoCompletado, terminoCompletado ? 'Calibración/Servicio concluido' : 'En proceso en banco');

                    // Paso 5: Cierre Real / Salida
                    let cierreCompletado = d.fecha_real_cierre_ot != null;
                    let descCierre = cierreCompletado ? `Cerrado y Facturado (Factura: ${d.factura})` : `Fecha estimada límite: ${d.fecha_limite_cierre_ot || 'N/D'}`;
                    timelineHtml += crearPasoTimeline('5. Cierre y Facturación', d.fecha_real_cierre_ot, cierreCompletado, descCierre);

                    $('#timeline-etapas').html(timelineHtml);
                    $('#mensaje-busqueda').hide();
                    $('#resultado-rastreo').show();
                } else {
                    $('#resultado-rastreo').hide();
                    $('#mensaje-busqueda').show().html(`<span class="text-danger">${response.message}</span>`);
                }
            }
        });
    }

function crearPasoTimeline(titulo, fecha, completado, descripcion) {
        let colorClase = completado ? 'text-success' : 'text-muted';
        let icono = completado ? '<i class="fas fa-check-circle text-success"></i>' : '<i class="far fa-circle text-gray-300"></i>';
        let fechaTexto = fecha ? fecha : 'Pendiente';
        let estiloLinea = completado ? 'border-success' : 'border-light';

        return `
            <div class="d-flex align-items-start mb-3 position-relative">
                <div class="me-3 fs-5" style="width: 25px; text-align: center;">
                    ${icono}
                </div>
                <div class="flex-grow-1 border-bottom pb-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="font-weight-bold ${colorClase}" style="font-size: 0.85rem;">${titulo}</span>
                        <small class="text-muted" style="font-size: 0.75rem;">${fechaTexto}</small>
                    </div>
                    <small class="text-muted d-block" style="font-size: 0.75rem;">${descripcion}</small>
                </div>
            </div>
        `;
    }

// Forzar el cierre de cualquier modal al hacer clic en sus botones de cierre ("X" o botón Cerrar)
    $(document).on('click', '[data-bs-dismiss="modal"]', function() {
        // Buscamos el contenedor del modal padre más cercano y lo cerramos manualmente
        let modalElement = $(this).closest('.modal');
        if (modalElement.length) {
            let modalInstance = bootstrap.Modal.getInstance(modalElement[0]);
            if (modalInstance) {
                modalInstance.hide();
            } else {
                // Fallback por si la instancia no se inicializó formalmente
                modalElement.removeClass('show').css('display', 'none');
                $('.modal-backdrop').remove();
                $('body').removeClass('modal-open').css('overflow', '');
            }
        }
    });

//fin document.ready
});