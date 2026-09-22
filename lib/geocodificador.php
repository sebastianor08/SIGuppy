<?php
function geocodificarDireccion($direccion, $barrio = null, $comuna = null, $ciudad = 'Santiago de Cali', $pais = 'Colombia') {
    $direccion = trim((string) $direccion);
    if ($direccion === '') {
        return null;
    }

    $normalizada = normalizarDireccionColombia($direccion);
    $via         = viaPrincipal($normalizada);

    $intentos = [
        [$normalizada, $barrio, $ciudad, $pais],
        [$normalizada, $ciudad, $pais],
        [$via, $barrio, $ciudad, $pais],
        [$barrio, $ciudad, $pais],
        [$comuna, $ciudad, $pais],   // último recurso: centro de la comuna
    ];

    $consultasHechas = [];
    foreach ($intentos as $partes) {
        $partes = array_values(array_filter($partes, function ($p) {
            return trim((string) $p) !== '';
        }));
        // Si solo queda ciudad y país, no sirve (pondría el punto en el centro de Cali)
        if (count($partes) <= 2) {
            continue;
        }
        $consulta = implode(', ', $partes);
        if (isset($consultasHechas[$consulta])) {
            continue; // no repetir la misma búsqueda
        }

        if (!empty($consultasHechas)) {
            usleep(1100000); // respetar el límite de 1 solicitud/segundo de Nominatim
        }
        $consultasHechas[$consulta] = true;

        $resultado = consultarNominatim($consulta);
        if ($resultado === false) {
            return null; // sin internet / timeout: no tiene sentido seguir intentando
        }
        if ($resultado !== null) {
            return $resultado;
        }
    }

    return null;
}

// Hace UNA consulta. Devuelve el punto, null si no encontró nada,
// o false si no hubo respuesta del servidor.
function consultarNominatim($consulta) {
    $url = 'https://nominatim.openstreetmap.org/search?' . http_build_query([
        'q'            => $consulta,
        'format'       => 'json',
        'limit'        => 1,
        'countrycodes' => 'co',
    ]);

    $contexto = stream_context_create([
        'http' => [
            'header'  => "User-Agent: SIGuppys-SENA-3145788/1.0 (proyecto academico)\r\n",
            'timeout' => 6,
        ],
    ]);

    $respuesta = @file_get_contents($url, false, $contexto);
    if ($respuesta === false) {
        return false;
    }

    $datos = json_decode($respuesta, true);
    if (!is_array($datos) || empty($datos[0]['lat']) || empty($datos[0]['lon'])) {
        return null;
    }

    return [
        'lat' => (float) $datos[0]['lat'],
        'lng' => (float) $datos[0]['lon'],
    ];
}

function normalizarDireccionColombia($direccion) {
    $d = ' ' . preg_replace('/\s+/u', ' ', trim($direccion)) . ' ';

    $abreviaturas = [
        '/\b(cll|cl|clle)\.?\s/iu'            => 'Calle ',
        '/\b(cra|cr|kra|kr|carr|crr)\.?\s/iu' => 'Carrera ',
        '/\b(av|avda|avd)\.?\s/iu'            => 'Avenida ',
        '/\b(dg|diag)\.?\s/iu'                => 'Diagonal ',
        '/\b(tv|tr|trans|transv)\.?\s/iu'     => 'Transversal ',
    ];
    $d = preg_replace(array_keys($abreviaturas), array_values($abreviaturas), $d);

    $d = preg_replace('/\s(?:No\b\.?|N°|Nº)\s?/iu', ' # ', $d);

    $d = preg_replace('/(\d+\s?[a-z]?)\s?N\b\.?/iu', '$1 Norte', $d);
    $d = preg_replace('/(\d+\s?[a-z]?)\s?S\b\.?/iu', '$1 Sur', $d);
    $d = preg_replace('/\b(oeste|oe)\b/iu', 'Oeste', $d);
    $d = preg_replace('/\bnorte\b/iu', 'Norte', $d);

    $d = preg_replace('/\s?#\s?/u', ' # ', $d);

    $d = preg_replace('/\b([a-záéíóúñ]+)(\s+\1\b)+/iu', '$1', $d);

    return trim(preg_replace('/\s+/u', ' ', $d));
}

function viaPrincipal($direccion) {
    $partes = explode('#', $direccion, 2);
    $via = trim($partes[0]);
    return $via !== $direccion ? $via : '';
}