<?php
    session_start();
    include 'conn.php';
    if ($_COOKIE['noEmpleado'] == '' || $_COOKIE['noEmpleado'] == null) {
        echo '<script>window.location.assign("index")</script>';
        exit;
    }

    // El enlace de PowerBI se resuelve en el servidor a partir del id del ingeniero (no viaja en la URL).
    $idIng     = isset($_GET['ing']) ? intval($_GET['ing']) : 0;
    $enlace    = '';
    $nombreIng = '';
    if ($idIng > 0) {
        $st = $conn->prepare("SELECT COALESCE(NULLIF(TRIM(CONCAT_WS(' ', nombres, apellidos)), ''), nombre) AS nombre, noEmpleado
                              FROM usuarios WHERE id_usuario = ? LIMIT 1");
        $st->bind_param('i', $idIng);
        $st->execute();
        $r = $st->get_result()->fetch_assoc();
        $st->close();
        if ($r) {
            $nombreIng = $r['nombre'];
            $noEmp = $r['noEmpleado'];
            // Enlace personalizado (tabla usuarios_enlace_planeacion, ligada por NoEmpleado).
            // Resiliente si la tabla no existe; se prefiere la fila con pageName (link específico).
            if ($noEmp !== null && $noEmp !== '' &&
                ($stE = @$conn->prepare("SELECT Enlace FROM usuarios_enlace_planeacion
                                         WHERE NoEmpleado = ? ORDER BY (Enlace LIKE '%pageName%') DESC LIMIT 1"))) {
                $stE->bind_param('s', $noEmp);
                $stE->execute();
                $rE = $stE->get_result()->fetch_assoc();
                $stE->close();
                if ($rE && !empty($rE['Enlace'])) $enlace = trim($rE['Enlace']);
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Tablero <?php echo htmlspecialchars($nombreIng); ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="css/planeacion.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <style>
        .tablero-wrap { width: 100%; height: 82vh; overflow: hidden; }
        /* Zoom 70% por defecto: tamaño lógico 1/0.7 y scale(0.7) => llena el ancho mostrando ~1.43x más contenido.
           clip-path recorta la barra inferior de PowerBI (quitarbarra). */
        .quitarbarra { width: 142.857%; height: 117.14vh; border: 0; transform: scale(0.7); transform-origin: 0 0;
                       clip-path: polygon(0% 0%, 100% 0%, 100% calc(100% - 37px), 0% calc(100% - 37px)); }
    </style>
</head>
<body id="page-top">
    <div id="wrapper">
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include 'encabezado.php'; ?>

                <div class="container-fluid">
                    <h1 class="h4 mb-3"><i class="fas fa-chart-line"></i> Tablero &mdash; <?php echo htmlspecialchars($nombreIng); ?></h1>
                    <?php if ($enlace !== ''): ?>
                        <div class="tablero-wrap">
                            <iframe class="quitarbarra" title="Tablero PowerBI" src="<?php echo htmlspecialchars($enlace); ?>" allowfullscreen></iframe>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning"><i class="fas fa-info-circle"></i> Este ingeniero no tiene un tablero de PowerBI configurado.</div>
                    <?php endif; ?>
                </div>
            </div>

            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto"><span>Copyright &copy; MESS <?php echo date('Y'); ?></span></div>
                </div>
            </footer>
        </div>
    </div>

    <a class="scroll-to-top rounded" href="#page-top"><i class="fas fa-angle-up"></i></a>

    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/sb-admin-2.min.js"></script>
</body>
</html>
