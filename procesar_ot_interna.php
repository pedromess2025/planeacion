<?php
header('Content-Type: application/json');
// Recibe el reporte de OT internas subido desde la tarjeta de `carga_sabana.php` y lo importa.
// La importación en sí vive en `lib_ot_interna.php`, la misma que usa el CLI `cargar_ot_interna.php`.

require __DIR__ . '/conn.php';
require __DIR__ . '/lib_ot_interna.php';
mysqli_set_charset($conn, 'utf8mb4');

// Acceso: mismo permiso que la pantalla que lo llama (planeacion/verCargaArchivos). Reemplaza
// el reporte completo de OT internas, así que no basta con tener sesión.
exigeAccesoEspecialJson($conn, 'planeacion', 'verCargaArchivos');

if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
    // El error más común aquí es que el archivo pase de upload_max_filesize/post_max_size del php.ini
    $errores = [
        UPLOAD_ERR_INI_SIZE   => 'El archivo excede el tamaño máximo permitido por el servidor.',
        UPLOAD_ERR_FORM_SIZE  => 'El archivo excede el tamaño máximo del formulario.',
        UPLOAD_ERR_PARTIAL    => 'El archivo se subió incompleto. Intenta de nuevo.',
        UPLOAD_ERR_NO_FILE    => 'No seleccionaste ningún archivo.',
        UPLOAD_ERR_NO_TMP_DIR => 'El servidor no tiene carpeta temporal configurada.',
        UPLOAD_ERR_CANT_WRITE => 'El servidor no pudo escribir el archivo temporal.',
    ];
    $cod = isset($_FILES['archivo']) ? $_FILES['archivo']['error'] : UPLOAD_ERR_NO_FILE;
    echo json_encode(['status' => 'error', 'message' => $errores[$cod] ?? 'No se pudo recibir el archivo.']);
    exit;
}

$nombreOriginal = $_FILES['archivo']['name'];
$ext = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
if ($ext !== 'csv') {
    echo json_encode(['status' => 'error',
                      'message' => 'El reporte debe ser un archivo .csv (recibí ".' . htmlspecialchars($ext) . '"). '
                                 . 'Si lo tienes en Excel, guárdalo como CSV.']);
    exit;
}

// Se lee directo del archivo temporal de PHP: no hace falta conservarlo en el servidor.
$r = otiImportar($conn, $_FILES['archivo']['tmp_name']);
if (!$r['ok']) {
    echo json_encode(['status' => 'error', 'message' => $r['mensaje']]);
    exit;
}

echo json_encode([
    'status'    => 'success',
    'archivo'   => $nombreOriginal,
    'filas'     => $r['filas'],
    'omitidas'  => $r['omitidas'],
    'rango'     => $r['rango'],
    'sin_match' => $r['sin_match'],
]);
