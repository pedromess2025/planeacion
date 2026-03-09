<?php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
header('X-Debug-File: ' . __FILE__);
include 'conn.php';

$noEmpleado = $_POST['noEmpleado'] ?? '';
$sistema = $_POST['sistema'] ?? '';
$archivo = $_POST['archivo'] ?? '';
$idRegistro = $_POST['idRegistro'] ?? '';

if ($noEmpleado === '') {
    echo json_encode([
        'success' => false,
        'status' => 'error',
        'mensaje' => 'Falta noEmpleado.'
    ]);
    exit;
}

$sql = "SELECT id_usuario, nombre, noEmpleado, rol, correo, departamento, diasdisponibles, TIMESTAMPDIFF(YEAR, fechaIngreso, CURDATE()) AS antiguedad FROM usuarios WHERE noEmpleado = ? AND estatus = 1 LIMIT 1";
$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    echo json_encode([
        'success' => false,
        'status' => 'error',
        'mensaje' => 'No se pudo preparar la validacion.'
    ]);
    exit;
}

mysqli_stmt_bind_param($stmt, 's', $noEmpleado);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

if ($resultado && mysqli_num_rows($resultado) === 1) {
    $usuario = mysqli_fetch_assoc($resultado);

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $_SESSION['nombredelusuario'] = $usuario['nombre'];
    $_SESSION['noEmpleado'] = $usuario['noEmpleado'];
    $_SESSION['rol'] = $usuario['rol'];
    $_SESSION['correo'] = $usuario['correo'];
    $_SESSION['id_usuario'] = $usuario['id_usuario'];

    $cookieOpts = [
        'expires' => time() + 86400,
        'path' => '/',
        'samesite' => 'Lax'
    ];

    setcookie('id_usuario', (string)$usuario['id_usuario'], $cookieOpts);
    setcookie('antiguedad', (string)$usuario['antiguedad'], $cookieOpts);
    setcookie('nombredelusuario', (string)$usuario['nombre'], $cookieOpts);
    setcookie('noEmpleado', (string)$usuario['noEmpleado'], $cookieOpts);
    setcookie('diasD', (string)$usuario['diasdisponibles'], $cookieOpts);
    setcookie('departamento', (string)$usuario['departamento'], $cookieOpts);
    setcookie('rol', (string)$usuario['rol'], $cookieOpts);

    $sesionOpts = [
        'expires' => time() + 99999000,
        'path' => '/',
        'samesite' => 'Lax'
    ];
    setcookie('SesionLogin', 'LoginMaster', $sesionOpts);

    echo json_encode([
        'success' => true,
        'status' => 'success',
        'mensaje' => 'Validacion correcta.',
        'sistema' => $sistema,
        'archivo' => $archivo,
        'idRegistro' => $idRegistro
    ]);
    exit;
}

echo json_encode([
    'success' => false,
    'status' => 'error',
    'mensaje' => 'Usuario no valido o inactivo.'
]);
exit;
?>