/**
 * HELPERS.JS - Funciones comunes reutilizables
 * Reduce código duplicado y centraliza lógica común
 */

// ============ AJAX HELPER ============
// Realiza llamadas AJAX genéricas al backend
// Centraliza configuración y manejo de errores
function creaAJAX(accion, data = {}, callback = null) {
    const ajaxData = { accion: accion, ...data };
    
    $.ajax({
        url: 'accionesEntradas.php',
        method: 'POST',
        dataType: 'json',
        data: ajaxData,
        success: function(response) {
            if (callback) callback(response);
        },
        error: function() {
            muestraAlerta('error', 'Error de conexión', 'No se pudo conectar con el servidor.');
        }
    });
}

// ============ ALERTS HELPER ============
// Muestra alertas SweetAlert2 personalizadas
// Unifica notificaciones al usuario en toda la aplicación
function muestraAlerta(icon, title, text, onConfirm = null) {
    const alert = Swal.fire({ icon, title, text });
    
    if (onConfirm) {
        alert.then(onConfirm);
    }
    return alert;
}

// Muestra alerta de éxito y recarga la página
// Útil después de operaciones exitosas que requieren actualización
function alertaExito(title, text = 'Operación completada correctamente.') {
    return Swal.fire({ icon: 'success', title, text }).then(() => location.reload());
}

// ============ MODAL HELPER ============
// Abre modales de Bootstrap y retorna su instancia
// Facilita el control programático de modales
function abreModal(modalId) {
    const modalEl = document.getElementById(modalId);
    if (!modalEl) return null;
    
    const instance = bootstrap.Modal.getOrCreateInstance(modalEl);
    instance.show();
    return instance;
}

// Cierra modal y limpia backdrop residual
// Previene problemas de superposición de fondos oscuros
function cierraModal(modalInstance) {
    if (modalInstance) {
        modalInstance.hide();
    }
    // Limpiar backdrop
    document.body.classList.remove('modal-open');
    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
}

// Inicializa Select2 con configuración estándar
// Permite búsqueda mejorada en dropdowns
function Select2(selector, parentSelector = null) {
    const config = {
        language: 'es',
        placeholder: 'Buscar...',
        allowClear: false,
        width: '100%'
    };
    
    if (parentSelector) {
        config.dropdownParent = $(parentSelector);
    }
    
    $(selector).select2(config);
}

// ============ DATE HELPER ============
// Obtiene fecha actual en formato YYYY-MM-DD
// Compatible con inputs tipo date
function obtenFechaHoy() {
    return new Date().toISOString().split('T')[0];
}

// Formatea fechas en español (México)
// Convierte YYYY-MM-DD a formato legible
function formateaFecha(fecha) {
    const opciones = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(fecha).toLocaleDateString('es-MX', opciones);
}

// Calcula días entre hoy y fecha objetivo
// Retorna negativo si la fecha ya pasó
function calculaDias(fecha) {
    const hoy = new Date();
    hoy.setHours(0, 0, 0, 0);
    const target = new Date(fecha);
    target.setHours(0, 0, 0, 0);
    return Math.ceil((target - hoy) / (1000 * 60 * 60 * 24));
}

// Retorna clase CSS según proximidad de fecha
// Aplica colores de alerta: rojo (vencido), amarillo (próximo), verde (normal)
function claseAlertaFecha(dias) {
    if (dias < 0) return 'text-danger';
    if (dias <= 3) return 'text-warning';
    return 'text-success';
}

// ============ COOKIE HELPER ============
// Obtiene valor de cookie por nombre
// Facilita lectura de cookies del navegador
function cookies(name) {
    const nameEQ = name + "=";
    const cookiesArray = document.cookie.split(';');
    for (let i = 0; i < cookiesArray.length; i++) {
        let cookie = cookiesArray[i].trim();
        if (cookie.indexOf(nameEQ) === 0) {
            return cookie.substring(nameEQ.length);
        }
    }
    return null;
}

// ============ DOM HELPER ============
// Llena dropdowns desde el backend vía AJAX
// Automatiza carga de catálogos (ingenieros, áreas, etc.)
function cargaOpcionesSelect(selectSelector, action, labelKey, valueKey, onLoaded = null) {
    creaAJAX(action, {}, function(response) {
        const select = $(selectSelector);
        let data = response.data || response;
        
        if (!Array.isArray(data)) data = [];
        
        select.empty();
        select.append($('<option></option>').attr('value', '').text('Selecciona...'));
        
        data.forEach(function(item) {
            select.append(
                $('<option></option>')
                    .attr('value', item[valueKey])
                    .text(item[labelKey])
            );
        });
        
        if (onLoaded) onLoaded();
    });
}

// ============ VALIDATION HELPER ============
// Valida campos obligatorios
// Muestra alerta si está vacío
function validaCampoVacio(value, fieldName) {
    if (!value || value.trim() === '') {
        muestraAlerta('warning', 'Campo requerido', `${fieldName} es obligatorio.`);
        return false;
    }
    return true;
}
