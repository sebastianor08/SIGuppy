<?php

function obtenerDatosSeguimientoDeActividades()
{
    // Esto luego se va a reemplazar por el resultado de una consulta a la BD
    $actividades = [
        ['actividad' => 'Limpieza de tanques',   'zoocriadero' => 'Selva Viva', 'inicio' => '01/05/2024', 'fin' => '03/05/2024', 'responsable' => 'Juan R.',  'estado' => 'Completado'],
        ['actividad' => 'Alimentación peces',    'zoocriadero' => 'Selva Viva', 'inicio' => '02/05/2024', 'fin' => '02/05/2024', 'responsable' => 'Ana S.',   'estado' => 'Completado'],
        ['actividad' => 'Chequeo parámetros',    'zoocriadero' => 'Acuarama',   'inicio' => '05/05/2024', 'fin' => '06/05/2024', 'responsable' => 'Dr. Ruiz', 'estado' => 'En proceso'],
        ['actividad' => 'Mantenimiento filtros', 'zoocriadero' => 'Selva Viva', 'inicio' => '06/05/2024', 'fin' => '07/05/2024', 'responsable' => 'Dr. Ruiz', 'estado' => 'Retrasada'],
        ['actividad' => 'Limpieza de tanques',   'zoocriadero' => 'Acuarama',   'inicio' => '06/05/2024', 'fin' => '09/05/2024', 'responsable' => 'C. Pérez', 'estado' => 'Retrasada'],
    ];

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
        if ($actividad['estado'] === 'Completado') {
            $totalCompletas++;
        } elseif ($actividad['estado'] === 'En proceso') {
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