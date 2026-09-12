<?php

function obtenerDatosTanquesPorZoocriadero()
{
    // Esto luego se va a reemplazar por el resultado de una consulta a la BD
    $registros = [
        ['zoocriadero' => 'Selva Viva', 'cantidad' => 10, 'tipo' => 'Fibra de vidrio',      'capacidad' => 3000, 'encargado' => 'Juan R.',  'estado' => 'Activo'],
        ['zoocriadero' => 'Acuarama',   'cantidad' => 8,  'tipo' => 'Circular plástico',    'capacidad' => 2800, 'encargado' => 'Ana S.',   'estado' => 'Activo'],
        ['zoocriadero' => 'El Paraíso', 'cantidad' => 5,  'tipo' => 'Rectangular plástico', 'capacidad' => 1800, 'encargado' => 'Dr. Ruiz', 'estado' => 'Activo'],
        ['zoocriadero' => 'Rio Claro',  'cantidad' => 3,  'tipo' => 'Fibra de vidrio',      'capacidad' => 1050, 'encargado' => 'Dr. Ruiz', 'estado' => 'Activo'],
        ['zoocriadero' => 'Agua Viva',  'cantidad' => 2,  'tipo' => 'Circular plástico',    'capacidad' => 800,  'encargado' => 'C. Pérez', 'estado' => 'Inactivo'],
    ];

    // --- LEEMOS LOS FILTROS ---
    $filtroZoocriadero = $_GET['zoocriadero'] ?? '';
    $filtroTipo         = $_GET['tanque']      ?? ''; // el select "Tanque" filtra por tipo de tanque

    // --- VALORES ÚNICOS PARA LOS SELECT ---
    $listaZoocriaderos = array_unique(array_column($registros, 'zoocriadero'));
    $listaTipos         = array_unique(array_column($registros, 'tipo'));

    // --- FILTRAMOS ---
    $registrosFiltrados = [];
    foreach ($registros as $registro) {
        $cumpleZoocriadero = ($filtroZoocriadero === '' || $registro['zoocriadero'] === $filtroZoocriadero);
        $cumpleTipo         = ($filtroTipo === '' || $registro['tipo'] === $filtroTipo);

        if ($cumpleZoocriadero && $cumpleTipo) {
            $registrosFiltrados[] = $registro;
        }
    }

    // --- TOTALES PARA LAS TARJETAS ---
    $totalZoocriaderos = count($registrosFiltrados);
    $totalTanques = 0;
    $capacidadTotal = 0;
    $totalTanquesActivos = 0;

    foreach ($registrosFiltrados as $registro) {
        $totalTanques += $registro['cantidad'];
        $capacidadTotal += $registro['capacidad'];
        if ($registro['estado'] === 'Activo') {
            $totalTanquesActivos += $registro['cantidad'];
        }
    }

    // --- AGRUPAMOS POR TIPO DE TANQUE (para la gráfica de dona) ---
    $cantidadPorTipo = [];
    foreach ($registrosFiltrados as $registro) {
        $tipo = $registro['tipo'];
        if (!isset($cantidadPorTipo[$tipo])) {
            $cantidadPorTipo[$tipo] = 0;
        }
        $cantidadPorTipo[$tipo] += $registro['cantidad'];
    }

    // Colores fijos, se van repartiendo en orden según cuántos tipos haya
    $paletaColores = ['#3b3fa8', '#2f7dfa', '#7c6ee0', '#8bd8f0', '#21a666', '#e0952d'];

    $segmentosDonut = [];
    $indiceColor = 0;
    foreach ($cantidadPorTipo as $tipo => $cantidad) {
        $porcentaje = $totalTanques > 0 ? ($cantidad / $totalTanques) * 100 : 0;
        $segmentosDonut[] = [
            'tipo'       => $tipo,
            'cantidad'   => $cantidad,
            'porcentaje' => round($porcentaje, 1),
            'color'      => $paletaColores[$indiceColor % count($paletaColores)],
        ];
        $indiceColor++;
    }

    return [
        'registrosFiltrados'  => $registrosFiltrados,
        'listaZoocriaderos'   => $listaZoocriaderos,
        'listaTipos'          => $listaTipos,
        'filtroZoocriadero'   => $filtroZoocriadero,
        'filtroTipo'          => $filtroTipo,
        'totalZoocriaderos'   => $totalZoocriaderos,
        'totalTanques'        => $totalTanques,
        'capacidadTotal'      => $capacidadTotal,
        'totalTanquesActivos' => $totalTanquesActivos,
        'segmentosDonut'      => $segmentosDonut,
    ];
}