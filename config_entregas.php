<?php
// Catálogo único de estatus para Entregas/Enlaces de Logística.
// Fuente de verdad compartida por la vista (selects) y los endpoints (validación server-side).
// Si se agrega o cambia un estatus, hacerlo SOLO aquí.
if (!defined('ESTATUS_ENLACES')) {
    define('ESTATUS_ENLACES', ['Pendiente', 'En tránsito', 'Entregado', 'Reprogramado', 'Cancelado']);
}

// Estatus que exigen un comentario obligatorio al asignarse (validado en cliente y servidor).
if (!defined('ESTATUS_REQUIERE_COMENTARIO')) {
    define('ESTATUS_REQUIERE_COMENTARIO', ['Reprogramado']);
}
?>
