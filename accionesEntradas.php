<?php
include_once 'conn.php';
header('Content-Type: application/json');
    
    $accion = isset($_POST['accion']) ? $_POST['accion'] : '';
    $usuario = isset($_COOKIE['id_usuarioL']) ? $_COOKIE['id_usuarioL'] : ''; // Usuario que registra la entrada
    
    //REGISTRO DE NUEVA ENTRADA
    $cliente = isset($_POST['cliente']) ? $_POST['cliente'] : '';
    $area  = isset($_POST['area']) ? $_POST['area'] : '';   
    $marca = isset($_POST['marca']) ? $_POST['marca'] : '';
    $modelo = isset($_POST['modelo']) ? $_POST['modelo'] : '';
    $no_serie = isset($_POST['no_serie']) ? $_POST['no_serie'] : '';
    $diagnostico_inicial  = isset($_POST['diagnostico_inicial']) ? $_POST['diagnostico_inicial'] : '';
    $fecha_estimada  = isset($_POST['fecha_estimada']) ? $_POST['fecha_estimada'] : '';
    $observaciones = isset($_POST['observaciones']) ? $_POST['observaciones'] : '';
    $demo = isset($_POST['demo']) ? 1 : 0; // Demo: 1 si está marcado, 0 si no
    $contacto_nombre = isset($_POST['nombre_cliente']) ? $_POST['nombre_cliente'] : '';
    $telefono = isset($_POST['contacto']) ? $_POST['contacto'] : '';
    $correo_cliente = isset($_POST['correo_cliente']) ? $_POST['correo_cliente'] : '';
    $contacto = $telefono . ($correo_cliente ? ' / ' . $correo_cliente : '');
    $id_ing_trae = isset($_POST['slcIngTrae']) ? intval($_POST['slcIngTrae']) : 0;
    $fecha_real_entrada = isset($_POST['fecha_real_entrada']) ? $_POST['fecha_real_entrada'] : '';
    $ov_ot = isset($_POST['ov_ot']) ? $_POST['ov_ot'] : '';

    $noEmpleado = isset($_COOKIE['noEmpleado']) ? intval($_COOKIE['noEmpleado']) : 0;
    
    // CAPTURAR IDs DE INGENIEROS
    $slcRespoonsable = (isset($_POST['slcRespoonsable']) && $_POST['slcRespoonsable'] != '0') ? intval($_POST['slcRespoonsable']) : '';
    $slcRespoonsable2 = (isset($_POST['slcRespoonsable2']) && $_POST['slcRespoonsable2'] != '0') ? intval($_POST['slcRespoonsable2']) : '';
    $slcRespoonsable3 = (isset($_POST['slcRespoonsable3']) && $_POST['slcRespoonsable3'] != '0') ? intval($_POST['slcRespoonsable3']) : '';
    
    // Concatenar IDs de ingenieros (opcional, solo si existen)
    $ingenieros = array_filter([$slcRespoonsable, $slcRespoonsable2, $slcRespoonsable3]);

    //INGENIERO ASIGNADO
    $id_registro = isset($_POST['id_registro']) ? intval($_POST['id_registro']) : 0;
    $nota = isset($_POST['notas_ingeniero']) ? trim($_POST['notas_ingeniero']) : '';
    $estatus = isset($_POST['estatus']) ? $_POST['estatus'] : '';
    $fecha_seguimiento = !empty($_POST['fecha_seguimiento']) ? $_POST['fecha_seguimiento'] : null;
    
    //ACTUALIZACION DE EQUIPO
    $equipo_id = isset($_POST['equipo_id']) ? intval($_POST['equipo_id']) : 0;
    $ingeniero_id = isset($_POST['ingeniero_id']) ? intval($_POST['ingeniero_id']) : 0;
    $nuevo_estatus = isset($_POST['nuevo_estatus']) ? $_POST['nuevo_estatus'] : '';
    $fecha_termino = isset($_POST['fecha_termino']) ? $_POST['fecha_termino'] : null;
    
    // REPROGRAMAR FECHA COMPROMISO
    $nueva_fecha = isset($_POST['nueva_fecha']) ? trim($_POST['nueva_fecha']) : '';

    // OBTENER INGENIEROS ASIGNADOS (solo id y nombre)
    $id_registro_param = isset($_POST['id_registro']) ? intval($_POST['id_registro']) : 0;

    // REGISTRO EQUIPOS
    if ($accion == 'nuevaEntrada') {
        $sqlInsert = "INSERT INTO entrada_registros (cliente, area, marca, modelo, no_serie, notas_recepcion, fecha_promesa_entrega, estatus, fecha_registro, fechaTermino, demo, contacto_nombre, contacto, id_ing_trae, fecha_real_entrada, ov_ot)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NULL, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sqlInsert);
        
        // "s" = string, "i" = integer
        // Orden: cliente(s), area(s), marca(s), modelo(s), no_serie(s), diagnostico_inicial(s), fecha_estimada(s), estatus(s), demo(i), contacto_nombre(s), contacto(s), id_ing_trae(i), fecha_real_entrada(s), ov_ot(s)
        $stmt->bind_param("ssssssssississ", 
            $cliente, $area, $marca, $modelo, $no_serie, $diagnostico_inicial, $fecha_estimada, $estatus, $demo, $contacto_nombre, $contacto, $id_ing_trae, $fecha_real_entrada, $ov_ot
        );
        
        if ($stmt->execute()) {
            $ultimoId = $conn->insert_id; // Obtenemos ID para las fotos
            $errorFotos = false;
            $errorIngenieros = false;

            // 1. REGISTRAR INGENIEROS EN TABLA entrada_log_ingenieros
            if (!empty($ingenieros)) {
                $sqlIngeniero = "INSERT INTO entrada_log_ingenieros (id_registro, id_ing, fecha, estatus, id_asigno) VALUES (?, ?, NOW(), 'ASIGNADO', ?)";
                $stmtIng = $conn->prepare($sqlIngeniero);
                
                foreach ($ingenieros as $id_ing) {
                    $stmtIng->bind_param("iii", $ultimoId, $id_ing, $usuario);
                    if (!$stmtIng->execute()) {
                        $errorIngenieros = true;
                    }
                }
                $stmtIng->close();
                
                // Actualizar fecha_asignacion si hay ingenieros asignados
                $stmtFechaAsig = $conn->prepare("UPDATE entrada_registros SET fecha_asignacion = NOW() WHERE id_registro = ?");
                $stmtFechaAsig->bind_param("i", $ultimoId);
                $stmtFechaAsig->execute();
                $stmtFechaAsig->close();
            }

            // 2. MANEJO DE FOTOS
            if (isset($_FILES['fotos'])) {
                $fotos = $_FILES['fotos'];
                // Contamos cuántos archivos vienen
                $totalArchivos = count($fotos['name']);

                // Creamos carpeta si no existe
                $directorio = 'imgEntradas/';
                if (!file_exists($directorio)) {
                    mkdir($directorio, 0777, true);
                }

                for ($i = 0; $i < $totalArchivos; $i++) {
                    // Verificar que no hubo error en la subida y que tiene nombre
                    if ($fotos['error'][$i] === UPLOAD_ERR_OK && !empty($fotos['name'][$i])) {
                        
                        $nombreOriginal = $fotos['name'][$i];
                        $tmpName        = $fotos['tmp_name'][$i];
                        
                        // Generar nombre único: entrada_15_TIMESTAMP_nombre.jpg
                        $nuevoNombre = 'entrada_' . $ultimoId . '_' . time() . '_' . $i;
                        $rutaDestino = $directorio . $nuevoNombre;

                        if (move_uploaded_file($tmpName, $rutaDestino)) {
                            // Insertar ruta en BD
                            // Obtener fecha actual
                            $fechaActual = date('Y-m-d H:i:s');
                            $sqlFoto = "INSERT INTO entrada_fotos (id_regEntrada, ruta, fecha) VALUES (?, ?, ?)";
                            $stmtFoto = $conn->prepare($sqlFoto);  
                            $stmtFoto->bind_param("iss", $ultimoId, $rutaDestino, $fechaActual);
                            $stmtFoto->execute();
                            $stmtFoto->close();
                        } else {
                            $errorFotos = true;
                        }
                    }
                }
            }
            $response = array(
                'status' => 'success', 
                'id_entrada' => $ultimoId,
                'message' => 'Entrada registrada con éxito.' . ($errorFotos ? ' (Hubo error al subir algunas fotos)' : '') . ($errorIngenieros ? ' (Hubo error al registrar algunos ingenieros)' : '')
            );
        } else {
            $response = array(
                'status' => 'error', 
                'message' => 'Error al guardar en BD: ' . $stmt->error
            );
        }
        $stmt->close();
        // Devolver JSON
        header('Content-Type: application/json');
        echo json_encode($response);
        exit; // Terminar script
    }

    // CARGAS DE REGISTROS DE ENTRADAS
    if ($accion == 'obtenerEquipos') {
        $esEncargado = isset($_POST['esEncargado']) ? $_POST['esEncargado'] : '';
        
        if ($esEncargado === 'Esencargado') {
            // Mostrar TODOS los registros
            $sql = "SELECT ent.id_registro, ent.cliente, ent.area, ent.marca, ent.modelo, ent.no_serie, 
                        ent.fecha_promesa_entrega AS fecha_compromiso, 
                        ent.fecha_real_entrada,
                        ent.notas_recepcion AS diagnostico_inicial, 
                        ent.estatus,
                        ent.num_reprogramaciones,
                        ent.fecha_reprogramacion,
                        (
                            SELECT GROUP_CONCAT(DISTINCT eli.id_ing SEPARATOR ',')
                            FROM entrada_log_ingenieros eli
                            WHERE eli.id_registro = ent.id_registro
                            AND eli.estatus = 'ASIGNADO'
                        ) AS ids_ingenieros,
                        (
                            SELECT GROUP_CONCAT(DISTINCT us.nombre SEPARATOR ', ')
                            FROM entrada_log_ingenieros eli
                            INNER JOIN usuarios us ON (us.id_usuario = eli.id_ing)
                            WHERE eli.id_registro = ent.id_registro
                            AND eli.estatus = 'ASIGNADO'
                        ) AS nombres_ingenieros,
                        CONCAT('#ENT-', ent.area, '-', YEAR(ent.fecha_registro), '-', LPAD(ent.id_registro, 2, '0')) AS folio,
                        CASE WHEN ent.fecha_real_entrada IS NULL THEN NULL
                            ELSE 
                            IF (ent.estatus = 'TERMINADO', DATEDIFF(ent.fechaTermino, ent.fecha_real_entrada), 
                                DATEDIFF(CURDATE(), ent.fecha_real_entrada))
                        END AS dias_transcurridos
                    FROM entrada_registros ent                    
                    ORDER BY ent.fecha_registro DESC";
            
            $result = $conn->query($sql);
        } else {
            // Mostrar solo registros donde el ingeniero está asignado
            $sql = "SELECT ent.id_registro, ent.cliente, ent.area, ent.marca, ent.modelo, ent.no_serie, 
                        ent.fecha_promesa_entrega AS fecha_compromiso, 
                        ent.fecha_real_entrada,
                        ent.notas_recepcion AS diagnostico_inicial, 
                        ent.estatus,
                        ent.num_reprogramaciones,
                        ent.fecha_reprogramacion,
                        (
                            SELECT GROUP_CONCAT(DISTINCT eli.id_ing SEPARATOR ',')
                            FROM entrada_log_ingenieros eli
                            WHERE eli.id_registro = ent.id_registro
                            AND eli.estatus = 'ASIGNADO'
                        ) AS ids_ingenieros,
                        (
                            SELECT GROUP_CONCAT(DISTINCT us.nombre SEPARATOR ', ')
                            FROM entrada_log_ingenieros eli
                            INNER JOIN usuarios us ON (us.id = eli.id_ing OR us.id_usuario = eli.id_ing)
                            WHERE eli.id_registro = ent.id_registro
                            AND eli.estatus = 'ASIGNADO'
                        ) AS nombres_ingenieros,
                        CONCAT('#ENT-', ent.area, '-', YEAR(ent.fecha_registro), '-', LPAD(ent.id_registro, 2, '0')) AS folio
                    FROM entrada_registros ent
                    INNER JOIN entrada_log_ingenieros eli_filtro ON ent.id_registro = eli_filtro.id_registro
                    WHERE eli_filtro.id_ing = ?
                    AND eli_filtro.estatus = 'ASIGNADO'
                    ORDER BY ent.fecha_promesa_entrega DESC";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $usuario);
            $stmt->execute();
            $result = $stmt->get_result();
        }
        
        $equipos = [];
        
        while ($row = $result->fetch_assoc()) {
            $row['id'] = $row['id_registro']; // Compatibilidad con frontend
            $row['puede_asignar'] = $esEncargado; // Flag: solo encargados pueden asignar
            $equipos[] = $row;
        }
        
        if (!$esEncargado) {
            $stmt->close();
        }
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $equipos]);
        exit;
    }

    // CARGAR INGENIEROS
    if ($accion == 'obtenerIngenieros') {
        $sql = "SELECT nombre, id_usuario FROM `usuarios` WHERE estatus = 1 ORDER BY nombre ASC";
        $result = $conn->query($sql);
        $ingenieros = [];
        
        while ($row = $result->fetch_assoc()) {
            $ingenieros[] = $row;
        }
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $ingenieros]);
        exit;
    }

    // OBTENER INGENIEROS ASIGNADOS A UNA ENTRADA (solo id y nombre)
    if ($accion == 'obtenerIngenierosAsignados') {
        $ingenierosAsignados = [];
        if ($id_registro_param > 0) {
            $sql = "SELECT DISTINCT eli.id_ing AS id_ing,
                        (SELECT COALESCE(u.nombre, '') FROM usuarios u WHERE u.id = eli.id_ing OR u.id_usuario = eli.id_ing LIMIT 1) AS nombre
                    FROM entrada_log_ingenieros eli
                    WHERE eli.id_registro = ? AND eli.estatus = 'ASIGNADO'";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $id_registro_param);
            $stmt->execute();
            $res = $stmt->get_result();
            while ($row = $res->fetch_assoc()) {
                $ingenierosAsignados[] = ['id' => $row['id_ing'], 'nombre' => $row['nombre']];
            }
            $stmt->close();
        }
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $ingenierosAsignados]);
        exit;
    }

    // ASIGNAR EQUIPO A INGENIERO
    if ($accion == 'asignarIngeniero') {
        // 1. Insertar en entrada_log_ingenieros
        $sqlInsert = "INSERT INTO entrada_log_ingenieros (id_registro, id_ing, fecha, estatus, id_asigno) VALUES (?, ?, NOW(), 'ASIGNADO', ?)";
        $stmtInsert = $conn->prepare($sqlInsert);
        $stmtInsert->bind_param("iii", $equipo_id, $ingeniero_id, $usuario);
        
        if ($stmtInsert->execute()) {
            // 2. Actualizar estatus del equipo si es necesario
            // Solo actualizar fecha_asignacion si aún no existe (es NULL)
            $stmtUpd = $conn->prepare("UPDATE entrada_registros SET fecha_asignacion = IF(fecha_asignacion IS NULL, NOW(), fecha_asignacion) WHERE id_registro = ?");
            $stmtUpd->bind_param("i", $equipo_id);
            $stmtUpd->execute();
            $stmtUpd->close();

            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => $stmtInsert->error]);
        }
        $stmtInsert->close();
        exit;
    }

    // RETIRAR (DESASIGNAR) INGENIERO: Cambia estatus en entrada_log_ingenieros a 'SIN ASIGNAR'
    if ($accion == 'retirarIngeniero') {
        header('Content-Type: application/json');
        if ($equipo_id > 0 && $ingeniero_id > 0) {
            $sqlUpd = "UPDATE entrada_log_ingenieros SET estatus = 'SIN ASIGNAR', fecha_actualizacion = NOW() WHERE id_registro = ? AND id_ing = ? AND estatus = 'ASIGNADO' LIMIT 1";
            $stmtUpd = $conn->prepare($sqlUpd);
            $stmtUpd->bind_param('ii', $equipo_id, $ingeniero_id);
            if ($stmtUpd->execute()) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => $stmtUpd->error]);
            }
            $stmtUpd->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Parámetros inválidos']);
        }
        exit;
    }

    // REPROGRAMAR FECHA COMPROMISO
    if ($accion == 'reprogramarFecha') {
        if ($id_registro <= 0 || $nueva_fecha === '') {
            echo json_encode(['success' => false, 'message' => 'Parametros invalidos']);
            exit;
        }

        $stmtRepro = $conn->prepare("UPDATE entrada_registros 
            SET fecha_reprogramacion = ?, 
                num_reprogramaciones = IFNULL(num_reprogramaciones, 0) + 1
            WHERE id_registro = ?");
        $stmtRepro->bind_param('si', $nueva_fecha, $id_registro);
        $ok = $stmtRepro->execute();
        $stmtRepro->close();

        if ($ok) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se pudo reprogramar la fecha']);
        }
        exit;
    }

    // CARGAR AREAS
    if ($accion == 'obtenerAreas') {
        $sql = "SELECT id, AREA, CDAREA FROM areas ORDER BY AREA ASC";
        $result = $conn->query($sql);
        $areas = [];
        
        while ($row = $result->fetch_assoc()) {
            $areas[] = $row;
        }
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $areas]);
        exit;
    }

    // GUARDAR SEGUIMIENTO / ACTUALIZACIÓN DE TRABAJO
    if ($accion == 'guardarSeguimiento') {
        // Insertar nota si viene
        if ($nota !== '') {
            $stmtSeg = $conn->prepare("INSERT INTO entrada_seguimiento (id_registro, id_usuario_nota, nota, fecha_seguimiento, fecha_actualizacion, estatus) 
            VALUES (?, ?, ?, ?, NOW(), ?)");
            $stmtSeg->bind_param('issss', $id_registro, $usuario, $nota, $fecha_termino, $nuevo_estatus);
            $stmtSeg->execute();
            $id_seguimiento = $conn->insert_id; // Obtener ID del seguimiento insertado
            $stmtSeg->close();
        }
        
        // MANEJO DE FOTOS DE SALIDA (SEGUIMIENTO)
        if (isset($_FILES['fotos_salida']) && isset($id_seguimiento)) {
            $fotos = $_FILES['fotos_salida'];
            $totalArchivos = count($fotos['name']);
            
            // Crear carpeta si no existe
            $directorio = 'imgEntradas/';
            if (!file_exists($directorio)) {
                mkdir($directorio, 0777, true);
            }
            
            for ($i = 0; $i < $totalArchivos; $i++) {
                // Verificar que no hubo error en la subida y que tiene nombre
                if ($fotos['error'][$i] === UPLOAD_ERR_OK && !empty($fotos['name'][$i])) {
                    
                    $tmpName = $fotos['tmp_name'][$i];
                    
                    // Generar nombre único: seguimiento_ID_TIMESTAMP_index
                    $nuevoNombre = 'seguimiento_' . $id_registro . '_' . time() . '_' . $i . '.' . pathinfo($fotos['name'][$i], PATHINFO_EXTENSION);
                    $rutaDestino = $directorio . $nuevoNombre;
                    
                    if (move_uploaded_file($tmpName, $rutaDestino)) {
                        // Insertar ruta en BD
                        $sqlFoto = "UPDATE entrada_seguimiento SET ruta_foto = ? WHERE id_seguimiento = ?";
                        $stmtFoto = $conn->prepare($sqlFoto);
                        $stmtFoto->bind_param("si", $rutaDestino, $id_seguimiento);
                        $stmtFoto->execute();
                        $stmtFoto->close();
                    }
                }
            }
        }

        // Actualizar estatus y fecha de término        
        if ($nuevo_estatus == "TERMINADO" || $nuevo_estatus === "SINENVIAR") {            
            $sqlEstatus = "UPDATE entrada_registros SET estatus = ?, fechaTermino = ? WHERE id_registro = ?";
            $stmtUpd = $conn->prepare($sqlEstatus);
            $stmtUpd->bind_param('ssi', $nuevo_estatus, $fecha_termino, $id_registro);
        } else {
            $sqlEstatus = "UPDATE entrada_registros SET estatus = ? WHERE id_registro = ?";
            $stmtUpd = $conn->prepare($sqlEstatus);
            $stmtUpd->bind_param('si', $nuevo_estatus, $id_registro);
        }

        // Ejecución única para evitar repetir código
        if ($stmtUpd->execute()) {      

        }
        $stmtUpd->close();
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }

    // OBTENER DETALLES DE EQUIPO
    if ($accion == 'obtenerDetalleEquipo') {
        
        $sql = "SELECT ent.*,
                (
                    SELECT GROUP_CONCAT(DISTINCT eli.id_ing SEPARATOR ',')
                    FROM entrada_log_ingenieros eli
                    WHERE eli.id_registro = ent.id_registro
                    AND eli.estatus = 'ASIGNADO'
                ) AS ids_ingenieros,
                (
                    SELECT GROUP_CONCAT(DISTINCT us.nombre SEPARATOR ', ')
                    FROM entrada_log_ingenieros eli
                    INNER JOIN usuarios us ON (us.id_usuario = eli.id_ing)
                    WHERE eli.id_registro = ent.id_registro
                    AND eli.estatus = 'ASIGNADO'
                ) AS ingeniero_nombre
                FROM entrada_registros ent
                WHERE ent.id_registro = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id_registro);
        $stmt->execute();
        $result = $stmt->get_result();
        $equipo = $result->fetch_assoc();
        $stmt->close();
        
        if ($equipo) {
            // Generar folio
            $folio = '#ENT-' . $equipo['area'] . '-' . date('Y', strtotime($equipo['fecha_registro'])) . '-' . str_pad($equipo['id_registro'], 2, '0', STR_PAD_LEFT);
            $equipo['folio'] = $folio;
            
            // Buscar fotos desde BD (entrada_fotos) - solo fotos de entrada (patrón: entrada_x_xxx_x)
            $sqlFotos = "SELECT ruta FROM entrada_fotos WHERE id_regEntrada = ? AND ruta LIKE 'imgEntradas/entrada_%' ORDER BY id ASC";
            $stmtFotos = $conn->prepare($sqlFotos);
            $stmtFotos->bind_param('i', $equipo['id_registro']);
            $stmtFotos->execute();
            $resultFotos = $stmtFotos->get_result();
            $fotos = [];
            while ($fotoRow = $resultFotos->fetch_assoc()) {
                if (!empty($fotoRow['ruta'])) {
                    $fotos[] = $fotoRow['ruta'];
                }
            }
            $stmtFotos->close();
            $equipo['fotos'] = $fotos;
            
            // Buscar seguimientos/comentarios
            $sqlSeguimientos = "SELECT es.id_seguimiento, es.id_registro, es.id_usuario_nota, es.nota, 
                                        es.fecha_seguimiento, es.fecha_actualizacion, es.ruta_foto, es.estatus,
                                        us.nombre AS nombre_usuario
                                FROM entrada_seguimiento es
                                INNER JOIN usuarios us ON us.id_usuario = es.id_usuario_nota
                                WHERE es.id_registro = ?
                                ORDER BY es.fecha_seguimiento DESC";
            $stmtSeg = $conn->prepare($sqlSeguimientos);
            $stmtSeg->bind_param('i', $id_registro);
            $stmtSeg->execute();
            $resultSeg = $stmtSeg->get_result();
            
            $seguimientos = [];
            while ($row = $resultSeg->fetch_assoc()) {
                // Formatear fecha
                $fecha = date('d/m/Y H:i', strtotime($row['fecha_actualizacion']));
                
                // Obtener iniciales del nombre
                $palabras = explode(' ', $row['nombre_usuario']);
                $iniciales = '';
                foreach ($palabras as $palabra) {
                    if (!empty($palabra)) {
                        $iniciales .= strtoupper(substr($palabra, 0, 1));
                    }
                }

                // Construir la estructura: "fecha-iniciales-estatus-nota" (fecha e iniciales en negrita, azul si tiene fotos)
                $tieneImagenes = !empty($row['ruta_foto']);
                $estilo = $tieneImagenes ? 'style="color: #0d6efd; cursor: pointer;"' : '';
                $onclick = $tieneImagenes ? 'onclick="mostrarFotosSeguimiento(INDEX_PLACEHOLDER)"' : '';
                $icono_img = $tieneImagenes ? ' <i class="fas fa-image"></i>' : '';
                $estatus_label = !empty($row['estatus']) ? ' - ' . $row['estatus'] : '';
                
                $seguimientos[] = [
                    'html' => $icono_img . '<strong ' . $estilo . ' ' . $onclick . '>' . $fecha . ' - ' . $iniciales . $estatus_label . '</strong> - ' . $row['nota'],
                    'tieneImagenes' => $tieneImagenes,
                    'fotos' => $tieneImagenes ? [$row['ruta_foto']] : [] // Convertir a array
                ];
            }
            $stmtSeg->close();
            
            // Unir todas las notas con saltos de línea dobles, o mensaje por defecto si no hay
            $equipo['notas_seguimiento'] = !empty($seguimientos) ? $seguimientos : null;
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'data' => $equipo]);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Equipo no encontrado']);
        }
        exit;
    }

    // MODIFICAR CAMPOS DE ENTRADA (solo encargados)
    if ($accion == 'modificarEntrada') {
        header('Content-Type: application/json');
        if (!$esEncargado) {
            echo json_encode(['success' => false, 'message' => 'Sin permisos']);
            exit;
        }

        if ($id_registro <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID invalido']);
            exit;
        }

        $cliente_mod = isset($_POST['cliente']) ? trim($_POST['cliente']) : '';
        $contacto_mod = isset($_POST['contacto']) ? trim($_POST['contacto']) : '';
        $telefono_mod = isset($_POST['telefono']) ? trim($_POST['telefono']) :'';

        $area_mod = $area_mod = isset($_POST['areaedit']) ? trim($_POST['areaedit']) : '';
        $quienEnvia_mod = isset($_POST['quienEnvia']) ? trim($_POST['quienEnvia']) : '';
        
        $marca_mod = isset($_POST['marca']) ? trim($_POST['marca']) : '';
        $modelo_mod = isset($_POST['modelo']) ? trim($_POST['modelo']) : '';
        $serie_mod = isset($_POST['no_serie']) ? trim($_POST['no_serie']) : '';

        $notas_mod = isset($_POST['notas']) ? trim($_POST['notas']) : '';
        $fechaReal_mod = isset($_POST['fechaReal']) ? trim($_POST['fechaReal']) : '';
        $fechaCompromiso_mod = isset($_POST['fechaCompromiso']) ? trim($_POST['fechaCompromiso']) : '';
        $ov_ot_mod = isset($_POST['ov_ot']) ? trim($_POST['ov_ot']) : '';

        $stmt = $conn->prepare("UPDATE entrada_registros 
                                SET cliente = ?, contacto_nombre = ?, contacto = ?, area = ?, id_ing_trae = ?, marca = ?, modelo = ?, no_serie = ?, notas_recepcion = ?, fecha_real_entrada = ?, fecha_promesa_entrega = ?, ov_ot = ?
                                WHERE id_registro = ?");
        $stmt->bind_param('ssssssssssssi', $cliente_mod, $contacto_mod, $telefono_mod, $area_mod, $quienEnvia_mod, $marca_mod, $modelo_mod, $serie_mod, $notas_mod, $fechaReal_mod, $fechaCompromiso_mod, $ov_ot_mod, $id_registro);
        $ok = $stmt->execute();
        $stmt->close();

        if ($ok) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se pudo actualizar']);
        }
        exit;
    }
?>