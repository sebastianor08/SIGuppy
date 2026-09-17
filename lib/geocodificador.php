<?php


function geocodificarDireccion($direccion, $barrio = null, $comuna = null, $ciudad = 'Santiago de Cali', $pais = 'Colombia') {
    $direccion = trim((string) $direccion);
    if ($direccion === '') {
        return null;
    }

    $partes   = array_filter([$direccion, $barrio, $ciudad, $pais]);
    $consulta = implode(', ', $partes);

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
        return null; 
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