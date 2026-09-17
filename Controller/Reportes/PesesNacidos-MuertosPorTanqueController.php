<?php

function obtenerDatosPecesNacidosMuertosPorTanque()
{
    require __DIR__ . '/../../lib/conf/conf.php';

    $conexion = pg_connect("host=$host port=$port dbname=$database user=$user password=$password");

    if (!$conexion) {
        die("No se pudo conectar a la base de datos");
    }

    // La tabla seguimiento_zoocriadero guarda el desglose por sexo de
    // nacidos y muertos en cada visita, así que no hay que contar filas.
    $sql = "SELECT z.nombre AS zoocriadero,
                'Tanque ' || t.numero_tanque AS tanque,
                TO_CHAR(sz.fecha, 'DD/MM/YYYY') AS fecha,
                sz.numero_nacidos_hembra AS nacidos_hembra,
                sz.numero_nacidos_macho AS nacidos_macho,
                sz.numero_muertos_hembra AS muertos_hembra,
                sz.numero_muertos_macho AS muertos_macho
            FROM seguimiento_zoocriadero sz
            INNER JOIN tanque t ON t.id_tanque = sz.id_tanque
            INNER JOIN zoocriadero z ON z.id_zoocriadero = sz.id_zoocriadero
            ORDER BY z.nombre, t.numero_tanque, sz.fecha";

    $resultado = pg_query($conexion, $sql);

    if (!$resultado) {
        die("Error en la consulta: " . pg_last_error($conexion));
    }

    $registros = [];
    while ($fila = pg_fetch_assoc($resultado)) {
        $fila['nacidos_hembra'] = (int) $fila['nacidos_hembra'];
        $fila['nacidos_macho']  = (int) $fila['nacidos_macho'];
        $fila['muertos_hembra'] = (int) $fila['muertos_hembra'];
        $fila['muertos_macho']  = (int) $fila['muertos_macho'];
        $registros[] = $fila;
    }

    pg_close($conexion);

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

        if ($cumpleZoocriadero && $cumpleTanque && $cumpleFechaInicio && $cumpleFechaFin) {
            $registrosFiltrados[] = $registro;
        }
    }

    // --- SUMAMOS LOS REGISTROS FILTRADOS POR TANQUE, CON DESGLOSE POR SEXO ---
    // La clave incluye el zoocriadero porque el "Tanque 1" existe en varios.
    $resumenPorTanque = [];

    foreach ($registrosFiltrados as $registro) {
        $clave = $registro['zoocriadero'] . ' - ' . $registro['tanque'];

        if (!isset($resumenPorTanque[$clave])) {
            $resumenPorTanque[$clave] = [
                'tanque'         => $registro['tanque'],
                'zoocriadero'    => $registro['zoocriadero'],
                'nacidos_hembra' => 0,
                'nacidos_macho'  => 0,
                'muertos_hembra' => 0,
                'muertos_macho'  => 0,
            ];
        }

        $resumenPorTanque[$clave]['nacidos_hembra'] += $registro['nacidos_hembra'];
        $resumenPorTanque[$clave]['nacidos_macho']  += $registro['nacidos_macho'];
        $resumenPorTanque[$clave]['muertos_hembra'] += $registro['muertos_hembra'];
        $resumenPorTanque[$clave]['muertos_macho']  += $registro['muertos_macho'];
    }

    ksort($resumenPorTanque); // para que siempre salgan en el mismo orden

    // --- SEGÚN EL FILTRO DE SEXO, DECIDIMOS QUÉ "nacidos"/"muertos" MOSTRAR ---
    // 'Todos' -> hembra + macho (el total real). 'Hembra'/'Macho' -> solo ese sexo.
    foreach ($resumenPorTanque as $clave => $fila) {
        if ($filtroSexo === 'Hembra') {
            $resumenPorTanque[$clave]['nacidos'] = $fila['nacidos_hembra'];
            $resumenPorTanque[$clave]['muertos'] = $fila['muertos_hembra'];
        } elseif ($filtroSexo === 'Macho') {
            $resumenPorTanque[$clave]['nacidos'] = $fila['nacidos_macho'];
            $resumenPorTanque[$clave]['muertos'] = $fila['muertos_macho'];
        } else {
            $resumenPorTanque[$clave]['nacidos'] = $fila['nacidos_hembra'] + $fila['nacidos_macho'];
            $resumenPorTanque[$clave]['muertos'] = $fila['muertos_hembra'] + $fila['muertos_macho'];
        }
    }

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
