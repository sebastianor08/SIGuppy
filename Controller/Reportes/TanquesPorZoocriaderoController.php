<?php

function obtenerDatosTanquesPorZoocriadero()
{
    require __DIR__ . '/../../lib/conf/conf.php';

    $conexion = pg_connect("host=localhost dbname=DB_Dengue_SIGuppy user=postgres password=Liliannys2008");

    if (!$conexion) {
        die("No se pudo conectar a la base de datos");
    }

    // ---------------------------------------------------------
    // 2. CONTAMOS LOS TANQUES AGRUPADOS POR ZOOCRIADERO Y POR TIPO
    // ---------------------------------------------------------
    // El encargado sale de zoocriadero.id_persona_cargo.
    // Se usa LEFT JOIN porque ese campo puede venir vacío.
    $sql = "SELECT z.nombre AS zoocriadero,
                tt.nombre AS tipo,
                COUNT(t.id_tanque) AS cantidad,
                COALESCE(u.nombre || ' ' || u.apellido, 'Sin asignar') AS encargado,
                CASE WHEN z.estado = 1 THEN 'Activo' ELSE 'Inactivo' END AS estado
            FROM tanque t
            INNER JOIN zoocriadero z ON z.id_zoocriadero = t.id_zoocriadero
            INNER JOIN tipo_tanque tt ON tt.id_tipo_tanque = t.id_tipo_tanque
            LEFT JOIN usuario u ON u.id_usuario = z.id_persona_cargo
            GROUP BY z.nombre, tt.nombre, u.nombre, u.apellido, z.estado
            ORDER BY z.nombre, tt.nombre";

    $resultado = pg_query($conexion, $sql);

    if (!$resultado) {
        die("Error en la consulta: " . pg_last_error($conexion));
    }

    // ---------------------------------------------------------
    // 3. GUARDAMOS CADA FILA DENTRO DEL ARREGLO $registros
    // ---------------------------------------------------------
    $registros = [];
    while ($fila = pg_fetch_assoc($resultado)) {
        // PostgreSQL devuelve los números como texto, los pasamos a número
        $fila['cantidad']  = (int) $fila['cantidad'];
        $fila['capacidad'] = (int) $fila['capacidad'];
        $registros[] = $fila;
    }

    pg_close($conexion);

    // --- LEEMOS LOS FILTROS ---
    $filtroZoocriadero = $_GET['zoocriadero'] ?? '';
    $filtroTipo        = $_GET['tanque']      ?? ''; // el select "Tanque" filtra por tipo de tanque

    // --- VALORES ÚNICOS PARA LOS SELECT ---
    $listaZoocriaderos = array_unique(array_column($registros, 'zoocriadero'));
    $listaTipos        = array_unique(array_column($registros, 'tipo'));

    // --- FILTRAMOS ---
    $registrosFiltrados = [];
    foreach ($registros as $registro) {
        $cumpleZoocriadero = ($filtroZoocriadero === '' || $registro['zoocriadero'] === $filtroZoocriadero);
        $cumpleTipo        = ($filtroTipo === '' || $registro['tipo'] === $filtroTipo);

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
