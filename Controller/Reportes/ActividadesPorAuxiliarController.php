<?php

function obtenerDatosActividadesPorAuxiliar()
{
    $registros = [
        ['auxiliar' => 'Juan R.', 'fecha' => '02/05/2024', 'estado' => 'Completada'],
        ['auxiliar' => 'Juan R.', 'fecha' => '03/05/2024', 'estado' => 'Completada'],
        ['auxiliar' => 'Juan R.', 'fecha' => '05/05/2024', 'estado' => 'Completada'],
        ['auxiliar' => 'Juan R.', 'fecha' => '08/05/2024', 'estado' => 'Completada'],
        ['auxiliar' => 'Juan R.', 'fecha' => '10/05/2024', 'estado' => 'Completada'],
        ['auxiliar' => 'Juan R.', 'fecha' => '12/05/2024', 'estado' => 'Completada'],
        ['auxiliar' => 'Juan R.', 'fecha' => '14/05/2024', 'estado' => 'En progreso'],
        ['auxiliar' => 'Juan R.', 'fecha' => '20/05/2024', 'estado' => 'Retrasada'],

        ['auxiliar' => 'Ana S.', 'fecha' => '02/05/2024', 'estado' => 'Completada'],
        ['auxiliar' => 'Ana S.', 'fecha' => '04/05/2024', 'estado' => 'Completada'],
        ['auxiliar' => 'Ana S.', 'fecha' => '06/05/2024', 'estado' => 'Completada'],
        ['auxiliar' => 'Ana S.', 'fecha' => '09/05/2024', 'estado' => 'Completada'],
        ['auxiliar' => 'Ana S.', 'fecha' => '11/05/2024', 'estado' => 'Completada'],
        ['auxiliar' => 'Ana S.', 'fecha' => '15/05/2024', 'estado' => 'En progreso'],
        ['auxiliar' => 'Ana S.', 'fecha' => '22/05/2024', 'estado' => 'Retrasada'],

        ['auxiliar' => 'Carlos P.', 'fecha' => '03/05/2024', 'estado' => 'Completada'],
        ['auxiliar' => 'Carlos P.', 'fecha' => '05/05/2024', 'estado' => 'Completada'],
        ['auxiliar' => 'Carlos P.', 'fecha' => '07/05/2024', 'estado' => 'Completada'],
        ['auxiliar' => 'Carlos P.', 'fecha' => '10/05/2024', 'estado' => 'Completada'],
        ['auxiliar' => 'Carlos P.', 'fecha' => '16/05/2024', 'estado' => 'En progreso'],
        ['auxiliar' => 'Carlos P.', 'fecha' => '23/05/2024', 'estado' => 'Retrasada'],

        ['auxiliar' => 'Luis G.', 'fecha' => '04/05/2024', 'estado' => 'Completada'],
        ['auxiliar' => 'Luis G.', 'fecha' => '06/05/2024', 'estado' => 'Completada'],
        ['auxiliar' => 'Luis G.', 'fecha' => '08/05/2024', 'estado' => 'Completada'],
        ['auxiliar' => 'Luis G.', 'fecha' => '17/05/2024', 'estado' => 'En progreso'],
        ['auxiliar' => 'Luis G.', 'fecha' => '24/05/2024', 'estado' => 'Retrasada'],

        ['auxiliar' => 'Sofía M.', 'fecha' => '02/05/2024', 'estado' => 'Completada'],
        ['auxiliar' => 'Sofía M.', 'fecha' => '04/05/2024', 'estado' => 'Completada'],
        ['auxiliar' => 'Sofía M.', 'fecha' => '07/05/2024', 'estado' => 'Completada'],
        ['auxiliar' => 'Sofía M.', 'fecha' => '09/05/2024', 'estado' => 'Completada'],
        ['auxiliar' => 'Sofía M.', 'fecha' => '13/05/2024', 'estado' => 'Completada'],
        ['auxiliar' => 'Sofía M.', 'fecha' => '18/05/2024', 'estado' => 'En progreso'],
        ['auxiliar' => 'Sofía M.', 'fecha' => '21/05/2024', 'estado' => 'Retrasada'],

        ['auxiliar' => 'Pedro L.', 'fecha' => '03/05/2024', 'estado' => 'Completada'],
        ['auxiliar' => 'Pedro L.', 'fecha' => '06/05/2024', 'estado' => 'Completada'],
        ['auxiliar' => 'Pedro L.', 'fecha' => '09/05/2024', 'estado' => 'Completada'],
        ['auxiliar' => 'Pedro L.', 'fecha' => '12/05/2024', 'estado' => 'Completada'],
        ['auxiliar' => 'Pedro L.', 'fecha' => '15/05/2024', 'estado' => 'En progreso'],
        ['auxiliar' => 'Pedro L.', 'fecha' => '19/05/2024', 'estado' => 'En progreso'],
        ['auxiliar' => 'Pedro L.', 'fecha' => '25/05/2024', 'estado' => 'Retrasada'],

        ['auxiliar' => 'Diana R.', 'fecha' => '01/05/2024', 'estado' => 'Completada'],
        ['auxiliar' => 'Diana R.', 'fecha' => '03/05/2024', 'estado' => 'Completada'],
        ['auxiliar' => 'Diana R.', 'fecha' => '05/05/2024', 'estado' => 'Completada'],
        ['auxiliar' => 'Diana R.', 'fecha' => '08/05/2024', 'estado' => 'Completada'],
        ['auxiliar' => 'Diana R.', 'fecha' => '11/05/2024', 'estado' => 'Completada'],
        ['auxiliar' => 'Diana R.', 'fecha' => '14/05/2024', 'estado' => 'Completada'],
        ['auxiliar' => 'Diana R.', 'fecha' => '17/05/2024', 'estado' => 'Completada'],
        ['auxiliar' => 'Diana R.', 'fecha' => '20/05/2024', 'estado' => 'En progreso'],
    ];

    $filtroAuxiliar = $_GET['auxiliar'] ?? '';
    $filtroFechaInicio = $_GET['fecha_inicio'] ?? '';
    $filtroFechaFin = $_GET['fecha_fin'] ?? '';

    $listaAuxiliares = array_unique(array_column($registros, 'auxiliar'));

    $registrosFiltrados = [];

    foreach ($registros as $registro) {
        $fechaRegistro = DateTime::createFromFormat('d/m/Y', $registro['fecha']);

        $cumpleAuxiliar = ($filtroAuxiliar === '' || $registro['auxiliar'] === $filtroAuxiliar);

        $cumpleFechaInicio = true;
        if ($filtroFechaInicio !== '') {
            $fechaDesde = DateTime::createFromFormat('Y-m-d', $filtroFechaInicio);
            $cumpleFechaInicio = ($fechaRegistro >= $fechaDesde);
        }

        $cumpleFechaFin = true;
        if ($filtroFechaFin !== '') {
            $fechaHasta = DateTime::createFromFormat('Y-m-d', $filtroFechaFin);
            $cumpleFechaFin = ($fechaRegistro <= $fechaHasta);
        }

        if ($cumpleAuxiliar && $cumpleFechaInicio && $cumpleFechaFin) {
            $registrosFiltrados[] = $registro;
        }
    }

    $resumenPorAuxiliar = [];

    foreach ($registrosFiltrados as $registro) {
        $auxiliar = $registro['auxiliar'];

        if (!isset($resumenPorAuxiliar[$auxiliar])) {
            $resumenPorAuxiliar[$auxiliar] = [
                'auxiliar' => $auxiliar,
                'completadas' => 0,
                'enProgreso' => 0,
                'retrasadas' => 0,
                'total' => 0,
            ];
        }

        if ($registro['estado'] === 'Completada') {
            $resumenPorAuxiliar[$auxiliar]['completadas']++;
        } elseif ($registro['estado'] === 'En progreso') {
            $resumenPorAuxiliar[$auxiliar]['enProgreso']++;
        } elseif ($registro['estado'] === 'Retrasada') {
            $resumenPorAuxiliar[$auxiliar]['retrasadas']++;
        }

        $resumenPorAuxiliar[$auxiliar]['total']++;
    }

    foreach ($resumenPorAuxiliar as $auxiliar => $fila) {
        $resumenPorAuxiliar[$auxiliar]['cumplimiento'] = $fila['total'] > 0
            ? round(($fila['completadas'] / $fila['total']) * 100, 1)
            : 0;
    }

    $totalActividades = count($registrosFiltrados);
    $totalCompletas = 0;
    $totalEnProgreso = 0;
    $totalRetrasadas = 0;

    foreach ($resumenPorAuxiliar as $fila) {
        $totalCompletas += $fila['completadas'];
        $totalEnProgreso += $fila['enProgreso'];
        $totalRetrasadas += $fila['retrasadas'];
    }

    $cumplimientoGeneral = $totalActividades > 0
        ? round(($totalCompletas / $totalActividades) * 100, 1)
        : 0;

    $paletaColores = ['#2f7dfa', '#3b3fa8', '#7c6ee0', '#8bd8f0', '#21a666', '#e0952d'];

    $segmentosDonut = [];
    $indiceColor = 0;
    foreach ($resumenPorAuxiliar as $fila) {
        $porcentaje = $totalActividades > 0 ? ($fila['total'] / $totalActividades) * 100 : 0;
        $segmentosDonut[] = [
            'auxiliar' => $fila['auxiliar'],
            'total' => $fila['total'],
            'porcentaje' => round($porcentaje, 1),
            'color' => $paletaColores[$indiceColor % count($paletaColores)],
        ];
        $indiceColor++;
    }

    $porPagina = 4;
    $totalAuxiliares = count($resumenPorAuxiliar);
    $totalPaginas = max(1, (int) ceil($totalAuxiliares / $porPagina));
    $paginaActual = (int) ($_GET['pagina'] ?? 1);

    if ($paginaActual < 1) {
        $paginaActual = 1;
    }
    if ($paginaActual > $totalPaginas) {
        $paginaActual = $totalPaginas;
    }

    $listaResumen = array_values($resumenPorAuxiliar);
    $inicio = ($paginaActual - 1) * $porPagina;
    $auxiliaresPagina = array_slice($listaResumen, $inicio, $porPagina);

    $desde = $totalAuxiliares > 0 ? $inicio + 1 : 0;
    $hasta = min($inicio + $porPagina, $totalAuxiliares);

    return [
        'listaAuxiliares' => $listaAuxiliares,
        'filtroAuxiliar' => $filtroAuxiliar,
        'filtroFechaInicio' => $filtroFechaInicio,
        'filtroFechaFin' => $filtroFechaFin,
        'totalActividades' => $totalActividades,
        'totalCompletas' => $totalCompletas,
        'totalEnProgreso' => $totalEnProgreso,
        'totalRetrasadas' => $totalRetrasadas,
        'cumplimientoGeneral' => $cumplimientoGeneral,
        'segmentosDonut' => $segmentosDonut,
        'auxiliaresPagina' => $auxiliaresPagina,
        'totalAuxiliares' => $totalAuxiliares,
        'totalPaginas' => $totalPaginas,
        'paginaActual' => $paginaActual,
        'desde' => $desde,
        'hasta' => $hasta,
    ];
}