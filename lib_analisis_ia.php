<?php
// Funciones puras para la subida de "Análisis de servicios IA".
// Sin BD, sin sesión, sin salida: testeables por CLI con php -l / php.
// Modo RECHAZAR lo peligroso, scripts internos permitidos: un HTML con
// código peligroso no se guarda nada; validarArchivoAnalisisIA()
// devuelve ok=false con el motivo, sin el contenido. Los reportes de BI
// (p. ej. tablero-de-capacidad-de-laboratorios.html) necesitan <script>
// para sus filtros y gráficas, así que <script> inline o con src relativo /
// messbook.com.mx está permitido; lo que se rechaza es el script que roba
// sesión, sale del frame o carga código de un dominio externo.
// <meta> y <link> también se permiten con condición: <meta> solo charset,
// viewport o color-scheme; <link> solo rel stylesheet/icon/preconnect y
// href relativo, data: (icono) o de fonts.googleapis.com, fonts.gstatic.com,
// messbook.com.mx, cdn.jsdelivr.net o cdnjs.cloudflare.com. Un <meta>
// http-equiv (p. ej. refresh) o un <link> a otro dominio se rechazan.
//
// Extensiones permitidas exactas: xls, csv, html (minúsculas). NO xlsx.
// Tamaño máximo: 50 MB (lo verifica el endpoint y también esta lib).
// No hay historial ni sufijos: si el nombre sanitizado ya existe en el
// destino, el endpoint lo sobrescribe (el historial vive en otro repo).

const ANALISIS_IA_MAX_BYTES = 50 * 1024 * 1024; // 50 MB
const ANALISIS_IA_EXT_PERMITIDAS = ['xls', 'csv', 'html'];
// Extensiones que nunca se permiten, en ninguna parte del nombre.
const ANALISIS_IA_EXT_PELIGROSAS = [
    'php', 'php3', 'php4', 'php5', 'php7', 'phps', 'phtml', 'phar',
    'cgi', 'pl', 'py', 'js', 'jsp', 'asp', 'aspx', 'sh', 'exe', 'com', 'bat',
];

// Limpia un nombre de archivo subido. Devuelve '' si no queda nada válido.
// - basename (quita directorios y ../), quita bytes nulos.
// - Espacios a _, solo [A-Za-z0-9._-], colapsa puntos seguidos.
// - No permite nombre vacío ni que empiece por punto.
function sanitizarNombreArchivo($nombre) {
    $nombre = (string)$nombre;
    $nombre = str_replace("\0", '', $nombre);
    $nombre = str_replace('\\', '/', $nombre);
    $nombre = basename($nombre);
    $nombre = trim($nombre);
    if ($nombre === '' || $nombre[0] === '.') {
        return '';
    }
    $nombre = str_replace(' ', '_', $nombre);
    $nombre = preg_replace('/[^A-Za-z0-9._-]/u', '_', $nombre);
    $nombre = preg_replace('/\.{2,}/', '.', $nombre);
    $nombre = trim($nombre, '.');
    if ($nombre === '' || $nombre[0] === '.') {
        return '';
    }
    return $nombre;
}


// ¿Es una URL externa a messbook.com.mx? Relativa = interna.
// "//evil.example/x" y "http(s)://otro-dominio/..." = externa.
// "https://messbook.com.mx/..." y subdominios = interna.
function analisis_ia_esUrlExterna($url) {
    $url = trim((string)$url);
    if ($url === '') return false;
    $u = strtolower($url);
    if (strpos($u, 'javascript:') === 0 || strpos($u, 'data:') === 0) return true;
    if (strpos($u, '//') === 0) return true;
    if (preg_match('~^https?://([^/?#]+)~', $u, $m)) {
        $host = strtolower($m[1]);
        $host = explode(':', $host)[0];
        if ($host === 'messbook.com.mx') return false;
        if (substr($host, -16) === '.messbook.com.mx') return false;
        return true;
    }
    return false;
}

// Revisa el contenido de un <script>. Devuelve el motivo o ''.
// Patrones peligrosos (insensible a mayúsculas, con espacios):
// robo de sesión, salida del frame, eval/Function, popups, y
// llamadas de red a URLs externas (fetch / XHR / import / location).
function analisis_ia_scriptPeligroso($js) {
    $js = (string)$js;
    $reglas = [
        ['/document\.cookie/i', 'document.cookie'],
        ['/\b(localStorage|sessionStorage)\b/i', 'acceso a almacenamiento del navegador (localStorage / sessionStorage)'],
        ['/\b(window\.top|top\.location|parent\.location|parent\.document|window\.parent)\b/i', 'acceso al frame padre (window.top / parent)'],
        ['/\beval\s*\(/i', 'eval()'],
        ['/\bnew\s+Function\s*\(/i', 'new Function()'],
        ['/\bwindow\.open\s*\(/i', 'window.open()'],
        ['/\bfetch\s*\(\s*[\'"]\s*(https?:)?\/\//i', 'fetch() a una URL externa'],
        ['/\.open\s*\(\s*[\'"][A-Z]+[\'"]\s*,\s*[\'"]\s*(https?:)?\/\//i', 'petición (XMLHttpRequest) a una URL externa'],
        ['/\bimport\s*\(\s*[\'"]\s*(https?:)?\/\//i', 'import() de una URL externa'],
        ['/(?:location|window\.location|top\.location)\s*\.\s*href\s*=\s*[\'"]\s*(https?:)?\/\//i', 'redirección (location.href) a una URL externa'],
        ['/location\s*\.\s*(?:assign|replace)\s*\(\s*[\'"]\s*(https?:)?\/\//i', 'redirección (location.assign/replace) a una URL externa'],
        ['/document\.write\s*\([^)]*(https?:)?\/\//i', 'document.write() con una URL externa'],
    ];
    foreach ($reglas as $r) {
        if (preg_match($r[0], $js)) {
            return 'El script contiene código peligroso (' . $r[1] . ').';
        }
    }
    return '';
}

// Revisa el src de un <script>. Devuelve el motivo o ''.
function analisis_ia_scriptSrcPeligroso($src) {
    $src = trim((string)$src);
    if ($src === '') return '';
    if (stripos($src, 'javascript:') === 0) {
        return 'El <script> usa un src javascript:.';
    }
    if (stripos($src, 'data:') === 0) {
        return 'El <script> usa un src data:.';
    }
    if (analisis_ia_esUrlExterna($src)) {
        return 'El <script> carga código de un dominio externo (' . $src . ').';
    }
    return '';
}


// Parsea los atributos de una etiqueta (<meta ...> / <link ...>) a
// ['nombre' => 'valor'] en minúsculas. Atributos sin valor quedan en ''.
function analisis_ia_parseAtributos($attrStr) {
    $attrs = [];
    if (preg_match_all('/([a-zA-Z_:][a-zA-Z0-9_:.\-]*)(?:\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s>]+)))?/', (string)$attrStr, $m, PREG_SET_ORDER)) {
        foreach ($m as $a) {
            $v = '';
            if (isset($a[2]) && $a[2] !== '') $v = $a[2];
            elseif (isset($a[3]) && $a[3] !== '') $v = $a[3];
            elseif (isset($a[4])) $v = $a[4];
            $attrs[strtolower($a[1])] = $v;
        }
    }
    return $attrs;
}

// Revisa un <meta> ya parseado. Devuelve el motivo o ''.
// Permitido: charset, name="viewport" o name="color-scheme".
function analisis_ia_metaMotivo($attrs) {
    if (array_key_exists('charset', $attrs)) return '';
    if (isset($attrs['http-equiv'])) {
        return 'El <meta> con http-equiv="' . $attrs['http-equiv'] . '" no está permitido.';
    }
    if (isset($attrs['name'])) {
        $n = strtolower(trim($attrs['name']));
        if ($n === 'viewport' || $n === 'color-scheme') return '';
        return 'El <meta name="' . $attrs['name'] . '"> no está permitido.';
    }
    return 'El <meta> no está permitido (solo charset, viewport y color-scheme).';
}

// ¿Es un host permitido para <link> (y para src con //)?
function analisis_ia_linkHostPermitido($host) {
    $host = strtolower(explode(':', (string)$host)[0]);
    $permitidos = ['fonts.googleapis.com', 'fonts.gstatic.com', 'messbook.com.mx',
                   'cdn.jsdelivr.net', 'cdnjs.cloudflare.com'];
    if (in_array($host, $permitidos, true)) return true;
    if (substr($host, -16) === '.messbook.com.mx') return true;
    return false;
}

// ¿Es un href permitido para <link>? Relativo, data: de icono/imagen, o
// https:// de un host permitido. Nada de javascript: ni data:text/html.
function analisis_ia_linkHrefPermitido($href) {
    $href = trim((string)$href);
    if ($href === '') return false;
    $h = strtolower($href);
    if (strpos($h, 'javascript:') === 0) return false;
    if (strpos($h, 'data:text/html') === 0) return false;
    if (strpos($h, 'data:') === 0) {
        return (bool)preg_match('#^data:(,|image/)#i', $href);
    }
    if (strpos($h, '//') === 0) {
        if (preg_match('~^//([^/?#]+)~', $h, $m)) {
            return analisis_ia_linkHostPermitido($m[1]);
        }
        return false;
    }
    if (preg_match('~^https?://([^/?#]+)~i', $href, $m)) {
        return analisis_ia_linkHostPermitido($m[1]);
    }
    if (preg_match('#^[a-z][a-z0-9+.\-]*:#i', $href)) return false;
    return true;
}

// Revisa un <link> ya parseado. Devuelve el motivo o ''.
function analisis_ia_linkMotivo($attrs) {
    $rel = isset($attrs['rel']) ? strtolower(trim($attrs['rel'])) : '';
    if ($rel === '') return 'El <link> sin rel no está permitido.';
    $rels = preg_split('/\s+/', $rel, -1, PREG_SPLIT_NO_EMPTY);
    $permitidos = ['stylesheet', 'icon', 'preconnect'];
    foreach ($rels as $r) {
        if (!in_array($r, $permitidos, true)) {
            return 'El <link rel="' . $attrs['rel'] . '"> no está permitido.';
        }
    }
    if (!isset($attrs['href']) || trim($attrs['href']) === '') {
        return 'El <link> sin href no está permitido.';
    }
    if (!analisis_ia_linkHrefPermitido($attrs['href'])) {
        return 'El <link> apunta a una URL no permitida (' . $attrs['href'] . ').';
    }
    return '';
}

// Valida un archivo ya recibido en $rutaTmp.
// $nombre es el nombre completo (sanitizado) del archivo; la extensión se
// deriva de él. $contenido, si se pasa, se usa en vez de leer el archivo
// (útil para pruebas CLI); si no, se lee de $rutaTmp.
// Devuelve ['ok' => bool, 'motivo' => string]. El motivo nombra lo que se
// encontró, sin volcar el contenido del archivo.
function validarArchivoAnalisisIA($rutaTmp, $nombre, $contenido = null) {
    $nombre = sanitizarNombreArchivo($nombre);
    if ($nombre === '') {
        return ['ok' => false, 'motivo' => 'Nombre de archivo no válido.'];
    }

    $partes = explode('.', $nombre);
    if (count($partes) < 2) {
        return ['ok' => false, 'motivo' => 'El archivo no tiene extensión. Solo se permiten .xls, .csv y .html.'];
    }
    $ext = strtolower(end($partes));
    // Doble extensión / extensión peligrosa en cualquier parte del nombre:
    // "informe.php.html" o "informe.html.php" se rechazan igual.
    foreach (array_slice($partes, 1) as $p) {
        if (in_array(strtolower($p), ANALISIS_IA_EXT_PELIGROSAS, true)) {
            return ['ok' => false, 'motivo' => 'Extensión no permitida en el nombre del archivo (.' . strtolower($p) . ').'];
        }
    }
    if (!in_array($ext, ANALISIS_IA_EXT_PERMITIDAS, true)) {
        return ['ok' => false, 'motivo' => 'Solo se permiten archivos .xls, .csv y .html (recibí ".' . $ext . '").'];
    }

    if ($contenido === null) {
        if (!is_file($rutaTmp)) {
            return ['ok' => false, 'motivo' => 'No se pudo leer el archivo subido.'];
        }
        $tam = filesize($rutaTmp);
        if ($tam === false || $tam > ANALISIS_IA_MAX_BYTES) {
            return ['ok' => false, 'motivo' => 'El archivo excede el tamaño máximo de 50 MB.'];
        }
        $contenido = file_get_contents($rutaTmp);
        if ($contenido === false) {
            return ['ok' => false, 'motivo' => 'No se pudo leer el archivo subido.'];
        }
    } else {
        if (strlen($contenido) > ANALISIS_IA_MAX_BYTES) {
            return ['ok' => false, 'motivo' => 'El archivo excede el tamaño máximo de 50 MB.'];
        }
    }

    // Código de servidor disfrazado, en cualquier tipo permitido.
    if (stripos($contenido, '<?php') !== false || stripos($contenido, '<?=') !== false
        || stripos($contenido, '<%') !== false) {
        return ['ok' => false, 'motivo' => 'El archivo contiene código de servidor (<?php / <?= / <%).'];
    }

    if ($ext === 'csv') {
        if (stripos($contenido, '<script') !== false) {
            return ['ok' => false, 'motivo' => 'El CSV contiene una etiqueta <script>.'];
        }
        return ['ok' => true, 'motivo' => ''];
    }

    if ($ext === 'xls') {
        // Un .xls real es OLE: D0 CF 11 E0 A1 B1 1A E1.
        $magia = substr($contenido, 0, 8);
        if ($magia !== "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1") {
            return ['ok' => false, 'motivo' => 'El archivo .xls no tiene el formato esperado de Excel (no es OLE).'];
        }
        if (stripos(substr($contenido, 0, 8192), '<?php') !== false) {
            return ['ok' => false, 'motivo' => 'El archivo .xls contiene código de servidor (<?php).'];
        }
        return ['ok' => true, 'motivo' => ''];
    }

    // html: modo RECHAZAR lo peligroso, scripts internos permitidos.
    // <script> inline o con src relativo / messbook.com.mx está permitido
    // (los reportes lo necesitan para filtros y gráficas); se rechaza el
    // script que roba sesión, sale del frame o carga código externo.
    // <meta> y <link>: validación COMPARTIDA por regex sobre el contenido
    // original, antes de la ruta DOM o fallback. Así ambas rutas coinciden
    // y no depende de lo que el parser DOM inserte por su cuenta.
    // <meta> solo charset / viewport / color-scheme; <link> solo rel
    // stylesheet/icon/preconnect con href permitido (ver helpers).
    if (preg_match_all('/<\s*meta\b([^>]*)>/i', $contenido, $metas, PREG_SET_ORDER)) {
        foreach ($metas as $t) {
            $mot = analisis_ia_metaMotivo(analisis_ia_parseAtributos($t[1]));
            if ($mot !== '') return ['ok' => false, 'motivo' => $mot];
        }
    }
    if (preg_match_all('/<\s*link\b([^>]*)>/i', $contenido, $links, PREG_SET_ORDER)) {
        foreach ($links as $t) {
            $mot = analisis_ia_linkMotivo(analisis_ia_parseAtributos($t[1]));
            if ($mot !== '') return ['ok' => false, 'motivo' => $mot];
        }
    }
    // Etiquetas peligrosas: script ya NO está en esta lista, y meta/link
    // ya se validaron arriba con condición, así que tampoco están aquí.
    $etiquetasPeligrosas = ['iframe', 'object', 'embed', 'form', 'base'];
    // Fallback si el servidor no tiene la extensión php-xml (DOMDocument):
    // mismo rechazo, por regex, sin morir con un fatal.
    if (!class_exists('DOMDocument')) {
        if (preg_match('/<\s*(iframe|object|embed|form|base)\b/i', $contenido, $m)) {
            return ['ok' => false, 'motivo' => 'El HTML contiene una etiqueta no permitida <' . strtolower($m[1]) . '>.'];
        }
        // Scripts: revisa cada <script> (src y contenido).
        if (preg_match_all('/<script\b([^>]*)>(.*?)<\/script\s*>/is', $contenido, $bloques, PREG_SET_ORDER)) {
            foreach ($bloques as $b) {
                if (preg_match('/\bsrc\s*=\s*[\'"]([^\'"]*)[\'"]/i', $b[1], $s)) {
                    $mot = analisis_ia_scriptSrcPeligroso($s[1]);
                    if ($mot !== '') return ['ok' => false, 'motivo' => $mot];
                }
                $mot = analisis_ia_scriptPeligroso($b[2]);
                if ($mot !== '') return ['ok' => false, 'motivo' => $mot];
            }
        }
        // <script src="..."> sin cierre (también hay que revisarlo).
        if (preg_match_all('/<script\b[^>]*\bsrc\s*=\s*[\'"]([^\'"]*)[\'"]/i', $contenido, $srcs)) {
            foreach ($srcs[1] as $s) {
                $mot = analisis_ia_scriptSrcPeligroso($s);
                if ($mot !== '') return ['ok' => false, 'motivo' => $mot];
            }
        }
        if (preg_match('/\s(on\w+)\s*=/i', $contenido, $m)) {
            return ['ok' => false, 'motivo' => 'El HTML contiene un atributo de evento no permitido (' . strtolower($m[1]) . ').'];
        }
        if (preg_match('/\b(href|src|action|formaction)\s*=\s*[\'"]?\s*javascript\s*:/i', $contenido)) {
            return ['ok' => false, 'motivo' => 'El HTML contiene un enlace javascript:.'];
        }
        if (preg_match('/\b(href|src|action|formaction)\s*=\s*[\'"]?\s*data\s*:\s*text\/html/i', $contenido)) {
            return ['ok' => false, 'motivo' => 'El HTML contiene un enlace data:text/html.'];
        }
        if (preg_match('/<style[^>]*>.*?javascript\s*:/is', $contenido)
            || preg_match('/<style[^>]*>.*?expression\s*\(/is', $contenido)) {
            return ['ok' => false, 'motivo' => 'El HTML contiene CSS con código (javascript: / expression()).'];
        }
        return ['ok' => true, 'motivo' => ''];
    }
    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $cargado = $doc->loadHTML($contenido, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
    libxml_clear_errors();
    if (!$cargado) {
        return ['ok' => false, 'motivo' => 'El HTML no se pudo analizar.'];
    }
    foreach ($doc->getElementsByTagName('*') as $el) {
        $tag = strtolower($el->tagName);
        if ($tag === 'meta' || $tag === 'link') {
            // Ya validados arriba desde el texto original (con condición).
            continue;
        }
        if ($tag === 'script') {
            // Script permitido, pero revisado: src externo o contenido peligroso.
            if ($el->hasAttribute('src')) {
                $mot = analisis_ia_scriptSrcPeligroso($el->getAttribute('src'));
                if ($mot !== '') return ['ok' => false, 'motivo' => $mot];
            }
            $mot = analisis_ia_scriptPeligroso($el->textContent);
            if ($mot !== '') return ['ok' => false, 'motivo' => $mot];
            continue;
        }
        if (in_array($tag, $etiquetasPeligrosas, true)) {
            return ['ok' => false, 'motivo' => 'El HTML contiene una etiqueta no permitida <' . $tag . '>.'];
        }
        if ($el->hasAttributes()) {
            foreach ($el->attributes as $attr) {
                $an = strtolower($attr->name);
                $av = trim(strtolower($attr->value));
                if (strpos($an, 'on') === 0) {
                    return ['ok' => false, 'motivo' => 'El HTML contiene un atributo de evento no permitido (' . $an . ').'];
                }
                if (strpos($av, 'javascript:') === 0) {
                    return ['ok' => false, 'motivo' => 'El HTML contiene un enlace javascript: en el atributo ' . $an . '.'];
                }
                if (strpos($av, 'data:text/html') === 0) {
                    return ['ok' => false, 'motivo' => 'El HTML contiene un enlace data:text/html en el atributo ' . $an . '.'];
                }
            }
        }
    }
    // <style> con javascript: o expression() tampoco pasa.
    if (preg_match('/<style[^>]*>.*?javascript\s*:/is', $contenido)
        || preg_match('/<style[^>]*>.*?expression\s*\(/is', $contenido)) {
        return ['ok' => false, 'motivo' => 'El HTML contiene CSS con código (javascript: / expression()).'];
    }

    return ['ok' => true, 'motivo' => ''];
}
