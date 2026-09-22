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
    // seguimiento_zoocriadero SÍ tiene una columna de texto (estado_actividad)
    // con 'Completada'/'En progreso'/'Retrasada'; se usa directamente.
    // seguimiento_terreno NO la tiene, solo un número (1/2/3) en "estado",
    // así que ahí se convierte a texto con CASE.
    $sql = "SELECT u.nombre || ' ' || u.apellido AS auxiliar,
                TO_CHAR(sz.fecha, 'DD/MM/YYYY') AS fecha,
                sz.estado_actividad AS estado
            FROM seguimiento_zoocriadero sz
            INNER JOIN usuario u ON u.id_usuario = sz.id_usuario
            INNER JOIN rol r ON r.id_rol = u.id_rol
            WHERE r.nombre_rol = 'Auxiliar'

            UNION ALL

            SELECT u.nombre || ' ' || u.apellido AS auxiliar,
                TO_CHAR(st.fecha, 'DD/MM/YYYY') AS fecha,
                CASE st.estado
                    WHEN 1 THEN 'Completada'
                    WHEN 2 THEN 'En progreso'
                    WHEN 3 THEN 'Retrasada'
                    ELSE 'Completada'
                END AS estado
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

    // --- COMPARATIVA VS EL MES ANTERIOR (respeta el filtro de auxiliar; no depende de Fecha Inicio/Fin) ---
    $meses = mesesComparativa($filtroFechaInicio);
    $actualTotal = 0;
    $actualCompletas = 0;
    $actualEnProgreso = 0;
    $actualRetrasadas = 0;
    $anteriorTotal = 0;
    $anteriorCompletas = 0;
    $anteriorEnProgreso = 0;
    $anteriorRetrasadas = 0;

    foreach ($registros as $registro) {
        if ($filtroAuxiliar !== '' && $registro['auxiliar'] !== $filtroAuxiliar) {
            continue;
        }

        if (fechaEnMes($registro['fecha'], $meses['actual'])) {
            $actualTotal++;
            if ($registro['estado'] === 'Completada') {
                $actualCompletas++;
            } elseif ($registro['estado'] === 'En progreso') {
                $actualEnProgreso++;
            } elseif ($registro['estado'] === 'Retrasada') {
                $actualRetrasadas++;
            }
        } elseif (fechaEnMes($registro['fecha'], $meses['anterior'])) {
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

    $comparativas = [
        'totalActividades' => armarComparativa($actualTotal, $anteriorTotal, true),
        'totalCompletas'   => armarComparativa($actualCompletas, $anteriorCompletas, true),
        'totalEnProgreso'  => armarComparativa($actualEnProgreso, $anteriorEnProgreso, true),
        'totalRetrasadas'  => armarComparativa($actualRetrasadas, $anteriorRetrasadas, false),
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
