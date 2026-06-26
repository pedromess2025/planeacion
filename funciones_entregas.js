// Funciones de la vista de Entregas / Enlaces (Logística).
// Endpoints en acciones_entregas.php. La página expone window.ESTATUS_ENLACES y
// window.ESTATUS_REQUIERE_COMENTARIO (desde config_entregas.php).

var _enlaces = {}; // mapa id -> enlace, para editar sin re-consultar

//FUNCION PARA OBTENER EL VALOR DE LA COOKIE
function getCookie(name) {
    const cookies = new URLSearchParams(document.cookie.replace(/; /g, '&'));
    return cookies.get(name) || undefined;
}

// Escapa texto para insertarlo en HTML (comentarios libres)
function escapeHtml(s) {
    return String(s == null ? '' : s)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

// Badge de color según estatus. Si hay comentario, se muestra al pasar el cursor (tooltip nativo).
function badgeEstatus(estatus, comentario) {
    var map = {
        'Pendiente':    'text-bg-secondary',
        'En tránsito':  'text-bg-primary',
        'Entregado':    'text-bg-success',
        'Reprogramado': 'text-bg-warning',
        'Cancelado':    'text-bg-danger'
    };
    var cls = map[estatus] || 'text-bg-secondary';
    var extra = comentario ? ' title="' + escapeHtml(comentario) + '" style="cursor:help"' : '';
    return '<span class="badge ' + cls + '"' + extra + '>' + (estatus || '') + '</span>';
}

// Botones de acción por fila (solo pasan el id; los datos se leen de _enlaces)
function accionesEnlace(e) {
    return '<div class="btn-group" role="group">' +
        '<button class="btn btn-sm btn-outline-secondary" title="Editar" onclick="editarEnlace(' + e.id + ')"><i class="fas fa-edit"></i></button>' +
        '<button class="btn btn-sm btn-outline-primary" title="Cambiar estatus" onclick="cambiarEstatusEnlace(' + e.id + ')"><i class="fas fa-exchange-alt"></i></button>' +
        '<button class="btn btn-sm btn-outline-info" title="Historial de estatus" onclick="verHistorialEnlace(' + e.id + ')"><i class="fas fa-history"></i></button>' +
        '</div>';
}

// Carga los empleados activos en el select de responsable; ejecuta cb() al terminar.
function cargarEmpleadosResponsable(cb) {
    $.ajax({
        url: 'acciones_entregas.php',
        method: 'POST',
        dataType: 'json',
        data: { opcion: 'empleadosEntrega' },
        success: function (resp) {
            var sel = $('#enlaceResponsable');
            sel.find('option:not(:first)').remove();
            if (resp.status === 'success') {
                resp.empleados.forEach(function (u) {
                    sel.append($('<option></option>').attr('value', u.nombre).text(u.nombre));
                });
            }
            if (typeof cb === 'function') cb();
        },
        error: function () { if (typeof cb === 'function') cb(); }
    });
}

// Carga la tabla de enlaces (con filtro opcional por estatus)
function cargarEnlaces() {
    var estatus = $('#filtroEstatusEnlace').val() || '';
    $.ajax({
        url: 'acciones_entregas.php',
        method: 'POST',
        dataType: 'json',
        data: { opcion: 'listarEnlaces', estatus: estatus },
        success: function (resp) {
            var table = $('#TEnlaces').DataTable();
            table.clear();
            _enlaces = {};
            if (resp.status === 'success') {
                resp.enlaces.forEach(function (e) {
                    _enlaces[e.id] = e;
                    table.row.add([
                        e.folio || '',
                        (e.origen || '') + ' &rarr; ' + (e.destino || ''),
                        e.contenido || '',
                        e.responsable || '',
                        e.fecha_envio || '',
                        e.fecha_entrega_estimada || '',
                        badgeEstatus(e.estatus, e.comentario),
                        accionesEnlace(e)
                    ]);
                });
            }
            table.draw();
        },
        error: function () {
            Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudieron cargar los enlaces.' });
        }
    });
}

// "YYYY-MM-DD HH:MM:SS" -> "YYYY-MM-DDTHH:MM" para inputs datetime-local
function toDatetimeLocal(mysqlDt) {
    if (!mysqlDt) return '';
    return mysqlDt.replace(' ', 'T').substring(0, 16);
}

function nuevoEnlace() {
    abrirModalEnlace(null);
}

function editarEnlace(id) {
    abrirModalEnlace(_enlaces[id] || null);
}

// Carga el modal (archivo aparte) y lo prepara
function abrirModalEnlace(e) {
    $('#contenedorModalEnlace').load('modalEnlace.php', function () {
        $('#formEnlace')[0].reset();
        $('#enlaceId').val('');
        $('#modalEnlaceTitulo').text('Nuevo enlace');

        // Carga el catálogo de empleados; al terminar, si es edición fija los valores
        cargarEmpleadosResponsable(function () {
            if (e) {
                $('#modalEnlaceTitulo').text('Editar enlace');
                $('#enlaceId').val(e.id);
                $('#enlaceOrigen').val(e.origen);
                $('#enlaceDestino').val(e.destino);
                $('#enlaceContenido').val(e.contenido);
                $('#enlaceResponsable').val(e.responsable || '');
                $('#enlaceFechaEnvio').val(toDatetimeLocal(e.fecha_envio));
                $('#enlaceFechaEntrega').val(toDatetimeLocal(e.fecha_entrega_estimada));
                $('#enlaceEstatus').val(e.estatus);
                $('#enlaceComentario').val(e.comentario || '');
            }
            actualizarHintComentario();
        });

        $('#enlaceEstatus').on('change', actualizarHintComentario);
        $('#btnGuardarEnlace').off('click').on('click', guardarEnlace);
        $('#modalEnlace').modal('show');
    });
}

function actualizarHintComentario() {
    var req = (window.ESTATUS_REQUIERE_COMENTARIO || []).indexOf($('#enlaceEstatus').val()) !== -1;
    $('#enlaceComentarioHint').toggle(req);
}

function guardarEnlace() {
    var estatus    = $('#enlaceEstatus').val();
    var comentario = ($('#enlaceComentario').val() || '').trim();
    var origen     = ($('#enlaceOrigen').val() || '').trim();
    var destino    = ($('#enlaceDestino').val() || '').trim();
    var contenido  = ($('#enlaceContenido').val() || '').trim();

    if (!origen || !destino || !contenido) {
        Swal.fire({ icon: 'warning', title: 'Faltan datos', text: 'Origen, destino y contenido son obligatorios.' });
        return;
    }
    if ((window.ESTATUS_REQUIERE_COMENTARIO || []).indexOf(estatus) !== -1 && comentario === '') {
        Swal.fire({ icon: 'warning', title: 'Comentario requerido', text: 'El estatus "' + estatus + '" requiere un comentario.' });
        return;
    }

    $('#btnGuardarEnlace').prop('disabled', true);
    $.ajax({
        url: 'acciones_entregas.php',
        method: 'POST',
        dataType: 'json',
        data: {
            opcion: 'guardarEnlace',
            id: $('#enlaceId').val(),
            origen: origen,
            destino: destino,
            contenido: contenido,
            responsable: $('#enlaceResponsable').val(),
            fecha_envio: $('#enlaceFechaEnvio').val(),
            fecha_entrega_estimada: $('#enlaceFechaEntrega').val(),
            estatus: estatus,
            comentario: comentario
        },
        success: function (resp) {
            $('#btnGuardarEnlace').prop('disabled', false);
            if (resp.status === 'success') {
                $('#modalEnlace').modal('hide');
                Swal.fire({ icon: 'success', title: 'Enlace guardado', timer: 1200, showConfirmButton: false });
                cargarEnlaces();
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: resp.message || 'No se pudo guardar.' });
            }
        },
        error: function () {
            $('#btnGuardarEnlace').prop('disabled', false);
            Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo guardar el enlace.' });
        }
    });
}

// Cambio de estatus inline (Swal con select + comentario; obligatorio si el estatus lo requiere)
function cambiarEstatusEnlace(id) {
    var estatusActual = (_enlaces[id] || {}).estatus || '';
    var opciones = (window.ESTATUS_ENLACES || []).map(function (s) {
        var sel = (s === estatusActual) ? ' selected' : '';
        return '<option value="' + s + '"' + sel + '>' + s + '</option>';
    }).join('');

    Swal.fire({
        title: 'Cambiar estatus',
        html:
            '<select id="swalEstatus" class="form-select mb-2">' + opciones + '</select>' +
            '<textarea id="swalComentario" class="form-control" rows="3" placeholder="Comentario"></textarea>' +
            '<small id="swalHint" class="text-danger d-block mt-1"></small>',
        showCancelButton: true,
        confirmButtonText: 'Guardar',
        cancelButtonText: 'Cancelar',
        didOpen: function () {
            var upd = function () {
                var req = (window.ESTATUS_REQUIERE_COMENTARIO || []).indexOf(document.getElementById('swalEstatus').value) !== -1;
                document.getElementById('swalHint').textContent = req ? 'Comentario obligatorio para este estatus.' : '';
            };
            document.getElementById('swalEstatus').addEventListener('change', upd);
            upd();
        },
        preConfirm: function () {
            var est = document.getElementById('swalEstatus').value;
            var com = (document.getElementById('swalComentario').value || '').trim();
            if ((window.ESTATUS_REQUIERE_COMENTARIO || []).indexOf(est) !== -1 && com === '') {
                Swal.showValidationMessage('El estatus "' + est + '" requiere un comentario.');
                return false;
            }
            return { estatus: est, comentario: com };
        }
    }).then(function (result) {
        if (!result.isConfirmed) return;
        $.ajax({
            url: 'acciones_entregas.php',
            method: 'POST',
            dataType: 'json',
            data: { opcion: 'cambiarEstatusEnlace', id: id, estatus: result.value.estatus, comentario: result.value.comentario },
            success: function (resp) {
                if (resp.status === 'success') {
                    Swal.fire({ icon: 'success', title: 'Estatus actualizado', timer: 1200, showConfirmButton: false });
                    cargarEnlaces();
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: resp.message || 'No se pudo actualizar.' });
                }
            },
            error: function () {
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo actualizar el estatus.' });
            }
        });
    });
}

// Muestra el histórico de cambios de estatus (con su comentario y fecha) de un enlace.
function verHistorialEnlace(id) {
    $.ajax({
        url: 'acciones_entregas.php',
        method: 'POST',
        dataType: 'json',
        data: { opcion: 'historialEnlace', id: id },
        success: function (resp) {
            if (resp.status !== 'success') {
                Swal.fire({ icon: 'error', title: 'Error', text: resp.message || 'No se pudo obtener el historial.' });
                return;
            }
            var html;
            if (!resp.historial || !resp.historial.length) {
                html = '<p class="text-muted mb-0">Sin cambios de estatus registrados.</p>';
            } else {
                html = '<div class="text-start">';
                resp.historial.forEach(function (h) {
                    html += '<div class="mb-2 pb-2 border-bottom">' +
                        '<div>' + badgeEstatus(h.estatus) + ' <small class="text-muted">' + escapeHtml(h.fecha || '') + '</small></div>' +
                        (h.comentario ? '<div class="mt-1">' + escapeHtml(h.comentario) + '</div>' : '') +
                        '</div>';
                });
                html += '</div>';
            }
            Swal.fire({ title: 'Historial de estatus', html: html, width: 600 });
        },
        error: function () {
            Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo obtener el historial.' });
        }
    });
}
