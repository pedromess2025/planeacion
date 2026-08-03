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
});