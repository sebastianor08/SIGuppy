<?php

function obtenerDatosSeguimientoDeActividades()
{
    // ---------------------------------------------------------
    // 1. NOS CONECTAMOS A LA BASE DE DATOS
    // ---------------------------------------------------------
    require __DIR__ . '/../../lib/conf/conf.php';

    $conexion = pg_connect("host=$host port=$port dbname=$database user=$user password=$password");

    if (!$conexion) {
        die("No se pudo conectar a la base de datos");
    }

    // ---------------------------------------------------------
    // 2. TRAEMOS LAS ACTIVIDADES HECHAS EN LOS ZOOCRIADEROS
    // ---------------------------------------------------------
    // Una fila de seguimiento_zoocriadero puede tener varias actividades,
    // por eso se une con actividad_zoocriadero y con actividad.
    $sql = "SELECT a.nombre AS actividad,
                z.nombre AS zoocriadero,
                TO_CHAR(sz.fecha, 'DD/MM/YYYY') AS inicio,
                TO_CHAR(sz.fecha, 'DD/MM/YYYY') AS fin,
                u.nombre || ' ' || u.apellido AS responsable,
                sz.estado AS estado
            FROM seguimiento_zoocriadero sz
            INNER JOIN actividad_zoocriadero az ON az.id_seguimiento = sz.id_seguimiento
            INNER JOIN actividad a ON a.id_actividad = az.id_actividad
            INNER JOIN zoocriadero z ON z.id_zoocriadero = sz.id_zoocriadero
            INNER JOIN usuario u ON u.id_usuario = sz.id_usuario
            ORDER BY sz.fecha";

    $resultado = pg_query($conexion, $sql);

    if (!$resultado) {
        die("Error en la consulta: " . pg_last_error($conexion));
    }

    // ---------------------------------------------------------
    // 3. GUARDAMOS CADA FILA DENTRO DEL ARREGLO $actividades
    // ---------------------------------------------------------
    $actividades = [];
    while ($fila = pg_fetch_assoc($resultado)) {
        $actividades[] = $fila;
    }

    pg_close($conexion);

    // --- LEEMOS LO QUE EL USUARIO ENVIÓ EN LOS FILTROS ---
    $filtroZoocriadero = $_GET['zoocriadero']  ?? '';
    $filtroActividad   = $_GET['actividad']    ?? '';
    $filtroFechaInicio = $_GET['fecha_inicio'] ?? '';
    $filtroFechaFin    = $_GET['fecha_fin']    ?? '';

    // --- VALORES ÚNICOS PARA LLENAR LOS SELECT ---
    $listaZoocriaderos = array_unique(array_column($actividades, 'zoocriadero'));
    $listaActividades  = array_unique(array_column($actividades, 'actividad'));

    // --- FILTRAMOS EL ARREGLO SEGÚN LO QUE HAYA ELEGIDO EL USUARIO ---
    $actividadesFiltradas = [];

    foreach ($actividades as $actividad) {
        $fechaInicioActividad = DateTime::createFromFormat('d/m/Y', $actividad['inicio']);
        $fechaFinActividad    = DateTime::createFromFormat('d/m/Y', $actividad['fin']);

        $cumpleZoocriadero = ($filtroZoocriadero === '' || $actividad['zoocriadero'] === $filtroZoocriadero);
        $cumpleActividad   = ($filtroActividad === '' || $actividad['actividad'] === $filtroActividad);

        $cumpleFechaInicio = true;
        if ($filtroFechaInicio !== '') {
            $fechaDesde = DateTime::createFromFormat('Y-m-d', $filtroFechaInicio);
            $cumpleFechaInicio = ($fechaInicioActividad >= $fechaDesde);
        }

        $cumpleFechaFin = true;
        if ($filtroFechaFin !== '') {
            $fechaHasta = DateTime::createFromFormat('Y-m-d', $filtroFechaFin);
            $cumpleFechaFin = ($fechaFinActividad <= $fechaHasta);
        }

        if ($cumpleZoocriadero && $cumpleActividad && $cumpleFechaInicio && $cumpleFechaFin) {
            $actividadesFiltradas[] = $actividad;
        }
    }

    // --- CONTAMOS CUÁNTAS HAY DE CADA ESTADO (sobre lo ya filtrado) ---
    $totalActividades = count($actividadesFiltradas);
    $totalCompletas = 0;
    $totalEnProgreso = 0;
    $totalRetrasadas = 0;

    foreach ($actividadesFiltradas as $actividad) {
        if ($actividad['estado'] === 'Completada') {
            $totalCompletas++;
        } elseif ($actividad['estado'] === 'En progreso') {
            $totalEnProgreso++;
        } elseif ($actividad['estado'] === 'Retrasada') {
            $totalRetrasadas++;
        }
    }

    // --- LE ENTREGAMOS TODO A LA VISTA EN UN SOLO ARREGLO ---
    return [
        'actividadesFiltradas' => $actividadesFiltradas,
        'listaZoocriaderos'    => $listaZoocriaderos,
        'listaActividades'     => $listaActividades,
        'filtroZoocriadero'    => $filtroZoocriadero,
        'filtroActividad'      => $filtroActividad,
        'filtroFechaInicio'    => $filtroFechaInicio,
        'filtroFechaFin'       => $filtroFechaFin,
        'totalActividades'     => $totalActividades,
        'totalCompletas'       => $totalCompletas,
        'totalEnProgreso'      => $totalEnProgreso,
        'totalRetrasadas'      => $totalRetrasadas,
    ];
}