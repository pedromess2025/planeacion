<?php
// Importador del reporte de OT internas -> tabla `planeacion_ot_interna`.
// Uso (CLI):  php cargar_ot_interna.php "ruta/al/reporte.csv"
//             (por defecto lee .claude/SERV INTERNOS.csv)
// Reejecutar = recarga incremental (UPSERT por orderCode; no duplica).
//
// La lógica vive en `lib_ot_interna.php`, COMPARTIDA con la carga desde el navegador
// (tarjeta "Reporte de OT internas" en `carga_sabana.php`), para que las dos vías se comporten igual.
if (php_sapi_name() !== 'cli') { die('Solo CLI'); }
require __DIR__ . '/conn.php';
require __DIR__ . '/lib_ot_interna.php';

$ruta = $argv[1] ?? (__DIR__ . '/.claude/SERV INTERNOS.csv');
if (!is_file($ruta)) { fwrite(STDERR, "No existe el archivo: $ruta\n"); exit(1); }

$r = otiImportar($conn, $ruta);
if (!$r['ok']) { fwrite(STDERR, $r['mensaje'] . "\n"); exit(1); }

echo "Reporte: $ruta\n";
echo "Filas cargadas (insert/update): {$r['filas']}  |  omitidas: {$r['omitidas']}\n";
if ($r['rango']['min']) { echo "Rango de fechas de inicio: {$r['rango']['min']} a {$r['rango']['max']}\n"; }
if (!empty($r['sin_match'])) {
    echo "\n⚠ Nombres del reporte que NO existen en `usuarios` (esas OT no se le pintan a nadie):\n";
    foreach ($r['sin_match'] as $nom => $veces) { echo "   - $nom  ($veces OT)\n"; }
}
