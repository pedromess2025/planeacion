<?php
// Importador del reporte de OT internas -> tabla `planeacion_ot_interna`.
// Uso (CLI):  php cargar_ot_interna.php "ruta/al/reporte.csv"
//             (por defecto lee .claude/SERV INTERNOS.csv)
// Reejecutar = recarga incremental (UPSERT por orderCode; no duplica).
// El reporte trae fechas en formato DD/MM/YYYY HH:MM y literales 'S/R' / "'-" que se guardan como NULL.
if (php_sapi_name() !== 'cli') { die('Solo CLI'); }
require __DIR__ . '/conn.php';

$ruta = $argv[1] ?? (__DIR__ . '/.claude/SERV INTERNOS.csv');
if (!is_file($ruta)) { fwrite(STDERR, "No existe el archivo: $ruta\n"); exit(1); }

$esVacio = function ($v) {
    $v = trim((string)$v);
    return $v === '' || strcasecmp($v, 'S/R') === 0 || $v === '-' || $v === "'-";
};
$parseFecha = function ($v) use ($esVacio) {
    if ($esVacio($v)) return null;
    $v = trim($v);
    $dt = DateTime::createFromFormat('d/m/Y H:i', $v) ?: DateTime::createFromFormat('d/m/Y', $v);
    return $dt ? $dt->format('Y-m-d H:i:s') : null;
};
$limpiaNum = function ($v) use ($esVacio) { return $esVacio($v) ? null : trim($v); };

$fh = fopen($ruta, 'r');
$headers = fgetcsv($fh);
if (!$headers) { fwrite(STDERR, "Reporte vacío o ilegible.\n"); exit(1); }
$headers = array_map(function ($h) { return trim($h, " \t\r\n\xEF\xBB\xBF"); }, $headers);
$idx = array_flip($headers);
$col = function ($row, $name) use ($idx) { return isset($idx[$name]) && isset($row[$idx[$name]]) ? trim($row[$idx[$name]]) : null; };

// El CSV trae encabezados en inglés; la tabla usa columnas en español (mapeo en $col(...) más abajo).
$sql = "INSERT INTO planeacion_ot_interna
        (codigo_ot,estatus,areas_calidad,ingenieros,fecha_creacion,fecha_compromiso,fecha_inicio,fecha_fin,fecha_cierre,tiempo_estimado,tiempo_trabajado,tiempo_registrado)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?)
        ON DUPLICATE KEY UPDATE estatus=VALUES(estatus), areas_calidad=VALUES(areas_calidad), ingenieros=VALUES(ingenieros),
            fecha_creacion=VALUES(fecha_creacion), fecha_compromiso=VALUES(fecha_compromiso), fecha_inicio=VALUES(fecha_inicio), fecha_fin=VALUES(fecha_fin),
            fecha_cierre=VALUES(fecha_cierre), tiempo_estimado=VALUES(tiempo_estimado), tiempo_trabajado=VALUES(tiempo_trabajado), tiempo_registrado=VALUES(tiempo_registrado)";
$stmt = $conn->prepare($sql);

$n = 0; $skip = 0;
while (($row = fgetcsv($fh)) !== false) {
    if (count(array_filter($row, function ($x) { return trim((string)$x) !== ''; })) === 0) continue; // línea vacía
    $orderCode = $col($row, 'orderCode');
    if ($orderCode === null || $orderCode === '') { $skip++; continue; }
    $vals = [
        $orderCode,
        $col($row, 'status'),
        $col($row, 'qualityAreas'),
        $col($row, 'Engineers'),
        $parseFecha($col($row, 'created')),
        $parseFecha($col($row, 'dueDate')),
        $parseFecha($col($row, 'startDate')),
        $parseFecha($col($row, 'endDate')),
        $parseFecha($col($row, 'closeDate')),
        $limpiaNum($col($row, 'estimatedTime')),
        $limpiaNum($col($row, 'workTime')),
        $limpiaNum($col($row, 'timedTime')),
    ];
    $stmt->bind_param('ssssssssssss', ...$vals);
    $stmt->execute();
    $n++;
}
fclose($fh);
echo "Reporte: $ruta\n";
echo "Filas cargadas (insert/update): $n  |  omitidas: $skip\n";
