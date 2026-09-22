<?php

require_once __DIR__ . '/ComparativaMensual.php';

function obtenerDatosSitiosPorDeposito()
{
    
    // AQUI CONECTAMOS A LA BASE DE DATOS
    require __DIR__ . '/../../lib/conf/conf.php';

$conexion = pg_connect("host=$host port=$port dbname=$database user=$user password=$password");

    if (!$conexion) {
        die("No se pudo conectar a la base de datos");
    }

    // TRAEMOS LOS SITIOS CON SU TIPO DE DEPÓSITO
    $sql = "SELECT 'ST-' || LPAD(s.id_sitio::text, 3, '0') AS id,
                d.direccion AS nombre,
                b.nombre AS zoocriadero,
                td.nombre AS tipo,
                COUNT(st.id_seguimiento_terreno) AS tanques,
                CASE WHEN s.estado = 1 THEN 'Activo' ELSE 'Inactivo' END AS estado,
                TO_CHAR(s.creado_en, 'DD/MM/YYYY') AS fecha
            FROM sitio s
            INNER JOIN tipo_deposito td ON td.id_tipo_deposito = s.id_tipo_deposito
            INNER JOIN direccion d ON d.id_direccion = s.id_direccion
            INNER JOIN barrio b ON b.id_barrio = d.id_barrio
            LEFT JOIN seguimiento_terreno st ON st.id_sitio = s.id_sitio
            GROUP BY s.id_sitio, d.direccion, b.nombre, td.nombre, s.estado, s.creado_en
            ORDER BY s.id_sitio";

    $resultado = pg_query($conexion, $sql);

    if (!$resultado) {
        die("Error en la consulta: " . pg_last_error($conexion));
    }

    //  GUARDAMOS CADA FILA DENTRO DEL ARREGLO $sitios
    $sitios = [];
    while ($fila = pg_fetch_assoc($resultado)) {
        $fila['tanques'] = (int) $fila['tanques'];
        $sitios[] = $fila;
    }

    pg_close($conexion);

    $filtroZoocriadero = $_GET['zoocriadero'] ?? '';
    $filtroEstado = $_GET['estado'] ?? '';
    $filtroTipo = $_GET['tipo_deposito'] ?? '';
    $filtroFechaInicio = $_GET['fecha_inicio'] ?? '';
    $filtroFechaFin = $_GET['fecha_fin'] ?? '';

    $listaZoocriaderos = array_unique(array_column($sitios, 'zoocriadero'));

    $listaEstados = [];
    foreach ($sitios as $sitio) {
        if (!in_array($sitio['estado'], $listaEstados)) {
            $listaEstados[] = $sitio['estado'];
        }
    }

    $listaTipos = [];
    foreach ($sitios as $sitio) {
        if ($sitio['tipo'] !== '' && !in_array($sitio['tipo'], $listaTipos)) {
            $listaTipos[] = $sitio['tipo'];
        }
    }

    $sitiosFiltrados = [];

    foreach ($sitios as $sitio) {
        $fechaSitio = DateTime::createFromFormat('d/m/Y', $sitio['fecha']);

        $cumpleZoocriadero = ($filtroZoocriadero === '' || $sitio['zoocriadero'] === $filtroZoocriadero);
        $cumpleEstado = ($filtroEstado === '' || $sitio['estado'] === $filtroEstado);
        $cumpleTipo = ($filtroTipo === '' || $sitio['tipo'] === $filtroTipo);

        $cumpleFechaInicio = true;
        if ($filtroFechaInicio !== '') {
            $fechaDesde = DateTime::createFromFormat('Y-m-d', $filtroFechaInicio);
            $cumpleFechaInicio = ($fechaSitio >= $fechaDesde);
        }

        $cumpleFechaFin = true;
        if ($filtroFechaFin !== '') {
            $fechaHasta = DateTime::createFromFormat('Y-m-d', $filtroFechaFin);
            $cumpleFechaFin = ($fechaSitio <= $fechaHasta);
        }

        if ($cumpleZoocriadero && $cumpleEstado && $cumpleTipo && $cumpleFechaInicio && $cumpleFechaFin) {
            $sitiosFiltrados[] = $sitio;
        }
    }

    $totalSitios = count($sitiosFiltrados);
    $totalConDeposito = 0;
    $totalSinDeposito = 0;
    $cantidadPorTipo = [];

    foreach ($sitiosFiltrados as $sitio) {
        if ($sitio['tipo'] === '') {
            $totalSinDeposito++;
        } else {
            $totalConDeposito++;
            if (!isset($cantidadPorTipo[$sitio['tipo']])) {
                $cantidadPorTipo[$sitio['tipo']] = 0;
            }
            $cantidadPorTipo[$sitio['tipo']]++;
        }
    }

    $totalTiposDeposito = count($cantidadPorTipo);

    // --- COMPARATIVA VS EL MES ANTERIOR (respeta zoocriadero, estado y tipo; no depende de Fecha Inicio/Fin) ---
    // La fecha de un sitio es la de su creación.
    $meses = mesesComparativa($filtroFechaInicio);
    $actualSitios = 0;
    $actualConDeposito = 0;
    $actualSinDeposito = 0;
    $actualTipos = [];
    $anteriorSitios = 0;
    $anteriorConDeposito = 0;
    $anteriorSinDeposito = 0;
    $anteriorTipos = [];

    foreach ($sitios as $sitio) {
        $cumpleZoocriadero = ($filtroZoocriadero === '' || $sitio['zoocriadero'] === $filtroZoocriadero);
        $cumpleEstado = ($filtroEstado === '' || $sitio['estado'] === $filtroEstado);
        $cumpleTipo = ($filtroTipo === '' || $sitio['tipo'] === $filtroTipo);
        if (!$cumpleZoocriadero || !$cumpleEstado || !$cumpleTipo) {
            continue;
        }

        if (fechaEnMes($sitio['fecha'], $meses['actual'])) {
            $actualSitios++;
            if ($sitio['tipo'] === '') {
                $actualSinDeposito++;
            } else {
                $actualConDeposito++;
                $actualTipos[$sitio['tipo']] = true;
            }
        } elseif (fechaEnMes($sitio['fecha'], $meses['anterior'])) {
            $anteriorSitios++;
            if ($sitio['tipo'] === '') {
                $anteriorSinDeposito++;
            } else {
                $anteriorConDeposito++;
                $anteriorTipos[$sitio['tipo']] = true;
            }
        }
    }

    $comparativas = [
        'totalSitios'        => armarComparativa($actualSitios, $anteriorSitios, true),
        'totalTiposDeposito' => armarComparativa(count($actualTipos), count($anteriorTipos), true),
        'totalConDeposito'   => armarComparativa($actualConDeposito, $anteriorConDeposito, true),
        'totalSinDeposito'   => armarComparativa($actualSinDeposito, $anteriorSinDeposito, false),
    ];

    $paletaColores = ['#2f7dfa', '#3bc9db', '#7c6ee0', '#8bd8f0', '#21a666', '#e0952d'];

    $segmentos = [];
    $indiceColor = 0;
    $valorMaximoBarra = 1;

    foreach ($cantidadPorTipo as $tipo => $cantidad) {
        $porcentaje = $totalConDeposito > 0 ? ($cantidad / $totalConDeposito) * 100 : 0;
        $segmentos[] = [
            'tipo' => $tipo,
            'cantidad' => $cantidad,
            'porcentaje' => round($porcentaje, 1),
            'color' => $paletaColores[$indiceColor % count($paletaColores)],
        ];
        $indiceColor++;
        $valorMaximoBarra = max($valorMaximoBarra, $cantidad);
    }

    $porPagina = 4;
    $totalPaginas = max(1, (int) ceil($totalSitios / $porPagina));
    $paginaActual = (int) ($_GET['pagina'] ?? 1);

    if ($paginaActual < 1) {
        $paginaActual = 1;
    }
    if ($paginaActual > $totalPaginas) {
        $paginaActual = $totalPaginas;
    }

    $inicio = ($paginaActual - 1) * $porPagina;
    $sitiosPagina = array_slice($sitiosFiltrados, $inicio, $porPagina);

    $desde = $totalSitios > 0 ? $inicio + 1 : 0;
    $hasta = min($inicio + $porPagina, $totalSitios);

    return [
        'listaZoocriaderos' => $listaZoocriaderos,
        'listaEstados' => $listaEstados,
        'listaTipos' => $listaTipos,
        'filtroZoocriadero' => $filtroZoocriadero,
        'filtroEstado' => $filtroEstado,
        'filtroTipo' => $filtroTipo,
        'filtroFechaInicio' => $filtroFechaInicio,
        'filtroFechaFin' => $filtroFechaFin,
        'totalSitios' => $totalSitios,
        'totalConDeposito' => $totalConDeposito,
        'totalSinDeposito' => $totalSinDeposito,
        'totalTiposDeposito' => $totalTiposDeposito,
        'comparativas' => $comparativas,
        'segmentos' => $segmentos,
        'valorMaximoBarra' => $valorMaximoBarra,
        'sitiosPagina' => $sitiosPagina,
        'totalPaginas' => $totalPaginas,
        'paginaActual' => $paginaActual,
        'desde' => $desde,
        'hasta' => $hasta,
    ];
}
