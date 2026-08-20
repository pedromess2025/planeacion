$(document).ready(function() {
    let fechaActualPivot = new Date();
    
    cargarLaboratorios();
    cargarTableroDia(fechaActualPivot);

    // Navegación diaria
    $('#btn-dia-anterior').click(function() {
        fechaActualPivot.setDate(fechaActualPivot.getDate() - 1);
        cargarTableroDia(fechaActualPivot);
    });

    $('#btn-dia-actual').click(function() {
        fechaActualPivot = new Date();
        cargarTableroDia(fechaActualPivot);
    });

    $('#btn-dia-siguiente').click(function() {
        fechaActualPivot.setDate(fechaActualPivot.getDate() + 1);
        cargarTableroDia(fechaActualPivot);
    });

    $('#filtro-laboratorio').change(function() {
        cargarTableroDia(fechaActualPivot);
    });

    function cargarLaboratorios() {
        $.ajax({
            url: 'acciones_canva.php',
            type: 'POST',
            dataType: 'json',
            data: { accion: 'obtener_laboratorios' },
            success: function(response) {
                if (response.status === 'success') {
                    let select = $('#filtro-laboratorio');
                    select.html('<option value="TODOS">Todos los Laboratorios</option>');
                    response.data.forEach(lab => {
                        select.append(`<option value="${lab}">${lab}</option>`);
                    });
                }
            }
        });
    }

    function cargarTableroDia(pivotDate) {
        let anio = pivotDate.getFullYear();
        let mes = ('0' + (pivotDate.getMonth() + 1)).slice(-2);
        let dia = ('0' + pivotDate.getDate()).slice(-2);
        let fechaFormat = `${anio}-${mes}-${dia}`;
        
        let opcionesFormato = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        let nombreDiaLargo = pivotDate.toLocaleDateString('es-ES', opcionesFormato);

        $('#rango-semana-titulo').text(`Operación del día: ${nombreDiaLargo.charAt(0).toUpperCase() + nombreDiaLargo.slice(1)}`);

        let labSeleccionado = $('#filtro-laboratorio').val();

        $.ajax({
            url: 'acciones_canva.php',
            type: 'POST',
            dataType: 'json',
            data: { accion: 'cargar_tablero', fecha_pivot: fechaFormat, laboratorio: labSeleccionado },
            success: function(response) {
                if (response.status === 'success') dibujarTableroDia(response.data, fechaFormat, nombreDiaLargo);
            }
        });
    }

    function dibujarTableroDia(registros, fechaActualStr, nombreDia) {
        let tbody = $('#kanban-tbody');
        tbody.empty();

        let totales = { RECEPCION: 0, TRANSFERENCIA: 0, LABORATORIO: 0, TERMINADO: 0, CERRADO: 0 };
        let tr = $('<tr></tr>');
        
        tr.append(`<td class="align-middle text-center bg-light">
            <strong class="text-dark d-block text-capitalize">${nombreDia.split(',')[0]}</strong>
            <small class="text-muted">${fechaActualStr}</small>
        </td>`);

        let tds = {
            RECEPCION: $('<td style="vertical-align: top;"></td>'),
            TRANSFERENCIA: $('<td style="vertical-align: top;"></td>'),
            LABORATORIO: $('<td style="vertical-align: top;"></td>'),
            TERMINADO: $('<td style="vertical-align: top;"></td>'),
            CERRADO: $('<td style="vertical-align: top;"></td>')
        };

        let currentTimestamp = new Date(fechaActualStr + "T00:00:00").getTime();

        registros.forEach(reg => {
            let esRezagado = false;
            let diasRetraso = 0;
            
            if (reg.fecha_origen && reg.fecha_origen < fechaActualStr) {
                esRezagado = true;
                let regTimestamp = new Date(reg.fecha_origen + "T00:00:00").getTime();
                diasRetraso = Math.floor((currentTimestamp - regTimestamp) / (1000 * 60 * 60 * 24));
            }

            let estiloBorde = reg.es_planeada_card 
                ? (esRezagado ? 'border-left: 5px solid #e74a3b; background-color: #fdf3f2;' : 'border-left: 5px solid #1d3557; background-color: #e2eafc;') 
                : (esRezagado ? 'border-left: 4px solid #e74a3b;' : 'border-left: 3px solid #4e73df;');
                
            let badgePlaneada = reg.es_planeada_card 
                ? '<span style="background: #1d3557; color: #fff; padding: 1px 5px; border-radius: 3px; font-size: 0.65rem; margin-bottom: 2px; display: inline-block; font-weight: bold;"><i class="fas fa-calendar-check"></i> Carga Planeada</span><br>' 
                : '';
                
            let alertaRezagoHtml = esRezagado 
                ? `<div class="text-danger mb-1" style="font-size: 0.65rem; text-transform: uppercase;">
                       <strong style="display:block;"><i class="fas fa-exclamation-triangle"></i> Rezagado (${diasRetraso} día${diasRetraso > 1 ? 's' : ''})</strong>
                       <span style="font-size: 0.6rem; color: #858796;">En etapa desde: <b>${reg.fecha_etapa_actual}</b></span>
                   </div>`
                : '';

            let cardHtml = `
                <div style="background: #fff; border: 1px solid #d1d3e2; ${estiloBorde} padding: 6px; margin-bottom: 6px; border-radius: 4px; font-size: 0.75rem; text-align: left; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                    ${alertaRezagoHtml}
                    <div style="display: flex; justify-content: space-between; margin-bottom: 2px;">
                        <strong style="color: #3a3b45;">${reg.folio}</strong>                         
                    </div>
                    <div style="margin-bottom: 2px;">
                        ${badgePlaneada}
                        <span style="background: #f6c23e; color: #fff; padding: 1px 4px; border-radius: 3px; font-size: 0.65rem;">${reg.orden_venta}</span>
                    </div>
                    <div style="margin-bottom: 2px; display: flex; justify-content: space-between; align-items: center;">
                        <span style="background: #36b9cc; color: #fff; padding: 1px 4px; border-radius: 3px; font-size: 0.65rem;">${reg.ot}</span>                        
                    </div>
                    <div style="color: #6c757d; font-size: 0.7rem; margin-bottom: 2px;"><i class="fas fa-flask"></i> ${reg.laboratorio}</div>
                    <div style="color: #1cc88a; font-weight: bold; font-size: 0.75rem; margin-top: 2px;">$${reg.valor_usd.toLocaleString('en-US', {minimumFractionDigits: 2})} USD</div>
                </div>
            `;
            
            totales[reg.columna] += reg.valor_usd;
            tds[reg.columna].append(cardHtml);
        });

        tr.append(tds.RECEPCION, tds.TRANSFERENCIA, tds.LABORATORIO, tds.TERMINADO, tds.CERRADO);
        tbody.append(tr);

        $('#tot-recepcion').text(`$${totales.RECEPCION.toLocaleString('en-US', {minimumFractionDigits: 2})}`);
        $('#tot-transferencia').text(`$${totales.TRANSFERENCIA.toLocaleString('en-US', {minimumFractionDigits: 2})}`);
        $('#tot-laboratorio').text(`$${totales.LABORATORIO.toLocaleString('en-US', {minimumFractionDigits: 2})}`);
        $('#tot-terminado').text(`$${totales.TERMINADO.toLocaleString('en-US', {minimumFractionDigits: 2})}`);
        $('#tot-cerrado').text(`$${totales.CERRADO.toLocaleString('en-US', {minimumFractionDigits: 2})}`);
    }

    // Modal Rezagados
    $('#btn-abrir-modal-rezago').click(function() {
        let labSeleccionado = $('#filtro-laboratorio').val() || 'TODOS';
        let fechaInicioStr = new Date().toISOString().split('T')[0];

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
                        tbody.html('<tr><td colspan="8" class="text-center py-3 text-muted">No hay registros rezagados ni atrasados.</td></tr>');
                    } else {
                        response.data.forEach(item => {
                            let badgeTipo = `<span class="badge ${item.clase_tipo}">${item.texto_tipo}</span>`;
                            let badgeCuarentena = item.es_cuarentena ? '<span class="badge bg-secondary ml-1"><i class="fas fa-shield-alt"></i> Cuarentena</span>' : '';
                            let badgeDias = `<span class="badge bg-light text-dark border mt-1"><i class="far fa-clock"></i> ${item.dias_transcurridos} días en empresa</span>`;

                            let tr = `<tr>
                                <td><strong>${item.folio_registro}</strong><br>${badgeTipo}</td>
                                <td>${item.orden_venta}</td>
                                <td><span class="badge bg-info text-dark">${item.ot}</span></td>
                                <td>${item.cliente || 'N/D'}<br><small class="text-muted">${item.laboratorio}</small></td>
                                <td class="text-center">
                                    <span class="badge ${item.clase_badge}">${item.etapa_rezago}</span>
                                    ${badgeCuarentena}<br>${badgeDias}
                                </td>
                                <td>${item.fecha_recepcion || 'N/D'}</td>
                                <td>${item.fecha_limite_cierre_ot || 'N/D'}</td>
                                <td class="text-success fw-bold">$${parseFloat(item.valor_ov_usd || 0).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                            </tr>`;
                            tbody.append(tr);
                        });
                    }
                    new bootstrap.Modal(document.getElementById('modalDetalleRezago')).show();
                }
            }
        });
    });

    // Rastreo
    $('#btn-abrir-modal-rastreo').click(function() {
        $('#input-buscar-re').val('');
        $('#resultado-rastreo').hide();
        $('#mensaje-busqueda').show().text('Ingresa un folio de recepción para ver su historial completo.');
        new bootstrap.Modal(document.getElementById('modalRastreoRE')).show();
    });

    $('#btn-ejecutar-rastreo').click(ejecutarBusquedaRastreo);
    $('#input-buscar-re').keypress(function(e) { if (e.which === 13) ejecutarBusquedaRastreo(); });

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

                    let timelineHtml = '';
                    timelineHtml += crearPasoTimeline('1. Recepción en Empresa', d.fecha_recepcion, true, `Medio / Estatus: ${d.status}`);
                    
                    let transCompletada = d.fecha_transferencia != null;
                    timelineHtml += crearPasoTimeline('2. Transferencia al Área', d.fecha_transferencia, transCompletada, transCompletada ? 'Transferido exitosamente' : 'Pendiente de transferir');

                    let labCompletado = d.fecha_asignacion_ot != null;
                    timelineHtml += crearPasoTimeline('3. Asignación a Laboratorio', d.fecha_asignacion_ot, labCompletado, `Laboratorio: ${d.laboratorio}`);

                    let terminoCompletado = d.fecha_termino_ot != null;
                    timelineHtml += crearPasoTimeline('4. Proceso Técnico Terminado', d.fecha_termino_ot, terminoCompletado, terminoCompletado ? 'Calibración/Servicio concluido' : 'En proceso en banco');

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

        return `
            <div class="d-flex align-items-start mb-3 position-relative">
                <div class="me-3 fs-5" style="width: 25px; text-align: center;">${icono}</div>
                <div class="flex-grow-1 border-bottom pb-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="font-weight-bold ${colorClase}" style="font-size: 0.85rem;">${titulo}</span>
                        <small class="text-muted" style="font-size: 0.75rem;">${fechaTexto}</small>
                    </div>
                    <small class="text-muted d-block" style="font-size: 0.75rem;">${descripcion}</small>
                </div>
            </div>`;
    }

    $(document).on('click', '[data-bs-dismiss="modal"]', function() {
        let modalElement = $(this).closest('.modal');
        if (modalElement.length) {
            let modalInstance = bootstrap.Modal.getInstance(modalElement[0]);
            if (modalInstance) {
                modalInstance.hide();
            } else {
                modalElement.removeClass('show').css('display', 'none');
                $('.modal-backdrop').remove();
                $('body').removeClass('modal-open').css('overflow', '');
            }
        }
    });
});