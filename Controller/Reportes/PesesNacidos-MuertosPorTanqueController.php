<?php

function obtenerDatosPecesNacidosMuertosPorTanque()
{
    // Esto luego se va a reemplazar por datos que vengan de la base de datos.
    $registros = [
        // Tanque T1 - Selva Viva
        ['zoocriadero' => 'Selva Viva', 'tanque' => 'T1', 'sexo' => 'Hembra', 'tipo' => 'Nacido', 'fecha' => '02/05/2024'],
        ['zoocriadero' => 'Selva Viva', 'tanque' => 'T1', 'sexo' => 'Macho',  'tipo' => 'Nacido', 'fecha' => '03/05/2024'],
        ['zoocriadero' => 'Selva Viva', 'tanque' => 'T1', 'sexo' => 'Hembra', 'tipo' => 'Nacido', 'fecha' => '10/05/2024'],
        ['zoocriadero' => 'Selva Viva', 'tanque' => 'T1', 'sexo' => 'Macho',  'tipo' => 'Muerto', 'fecha' => '12/05/2024'],
        ['zoocriadero' => 'Selva Viva', 'tanque' => 'T1', 'sexo' => 'Hembra', 'tipo' => 'Muerto', 'fecha' => '15/05/2024'],

        // Tanque T2 - Selva Viva
        ['zoocriadero' => 'Selva Viva', 'tanque' => 'T2', 'sexo' => 'Macho',  'tipo' => 'Nacido', 'fecha' => '04/05/2024'],
        ['zoocriadero' => 'Selva Viva', 'tanque' => 'T2', 'sexo' => 'Hembra', 'tipo' => 'Nacido', 'fecha' => '06/05/2024'],
        ['zoocriadero' => 'Selva Viva', 'tanque' => 'T2', 'sexo' => 'Macho',  'tipo' => 'Nacido', 'fecha' => '18/05/2024'],
        ['zoocriadero' => 'Selva Viva', 'tanque' => 'T2', 'sexo' => 'Hembra', 'tipo' => 'Muerto', 'fecha' => '20/05/2024'],

        // Tanque T3 - Acuarama
        ['zoocriadero' => 'Acuarama', 'tanque' => 'T3', 'sexo' => 'Hembra', 'tipo' => 'Nacido', 'fecha' => '05/05/2024'],
        ['zoocriadero' => 'Acuarama', 'tanque' => 'T3', 'sexo' => 'Macho',  'tipo' => 'Nacido', 'fecha' => '09/05/2024'],
        ['zoocriadero' => 'Acuarama', 'tanque' => 'T3', 'sexo' => 'Hembra', 'tipo' => 'Nacido', 'fecha' => '22/05/2024'],
        ['zoocriadero' => 'Acuarama', 'tanque' => 'T3', 'sexo' => 'Macho',  'tipo' => 'Muerto', 'fecha' => '25/05/2024'],

        // Tanque T4 - Acuarama
        ['zoocriadero' => 'Acuarama', 'tanque' => 'T4', 'sexo' => 'Macho',  'tipo' => 'Nacido', 'fecha' => '07/05/2024'],
        ['zoocriadero' => 'Acuarama', 'tanque' => 'T4', 'sexo' => 'Hembra', 'tipo' => 'Nacido', 'fecha' => '11/05/2024'],
        ['zoocriadero' => 'Acuarama', 'tanque' => 'T4', 'sexo' => 'Macho',  'tipo' => 'Muerto', 'fecha' => '28/05/2024'],
    ];

    // --- LEEMOS LOS FILTROS ---
    $filtroZoocriadero = $_GET['zoocriadero']  ?? '';
    $filtroTanque      = $_GET['tanque']       ?? '';
    $filtroSexo        = $_GET['sexo']         ?? '';
    $filtroFechaInicio = $_GET['fecha_inicio'] ?? '';
    $filtroFechaFin    = $_GET['fecha_fin']    ?? '';

    // --- VALORES ÚNICOS PARA LLENAR LOS SELECT ---
    $listaZoocriaderos = array_unique(array_column($registros, 'zoocriadero'));
    $listaTanques      = array_unique(array_column($registros, 'tanque'));

    // --- FILTRAMOS LOS REGISTROS ---
    $registrosFiltrados = [];

    foreach ($registros as $registro) {
        $fechaRegistro = DateTime::createFromFormat('d/m/Y', $registro['fecha']);

        $cumpleZoocriadero = ($filtroZoocriadero === '' || $registro['zoocriadero'] === $filtroZoocriadero);
        $cumpleTanque      = ($filtroTanque === '' || $registro['tanque'] === $filtroTanque);
        $cumpleSexo        = ($filtroSexo === '' || $registro['sexo'] === $filtroSexo);

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

        if ($cumpleZoocriadero && $cumpleTanque && $cumpleSexo && $cumpleFechaInicio && $cumpleFechaFin) {
            $registrosFiltrados[] = $registro;
        }
    }

    // --- AGRUPAMOS LOS REGISTROS FILTRADOS POR TANQUE ---
    $resumenPorTanque = [];

    foreach ($registrosFiltrados as $registro) {
        $tanque = $registro['tanque'];

        if (!isset($resumenPorTanque[$tanque])) {
            $resumenPorTanque[$tanque] = [
                'tanque'      => $tanque,
                'zoocriadero' => $registro['zoocriadero'],
                'nacidos'     => 0,
                'muertos'     => 0,
            ];
        }

        if ($registro['tipo'] === 'Nacido') {
            $resumenPorTanque[$tanque]['nacidos']++;
        } else {
            $resumenPorTanque[$tanque]['muertos']++;
        }
    }

    ksort($resumenPorTanque); // para que siempre salgan en orden T1, T2, T3...

    // --- TOTALES GENERALES ---
    $totalNacidos = 0;
    $totalMuertos = 0;

    foreach ($resumenPorTanque as $fila) {
        $totalNacidos += $fila['nacidos'];
        $totalMuertos += $fila['muertos'];
    }

    $totalGeneral = $totalNacidos + $totalMuertos;
    $tasaMortalidadGeneral = $totalGeneral > 0 ? ($totalMuertos / $totalGeneral) * 100 : 0;

    // --- VALOR MÁXIMO PARA DIBUJAR LAS BARRAS DEL GRÁFICO ---
    $valorMaximoGrafico = 1; // evita dividir entre 0
    foreach ($resumenPorTanque as $fila) {
        $valorMaximoGrafico = max($valorMaximoGrafico, $fila['nacidos'], $fila['muertos']);
    }

    // --- LE ENTREGAMOS TODO A LA VISTA EN UN SOLO ARREGLO ---
    return [
        'listaZoocriaderos'      => $listaZoocriaderos,
        'listaTanques'           => $listaTanques,
        'filtroZoocriadero'      => $filtroZoocriadero,
        'filtroTanque'           => $filtroTanque,
        'filtroSexo'             => $filtroSexo,
        'filtroFechaInicio'      => $filtroFechaInicio,
        'filtroFechaFin'         => $filtroFechaFin,
        'resumenPorTanque'       => $resumenPorTanque,
        'totalNacidos'           => $totalNacidos,
        'totalMuertos'           => $totalMuertos,
        'tasaMortalidadGeneral'  => $tasaMortalidadGeneral,
        'valorMaximoGrafico'     => $valorMaximoGrafico,
    ];
}