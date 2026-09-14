<?php

function generarPuntosLinea($valores, $valorMaximo, $anchoGrafico, $altoGrafico, $margenIzquierdo)
{
    $cantidadPuntos = count($valores);
    $espacioEntrePuntos = $anchoGrafico / ($cantidadPuntos - 1);

    $puntos = [];
    foreach ($valores as $indice => $valor) {
        $x = $margenIzquierdo + ($indice * $espacioEntrePuntos);
        $y = $altoGrafico - (($valor / $valorMaximo) * $altoGrafico) + 10;
        $puntos[] = round($x) . ',' . round($y);
    }
    return implode(' ', $puntos);
}

function obtenerDatosActividadesDeTerrenoPorTipo()
{
    $actividades = [
        ['tipo' => 'Inspección', 'zoocriadero' => 'Selva Viva', 'fecha' => '02/05/2024', 'estado' => 'Completada'],
        ['tipo' => 'Inspección', 'zoocriadero' => 'Acuarama',   'fecha' => '04/05/2024', 'estado' => 'Completada'],
        ['tipo' => 'Inspección', 'zoocriadero' => 'Selva Viva', 'fecha' => '09/05/2024', 'estado' => 'Completada'],
        ['tipo' => 'Inspección', 'zoocriadero' => 'Acuarama',   'fecha' => '11/05/2024', 'estado' => 'Completada'],
        ['tipo' => 'Inspección', 'zoocriadero' => 'Selva Viva', 'fecha' => '16/05/2024', 'estado' => 'Completada'],
        ['tipo' => 'Inspección', 'zoocriadero' => 'Acuarama',   'fecha' => '20/05/2024', 'estado' => 'Completada'],
        ['tipo' => 'Inspección', 'zoocriadero' => 'Selva Viva', 'fecha' => '23/05/2024', 'estado' => 'En progreso'],
        ['tipo' => 'Inspección', 'zoocriadero' => 'Acuarama',   'fecha' => '27/05/2024', 'estado' => 'Retrasada'],

        ['tipo' => 'Siembra', 'zoocriadero' => 'Selva Viva', 'fecha' => '03/05/2024', 'estado' => 'Completada'],
        ['tipo' => 'Siembra', 'zoocriadero' => 'Acuarama',   'fecha' => '07/05/2024', 'estado' => 'Completada'],
        ['tipo' => 'Siembra', 'zoocriadero' => 'Selva Viva', 'fecha' => '14/05/2024', 'estado' => 'Completada'],
        ['tipo' => 'Siembra', 'zoocriadero' => 'Acuarama',   'fecha' => '21/05/2024', 'estado' => 'Completada'],
        ['tipo' => 'Siembra', 'zoocriadero' => 'Selva Viva', 'fecha' => '24/05/2024', 'estado' => 'En progreso'],
        ['tipo' => 'Siembra', 'zoocriadero' => 'Acuarama',   'fecha' => '29/05/2024', 'estado' => 'Retrasada'],

        ['tipo' => 'Seguimiento', 'zoocriadero' => 'Selva Viva', 'fecha' => '05/05/2024', 'estado' => 'Completada'],
        ['tipo' => 'Seguimiento', 'zoocriadero' => 'Acuarama',   'fecha' => '08/05/2024', 'estado' => 'Completada'],
        ['tipo' => 'Seguimiento', 'zoocriadero' => 'Selva Viva', 'fecha' => '13/05/2024', 'estado' => 'Completada'],
        ['tipo' => 'Seguimiento', 'zoocriadero' => 'Acuarama',   'fecha' => '17/05/2024', 'estado' => 'Completada'],
        ['tipo' => 'Seguimiento', 'zoocriadero' => 'Selva Viva', 'fecha' => '22/05/2024', 'estado' => 'Completada'],
        ['tipo' => 'Seguimiento', 'zoocriadero' => 'Acuarama',   'fecha' => '25/05/2024', 'estado' => 'En progreso'],
        ['tipo' => 'Seguimiento', 'zoocriadero' => 'Selva Viva', 'fecha' => '28/05/2024', 'estado' => 'Retrasada'],

        ['tipo' => 'Resiembra', 'zoocriadero' => 'Selva Viva', 'fecha' => '06/05/2024', 'estado' => 'Completada'],
        ['tipo' => 'Resiembra', 'zoocriadero' => 'Acuarama',   'fecha' => '15/05/2024', 'estado' => 'Completada'],
        ['tipo' => 'Resiembra', 'zoocriadero' => 'Selva Viva', 'fecha' => '26/05/2024', 'estado' => 'Completada'],
        ['tipo' => 'Resiembra', 'zoocriadero' => 'Acuarama',   'fecha' => '30/05/2024', 'estado' => 'En progreso'],
    ];

    $listaTipos = ['Inspección', 'Siembra', 'Seguimiento', 'Resiembra'];

    $coloresPorTipo = [
        'Inspección'  => '#2f5fdc',
        'Siembra'     => '#5bc9e8',
        'Seguimiento' => '#6c5ce7',
        'Resiembra'   => '#b8a8f5',
    ];

    $filtroTipo         = $_GET['tipo']         ?? '';
    $filtroZoocriadero  = $_GET['zoocriadero']  ?? '';
    $filtroFechaInicio  = $_GET['fecha_inicio'] ?? '';
    $filtroFechaFin     = $_GET['fecha_fin']    ?? '';

    $listaZoocriaderos = array_unique(array_column($actividades, 'zoocriadero'));

    $actividadesFiltradas = [];

    foreach ($actividades as $actividad) {
        $fechaActividad = DateTime::createFromFormat('d/m/Y', $actividad['fecha']);

        $cumpleTipo        = ($filtroTipo === '' || $actividad['tipo'] === $filtroTipo);
        $cumpleZoocriadero = ($filtroZoocriadero === '' || $actividad['zoocriadero'] === $filtroZoocriadero);

        $cumpleFechaInicio = true;
        if ($filtroFechaInicio !== '') {
            $fechaDesde = DateTime::createFromFormat('Y-m-d', $filtroFechaInicio);
            $cumpleFechaInicio = ($fechaActividad >= $fechaDesde);
        }

        $cumpleFechaFin = true;
        if ($filtroFechaFin !== '') {
            $fechaHasta = DateTime::createFromFormat('Y-m-d', $filtroFechaFin);
            $cumpleFechaFin = ($fechaActividad <= $fechaHasta);
        }

        if ($cumpleTipo && $cumpleZoocriadero && $cumpleFechaInicio && $cumpleFechaFin) {
            $actividadesFiltradas[] = $actividad;
        }
    }

    $resumenPorTipo = [];

    foreach ($listaTipos as $tipo) {
        $completadas = 0;
        $enProgreso  = 0;
        $retrasadas  = 0;

        foreach ($actividadesFiltradas as $actividad) {
            if ($actividad['tipo'] === $tipo) {
                if ($actividad['estado'] === 'Completada') {
                    $completadas++;
                } elseif ($actividad['estado'] === 'En progreso') {
                    $enProgreso++;
                } elseif ($actividad['estado'] === 'Retrasada') {
                    $retrasadas++;
                }
            }
        }

        $totalTipo = $completadas + $enProgreso + $retrasadas;

        $resumenPorTipo[] = [
            'tipo'        => $tipo,
            'color'       => $coloresPorTipo[$tipo],
            'completadas' => $completadas,
            'enProgreso'  => $enProgreso,
            'retrasadas'  => $retrasadas,
            'total'       => $totalTipo,
        ];
    }

    $totalActividades = 0;
    $totalCompletas    = 0;
    $totalEnProgreso   = 0;
    $totalRetrasadas   = 0;

    foreach ($resumenPorTipo as $fila) {
        $totalActividades += $fila['total'];
        $totalCompletas    += $fila['completadas'];
        $totalEnProgreso   += $fila['enProgreso'];
        $totalRetrasadas   += $fila['retrasadas'];
    }

    for ($i = 0; $i < count($resumenPorTipo); $i++) {
        $totalFila = $resumenPorTipo[$i]['total'];
        $resumenPorTipo[$i]['porcentaje'] = $totalActividades > 0
            ? round(($totalFila / $totalActividades) * 100, 1)
            : 0;
    }

    $fechasEvolucion = ['1 May', '8 May', '15 May', '22 May', '29 May'];

    $valoresEvolucionPorTipo = [
        'Inspección'  => [18, 22, 19, 25, 23],
        'Siembra'     => [10, 13, 11, 15, 14],
        'Seguimiento' => [28, 24, 30, 27, 33],
        'Resiembra'   => [5, 7, 6, 9, 8],
    ];

    $valorMaximoEvolucion = 40;
    $anchoGraficoEvolucion = 500;
    $altoGraficoEvolucion  = 160;
    $margenIzquierdoEvolucion = 40;

    $seriesEvolucion = [];
    foreach ($listaTipos as $tipo) {
        $seriesEvolucion[] = [
            'tipo'   => $tipo,
            'color'  => $coloresPorTipo[$tipo],
            'puntos' => generarPuntosLinea(
                $valoresEvolucionPorTipo[$tipo],
                $valorMaximoEvolucion,
                $anchoGraficoEvolucion,
                $altoGraficoEvolucion,
                $margenIzquierdoEvolucion
            ),
        ];
    }

    return [
        'listaTipos'           => $listaTipos,
        'listaZoocriaderos'    => $listaZoocriaderos,
        'filtroTipo'           => $filtroTipo,
        'filtroZoocriadero'    => $filtroZoocriadero,
        'filtroFechaInicio'    => $filtroFechaInicio,
        'filtroFechaFin'       => $filtroFechaFin,
        'resumenPorTipo'       => $resumenPorTipo,
        'totalActividades'     => $totalActividades,
        'totalCompletas'       => $totalCompletas,
        'totalEnProgreso'      => $totalEnProgreso,
        'totalRetrasadas'      => $totalRetrasadas,
        'fechasEvolucion'      => $fechasEvolucion,
        'seriesEvolucion'      => $seriesEvolucion,
        'valorMaximoEvolucion' => $valorMaximoEvolucion,
    ];
}
