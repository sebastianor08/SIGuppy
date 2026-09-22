<?php

function generarPuntosLinea($valores, $valorMaximo, $anchoGrafico, $altoGrafico, $margenIzquierdo)
{
    $cantidadPuntos = count($valores);

    // Si hay 1 punto o ninguno no se puede dividir, ponemos 0 para no romper la página
    if ($cantidadPuntos > 1) {
        $espacioEntrePuntos = $anchoGrafico / ($cantidadPuntos - 1);
    } else {
        $espacioEntrePuntos = 0;
    }

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
    
    // NOS CONECTAMOS A LA BASE DE DATOS
    // ---------------------------------------------------------
    require __DIR__ . '/../../lib/conf/conf.php';

    $conexion = pg_connect("host=$host port=$port dbname=$database user=$user password=$password");

    if (!$conexion) {
        die("No se pudo conectar a la base de datos");
    }

    // ---------------------------------------------------------
    // TRAEMOS LAS ACTIVIDADES DE TERRENO
    // ---------------------------------------------------------
    // las actividades de terreno NO pertenecen a un zoocriadero,
    // se hacen en sitios que están en una comuna. Por eso la variable
    // $listaZoocriaderos aquí trae COMUNAS (el nombre se dejó igual
    // para no tener que cambiar toda la vista).
    // st.estado es un número (1/2/3) en la base de datos; aquí se convierte a
    // texto porque el resto del reporte compara contra 'Completada', etc.
    $sql = "SELECT a.nombre AS tipo,
                c.nombre AS zoocriadero,
                TO_CHAR(st.fecha, 'DD/MM/YYYY') AS fecha,
                CASE st.estado
                    WHEN 1 THEN 'Completada'
                    WHEN 2 THEN 'En progreso'
                    WHEN 3 THEN 'Retrasada'
                    ELSE 'Completada'
                END AS estado
            FROM seguimiento_terreno st
            INNER JOIN actividad_terreno act ON act.id_seguimiento_terreno = st.id_seguimiento_terreno
            INNER JOIN actividad a ON a.id_actividad = act.id_actividad
            INNER JOIN sitio s ON s.id_sitio = st.id_sitio
            INNER JOIN direccion d ON d.id_direccion = s.id_direccion
            INNER JOIN comuna c ON c.id_comuna = d.id_comuna
            WHERE a.ambito = 'terreno'
            ORDER BY st.fecha";

    $resultado = pg_query($conexion, $sql);

    if (!$resultado) {
        die("Error en la consulta: " . pg_last_error($conexion));
    }

    // GUARDAMOS CADA FILA DENTRO DEL ARREGLO $actividades
    // ---------------------------------------------------------
    $actividades = [];
    while ($fila = pg_fetch_assoc($resultado)) {
        $actividades[] = $fila;
    }

    pg_close($conexion);

    // --- SACAMOS LOS TIPOS QUE DE VERDAD EXISTEN EN LA BASE DE DATOS ---
    $listaTipos = [];
    foreach ($actividades as $actividad) {
        if (!in_array($actividad['tipo'], $listaTipos)) {
            $listaTipos[] = $actividad['tipo'];
        }
    }

    // --- LE ASIGNAMOS UN COLOR A CADA TIPO, EN ORDEN ---
    $paletaColores = ['#2f5fdc', '#5bc9e8', '#6c5ce7', '#b8a8f5', '#21a666', '#e0952d'];

    $coloresPorTipo = [];
    $indiceColor = 0;
    foreach ($listaTipos as $tipo) {
        $coloresPorTipo[$tipo] = $paletaColores[$indiceColor % count($paletaColores)];
        $indiceColor++;
    }

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

    // --- ARMAMOS LA LISTA DE FECHAS QUE SÍ TIENEN ACTIVIDADES ---
    // Como la consulta ya viene ordenada por fecha, quedan en orden.
    $fechasEvolucion = [];
    foreach ($actividadesFiltradas as $actividad) {
        if (!in_array($actividad['fecha'], $fechasEvolucion)) {
            $fechasEvolucion[] = $actividad['fecha'];
        }
    }

    // --- CONTAMOS CUÁNTAS ACTIVIDADES HAY DE CADA TIPO EN CADA FECHA ---
    $valoresEvolucionPorTipo = [];
    $valorMaximoEvolucion = 1; // empieza en 1 para no dividir entre 0

    foreach ($listaTipos as $tipo) {
        $valores = [];

        foreach ($fechasEvolucion as $fecha) {
            $contador = 0;

            foreach ($actividadesFiltradas as $actividad) {
                if ($actividad['tipo'] === $tipo && $actividad['fecha'] === $fecha) {
                    $contador++;
                }
            }

            $valores[] = $contador;

            if ($contador > $valorMaximoEvolucion) {
                $valorMaximoEvolucion = $contador;
            }
        }

        $valoresEvolucionPorTipo[$tipo] = $valores;
    }

    $anchoGraficoEvolucion = 500;
    $altoGraficoEvolucion  = 160;
    $margenIzquierdoEvolucion = 40;

    // --- ETIQUETAS DEL EJE VERTICAL, SEGÚN EL MÁXIMO REAL ---
    // Antes el eje decía siempre "40, 30, 20, 10, 0" sin importar los datos.
    // Ahora se redondea el máximo real hacia arriba al múltiplo de 4 más
    // cercano (para repartirlo en 4 tramos iguales) y de ahí salen las 5
    // etiquetas, de arriba hacia abajo.
    $maximoEjeEvolucion = (int) (ceil($valorMaximoEvolucion / 4) * 4);
    $etiquetasEjeEvolucion = [];
    for ($i = 4; $i >= 0; $i--) {
        $etiquetasEjeEvolucion[] = (int) round($maximoEjeEvolucion * $i / 4);
    }

    $seriesEvolucion = [];
    foreach ($listaTipos as $tipo) {
        $seriesEvolucion[] = [
            'tipo'   => $tipo,
            'color'  => $coloresPorTipo[$tipo],
            'puntos' => generarPuntosLinea(
                $valoresEvolucionPorTipo[$tipo],
                $maximoEjeEvolucion,
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
        'etiquetasEjeEvolucion' => $etiquetasEjeEvolucion,
    ];
}
