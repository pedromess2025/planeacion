<?php
header('Content-Type: application/json');
// Recibe el archivo de "Análisis de servicios IA" subido desde la tarjeta
// de `carga_sabana.php` y lo guarda en la carpeta hermana "Analisis BI"
// (al mismo nivel que este repo, fuera del repo, sin versionar).
// Modo RECHAZAR lo peligroso, scripts internos permitidos: un HTML con
// código peligroso no se guarda; se responde con el motivo. El <script>
// inline o de messbook.com.mx sí pasa (los reportes lo necesitan). No hay historial ni sufijos: si ya existe un archivo con
// ese mismo nombre sanitizado, SE REEMPLAZA (el historial vive en otro repo).
// El archivo subido NUNCA se incluye, se requiere ni se sirve inline.

require __DIR__ . '/conn.php';
require __DIR__ . '/lib_analisis_ia.php';
mysqli_set_charset($conn, 'utf8mb4');

// Acceso: mismo permiso que la pantalla que lo llama (planeacion/verCargaArchivos).
exigeAccesoEspecialJson($conn, 'planeacion', 'verCargaArchivos');

if (!isset($_FILES['archivo_analisis_ia']) || $_FILES['archivo_analisis_ia']['error'] !== UPLOAD_ERR_OK) {
    // El error más común aquí es que el archivo pase de upload_max_filesize/post_max_size del php.ini
    $errores = [
        UPLOAD_ERR_INI_SIZE   => 'El archivo excede el tamaño máximo permitido por el servidor.',
        UPLOAD_ERR_FORM_SIZE  => 'El archivo excede el tamaño máximo del formulario.',
        UPLOAD_ERR_PARTIAL    => 'El archivo se subió incompleto. Intenta de nuevo.',
        UPLOAD_ERR_NO_FILE    => 'No seleccionaste ningún archivo.',
        UPLOAD_ERR_NO_TMP_DIR => 'El servidor no tiene carpeta temporal configurada.',
        UPLOAD_ERR_CANT_WRITE => 'El servidor no pudo escribir el archivo temporal.',
    ];
    $cod = isset($_FILES['archivo_analisis_ia']) ? $_FILES['archivo_analisis_ia']['error'] : UPLOAD_ERR_NO_FILE;
    echo json_encode(['status' => 'error', 'message' => $errores[$cod] ?? 'No se pudo recibir el archivo.']);
    exit;
}

$archivo = $_FILES['archivo_analisis_ia'];

// Tamaño máximo: 50 MB. Ojo: el servidor además necesita
// upload_max_filesize y post_max_size >= 50M en php.ini; si no, el
// upload falla antes de llegar a este endpoint.
if ($archivo['size'] > ANALISIS_IA_MAX_BYTES) {
    echo json_encode(['status' => 'error', 'message' => 'El archivo excede el tamaño máximo de 50 MB.']);
    exit;
}

// Nombre: el que trae el archivo, ya sanitizado (sin directorios ni
// caracteres raros). Si no queda nada válido, se rechaza.
$nombre = sanitizarNombreArchivo($archivo['name']);
if ($nombre === '') {
    echo json_encode(['status' => 'error', 'message' => 'Nombre de archivo no válido.']);
    exit;
}

// Validación (extensión, doble extensión, contenido; HTML en modo rechazar).
$val = validarArchivoAnalisisIA($archivo['tmp_name'], $nombre);
if (!$val['ok']) {
    echo json_encode(['status' => 'error', 'message' => $val['motivo']]);
    exit;
}

// Destino: carpeta hermana del repo, con espacio, sin acento.
$dirDestino = __DIR__ . '/../Analisis BI/';
if (!is_dir($dirDestino)) {
    if (!mkdir($dirDestino, 0755, true)) {
        echo json_encode(['status' => 'error', 'message' => 'No se pudo crear la carpeta de destino (Analisis BI).']);
        exit;
    }
}
$dirReal = realpath($dirDestino);
if ($dirReal === false) {
    echo json_encode(['status' => 'error', 'message' => 'No se pudo resolver la carpeta de destino (Analisis BI).']);
    exit;
}
$destino = $dirReal . DIRECTORY_SEPARATOR . $nombre;
// El destino final debe quedar dentro de la carpeta Analisis BI.
if (strpos($destino, $dirReal . DIRECTORY_SEPARATOR) !== 0) {
    echo json_encode(['status' => 'error', 'message' => 'Ruta de destino no válida.']);
    exit;
}

// Defensa en profundidad: si la carpeta llegara a servirse por web, que
// nunca se ejecute nada en ella. Solo se crea si no existe.
// "php_flag engine off" solo vale con mod_php: si el servidor usa PHP-FPM
// esa línea puede dar error 500, así que en FPM se escribe sin ella
// (el FilesMatch seguiría bloqueando los ejecutables).
$htaccess = $dirReal . DIRECTORY_SEPARATOR . '.htaccess';
if (!file_exists($htaccess)) {
    $sapi = php_sapi_name();
    $esFpm = ($sapi === 'fpm-fcgi' || stripos((string)$sapi, 'fpm') !== false);
    $lineas = [];
    $lineas[] = 'Options -ExecCGI -Indexes';
    if (!$esFpm) {
        $lineas[] = 'php_flag engine off';
    }
    $lineas[] = 'RemoveHandler .php .phtml .phar .cgi .pl .py';
    $lineas[] = 'RemoveType .php .phtml .phar';
    $lineas[] = '<FilesMatch "\.(php|phtml|phar|cgi|pl|py|sh|exe|com|bat)$">';
    $lineas[] = '  Require all denied';
    $lineas[] = '</FilesMatch>';
    // CSP sandbox allow-scripts (SIN allow-same-origin): los reportes de
    // BI necesitan JS para filtros y gráficas, así que el script sí corre,
    // pero el documento queda aislado de loginMaster (no toca su sesión
    // ni puede salirse del frame). La subida ya rechaza el script peligroso.
    $lineas[] = '<FilesMatch "\.html?$">';
    $lineas[] = '  Header set Content-Security-Policy "sandbox allow-scripts"';
    $lineas[] = '  Header set X-Content-Type-Options "nosniff"';
    $lineas[] = '</FilesMatch>';
    @file_put_contents($htaccess, implode("\n", $lineas) . "\n");
}

// Sin historial ni sufijos: si ya existe, se reemplaza.
$reemplazado = file_exists($destino);
if (!move_uploaded_file($archivo['tmp_name'], $destino)) {
    echo json_encode(['status' => 'error', 'message' => 'No se pudo guardar el archivo en la carpeta Analisis BI.']);
    exit;
}
@chmod($destino, 0644);

echo json_encode([
    'status'      => 'success',
    'archivo'     => $nombre,
    'extension'   => strtolower(pathinfo($nombre, PATHINFO_EXTENSION)),
    'tamano'      => filesize($destino),
    'reemplazado' => $reemplazado,
    'mensaje'     => $reemplazado
        ? 'Se reemplazó el archivo anterior con ese nombre.'
        : 'Archivo guardado en Analisis BI.',
]);
