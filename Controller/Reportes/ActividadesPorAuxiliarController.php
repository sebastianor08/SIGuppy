<?php

require_once __DIR__ . '/ComparativaMensual.php';

function obtenerDatosActividadesPorAuxiliar()
{
    require __DIR__ . '/../../lib/conf/conf.php';

    $conexion = pg_connect("host=$host port=$port dbname=$database user=$user password=$password");

    if (!$conexion) {
        die("No se pudo conectar a la base de datos");
    }

    // Un auxiliar registra trabajo en dos tablas distintas:
    // seguimiento_zoocriadero y seguimiento_terreno.
    // UNION ALL simplemente pega los resultados de las dos consultas,
    // una debajo de la otra.
    $sql = "SELECT u.nombre || ' ' || u.apellido AS auxiliar,
                TO_CHAR(sz.fecha, 'DD/MM/YYYY') AS fecha,
                sz.estado AS estado
            FROM seguimiento_zoocriadero sz
            INNER JOIN usuario u ON u.id_usuario = sz.id_usuario
            INNER JOIN rol r ON r.id_rol = u.id_rol
            WHERE r.nombre_rol = 'Auxiliar'

            UNION ALL

            SELECT u.nombre || ' ' || u.apellido AS auxiliar,
                TO_CHAR(st.fecha, 'DD/MM/YYYY') AS fecha,
                st.estado AS estado
            FROM seguimiento_terreno st
            INNER JOIN usuario u ON u.id_usuario = st.id_usuario
            INNER JOIN rol r ON r.id_rol = u.id_rol
            WHERE r.nombre_rol = 'Auxiliar'

            ORDER BY auxiliar";

    $resultado = pg_query($conexion, $sql);

    if (!$resultado) {
        die("Error en la consulta: " . pg_last_error($conexion));
    }

    $registros = [];
    while ($fila = pg_fetch_assoc($resultado)) {
        $registros[] = $fila;
    }

    pg_close($conexion);

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

    // --- COMPARATIVA VS MES ANTERIOR (según la Fecha Inicio; respeta el filtro de auxiliar) ---
    $mesAnterior = obtenerMesAnterior($filtroFechaInicio);
    $anteriorTotal = 0;
    $anteriorCompletas = 0;
    $anteriorEnProgreso = 0;
    $anteriorRetrasadas = 0;

    if ($mesAnterior !== null) {
        foreach ($registros as $registro) {
            $cumpleAuxiliar = ($filtroAuxiliar === '' || $registro['auxiliar'] === $filtroAuxiliar);

            if ($cumpleAuxiliar && fechaEnMesAnterior($registro['fecha'], $mesAnterior)) {
                $anteriorTotal++;
                if ($registro['estado'] === 'Completada') {
                    $anteriorCompletas++;
                } elseif ($registro['estado'] === 'En progreso') {
                    $anteriorEnProgreso++;
                } elseif ($registro['estado'] === 'Retrasada') {
                    $anteriorRetrasadas++;
                }
            }
        }
    }

    $comparativas = [
        'totalActividades' => armarComparativa($totalActividades, $anteriorTotal, $mesAnterior, true),
        'totalCompletas'   => armarComparativa($totalCompletas, $anteriorCompletas, $mesAnterior, true),
        'totalEnProgreso'  => armarComparativa($totalEnProgreso, $anteriorEnProgreso, $mesAnterior, null),
        'totalRetrasadas'  => armarComparativa($totalRetrasadas, $anteriorRetrasadas, $mesAnterior, false),
    ];

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
        'comparativas' => $comparativas,
        'segmentosDonut' => $segmentosDonut,
        'auxiliaresPagina' => $auxiliaresPagina,
        'totalAuxiliares' => $totalAuxiliares,
        'totalPaginas' => $totalPaginas,
        'paginaActual' => $paginaActual,
        'desde' => $desde,
        'hasta' => $hasta,
    ];
}
