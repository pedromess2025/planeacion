<?php
// Lógica compartida de importación del reporte de OT internas -> tabla `planeacion_ot_interna`.
// La usan DOS entradas, para que ambas se comporten idéntico:
//   - `cargar_ot_interna.php`  (línea de comandos)
//   - `procesar_ot_interna.php` (subida desde el navegador; la pantalla es `carga_sabana.php`)
// La tabla la lee el grid de Disponibilidad de Ingenieros (bloque 3c de acciones_disponibilidad.php),
// que pinta las celdas moradas "OT interna".

// Normaliza un nombre para compararlo: sin acentos, sin mayúsculas, sin signos ni espacios de más.
// Debe coincidir con la normalización del endpoint, o el diagnóstico mentiría.
function otiNormalizarNombre($s) {
    $s = mb_strtolower(trim((string)$s), 'UTF-8');
    $s = strtr($s, ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ü'=>'u','ñ'=>'n',
                    'à'=>'a','è'=>'e','ì'=>'i','ò'=>'o','ù'=>'u']);
    $s = preg_replace('/[^a-z0-9 ]+/', ' ', $s);
    return trim(preg_replace('/\s+/', ' ', $s));
}

// Importa el CSV. Devuelve un arreglo con el resultado; NUNCA lanza excepción hacia afuera.
//   ok, mensaje, filas, omitidas, rango{min,max}, sin_match{nombre => veces}
function otiImportar(mysqli $conn, $ruta) {
    $r = ['ok' => false, 'mensaje' => '', 'filas' => 0, 'omitidas' => 0, 'sin_fecha' => 0,
          'rango' => ['min' => null, 'max' => null], 'sin_match' => []];

    if (!is_file($ruta) || !is_readable($ruta)) {
        $r['mensaje'] = 'No se pudo leer el archivo.';
        return $r;
    }

    // El reporte puede venir de Excel en Windows-1252; si no es UTF-8 válido se convierte, porque
    // si no los nombres con acento nunca casarían contra `usuarios` y se perderían OT.
    $contenido = file_get_contents($ruta);
    if ($contenido === false) { $r['mensaje'] = 'No se pudo leer el archivo.'; return $r; }
    if (!mb_check_encoding($contenido, 'UTF-8')) {
        $contenido = mb_convert_encoding($contenido, 'UTF-8', 'Windows-1252');
    }
    $fh = fopen('php://temp', 'r+');
    fwrite($fh, $contenido);
    rewind($fh);

    $headers = fgetcsv($fh);
    if (!$headers) { fclose($fh); $r['mensaje'] = 'El archivo está vacío o no se pudo leer.'; return $r; }
    $headers = array_map(function ($h) { return trim($h, " \t\r\n\xEF\xBB\xBF"); }, $headers);
    $idx = array_flip($headers);
    if (!isset($idx['orderCode'])) {
        fclose($fh);
        $r['mensaje'] = 'Este archivo no parece el reporte de OT internas: no trae la columna "orderCode". '
                      . 'Columnas encontradas: ' . implode(', ', array_slice($headers, 0, 8)) . '…';
        return $r;
    }
    $col = function ($row, $name) use ($idx) {
        return isset($idx[$name]) && isset($row[$idx[$name]]) ? trim($row[$idx[$name]]) : null;
    };

    // El reporte usa 'S/R' y "'-" como "sin registro"; se guardan como NULL.
    $esVacio = function ($v) {
        $v = trim((string)$v);
        return $v === '' || strcasecmp($v, 'S/R') === 0 || $v === '-' || $v === "'-";
    };
    // El formato de fecha del export YA CAMBIÓ una vez (era "20/07/2026 08:00" y pasó a
    // "2026-01-02  08:41 AM", con doble espacio y AM/PM). Cuando eso pasó, TODAS las fechas entraron
    // como NULL y el tablero se quedó en blanco sin ningún error. Por eso se prueban varios formatos
    // y se cuenta aparte cuántas OT quedaron sin fecha (ver 'sin_fecha' en el resultado).
    $parseFecha = function ($v) use ($esVacio) {
        if ($esVacio($v)) return null;
        $s = trim(preg_replace('/\s+/', ' ', (string)$v));
        if ($s === '' || strpos($s, '0000-00-00') === 0) return null;
        foreach (['d/m/Y H:i', 'Y-m-d h:i A', 'Y-m-d H:i:s', 'Y-m-d H:i', 'd/m/Y', 'Y-m-d'] as $f) {
            $dt = DateTime::createFromFormat($f, $s);
            $err = DateTime::getLastErrors();   // en PHP 8 devuelve false cuando no hubo problemas
            if ($dt && (!$err || ($err['warning_count'] === 0 && $err['error_count'] === 0))) {
                return $dt->format('Y-m-d H:i:s');
            }
        }
        return null;
    };
    $limpiaNum = function ($v) use ($esVacio) { return $esVacio($v) ? null : trim($v); };

    // Nombres de los ingenieros activos, para avisar cuáles del reporte NO van a casar.
    // El cruce del grid es POR NOMBRE (la columna `Engineers` guarda texto, no ids), así que un
    // typo en el reporte hace que esa OT no se le pinte a nadie y nadie se entera.
    $conocidos = [];
    $q = $conn->query("SELECT nombres, apellidos, nombre FROM usuarios
                       WHERE estatus = 1 AND tipo_usr IN ('ING','JEFE_ENCARGADO','JEFE_LAB')");
    if ($q) {
        while ($u = $q->fetch_assoc()) {
            foreach ([trim($u['nombres'] . ' ' . $u['apellidos']),
                      trim($u['apellidos'] . ' ' . $u['nombres']),
                      $u['nombre']] as $cand) {
                $k = otiNormalizarNombre($cand);
                if ($k !== '') $conocidos[$k] = true;
            }
        }
    }

    $sql = "INSERT INTO planeacion_ot_interna
            (codigo_ot,estatus,areas_calidad,ingenieros,fecha_creacion,fecha_compromiso,fecha_inicio,fecha_fin,fecha_cierre,tiempo_estimado,tiempo_trabajado,tiempo_registrado)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?)
            ON DUPLICATE KEY UPDATE estatus=VALUES(estatus), areas_calidad=VALUES(areas_calidad), ingenieros=VALUES(ingenieros),
                fecha_creacion=VALUES(fecha_creacion), fecha_compromiso=VALUES(fecha_compromiso), fecha_inicio=VALUES(fecha_inicio), fecha_fin=VALUES(fecha_fin),
                fecha_cierre=VALUES(fecha_cierre), tiempo_estimado=VALUES(tiempo_estimado), tiempo_trabajado=VALUES(tiempo_trabajado), tiempo_registrado=VALUES(tiempo_registrado)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        fclose($fh);
        $r['mensaje'] = 'No existe la tabla `planeacion_ot_interna` o no se pudo preparar la consulta: ' . $conn->error;
        return $r;
    }

    while (($row = fgetcsv($fh)) !== false) {
        if (count(array_filter($row, function ($x) { return trim((string)$x) !== ''; })) === 0) continue;
        $orderCode = $col($row, 'orderCode');
        if ($orderCode === null || $orderCode === '') { $r['omitidas']++; continue; }

        // Sin fecha de inicio la OT se guarda pero NO se puede pintar en el tablero (no se sabe qué día).
        $fIni = $parseFecha($col($row, 'startDate'));
        if ($fIni !== null) {
            $d = substr($fIni, 0, 10);
            if ($r['rango']['min'] === null || $d < $r['rango']['min']) $r['rango']['min'] = $d;
            if ($r['rango']['max'] === null || $d > $r['rango']['max']) $r['rango']['max'] = $d;
        } else {
            $r['sin_fecha']++;
        }

        // Diagnóstico: ¿cada nombre del reporte existe en `usuarios`? (mismos separadores que el grid)
        $ingsTxt = (string)$col($row, 'Engineers');
        foreach (preg_split('/[,;\/&]| y /u', $ingsTxt) as $parte) {
            $k = otiNormalizarNombre($parte);
            if ($k === '') continue;
            if (!isset($conocidos[$k])) {
                $limpio = trim($parte);
                $r['sin_match'][$limpio] = ($r['sin_match'][$limpio] ?? 0) + 1;
            }
        }

        $vals = [
            $orderCode,
            $col($row, 'status'),
            $col($row, 'qualityAreas'),
            $ingsTxt,
            $parseFecha($col($row, 'created')),
            $parseFecha($col($row, 'dueDate')),
            $fIni,
            $parseFecha($col($row, 'endDate')),
            $parseFecha($col($row, 'closeDate')),
            $limpiaNum($col($row, 'estimatedTime')),
            $limpiaNum($col($row, 'workTime')),
            $limpiaNum($col($row, 'timedTime')),
        ];
        $stmt->bind_param('ssssssssssss', ...$vals);
        if ($stmt->execute()) { $r['filas']++; } else { $r['omitidas']++; }
    }
    $stmt->close();
    fclose($fh);

    if ($r['filas'] === 0 && $r['omitidas'] === 0) {
        $r['mensaje'] = 'El archivo no traía ningún renglón de datos.';
        return $r;
    }
    // Si NINGUNA fecha se pudo leer, el formato del export cambió: se importó pero el tablero
    // quedaría en blanco. Vale más fallar ruidosamente que dejarlo pasar.
    if ($r['filas'] > 0 && $r['sin_fecha'] === $r['filas']) {
        $r['mensaje'] = 'Se leyeron ' . $r['filas'] . ' OT pero NINGUNA traía una fecha de inicio reconocible: '
                      . 'seguramente cambió el formato de fecha del reporte. Los datos se guardaron, pero '
                      . 'no se pintarán en el tablero hasta corregir la lectura de fechas.';
        return $r;   // ok = false a propósito: es un fallo, aunque haya filas
    }
    arsort($r['sin_match']);
    $r['ok'] = true;
    return $r;
}
