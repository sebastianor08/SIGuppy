<?php

function obtenerDatosSitiosPorDeposito()
{
    $sitios = [
        ['id' => 'ST-001', 'nombre' => 'La Primavera', 'zoocriadero' => 'Selva Viva', 'tipo' => 'Tanque de concreto', 'tanques' => 4, 'estado' => 'Activo', 'fecha' => '05/01/2024'],
        ['id' => 'ST-002', 'nombre' => 'El Paraíso', 'zoocriadero' => 'Acuarama', 'tipo' => 'Tanque de plástico', 'tanques' => 3, 'estado' => 'Activo', 'fecha' => '10/01/2024'],
        ['id' => 'ST-003', 'nombre' => 'Rio Claro', 'zoocriadero' => 'Rio Claro', 'tipo' => 'Estanque natural', 'tanques' => 2, 'estado' => 'Activo', 'fecha' => '15/01/2024'],
        ['id' => 'ST-004', 'nombre' => 'Los Mangos', 'zoocriadero' => 'El Bosque', 'tipo' => 'Otro', 'tanques' => 1, 'estado' => 'Inactivo', 'fecha' => '20/01/2024'],
        ['id' => 'ST-005', 'nombre' => 'La Esperanza', 'zoocriadero' => 'Selva Viva', 'tipo' => 'Tanque de concreto', 'tanques' => 5, 'estado' => 'Activo', 'fecha' => '22/01/2024'],
        ['id' => 'ST-006', 'nombre' => 'Las Palmas', 'zoocriadero' => 'Acuarama', 'tipo' => 'Tanque de plástico', 'tanques' => 2, 'estado' => 'Activo', 'fecha' => '25/01/2024'],
        ['id' => 'ST-007', 'nombre' => 'El Mirador', 'zoocriadero' => 'Agua Viva', 'tipo' => '', 'tanques' => 0, 'estado' => 'Inactivo', 'fecha' => '28/01/2024'],
        ['id' => 'ST-008', 'nombre' => 'Vista Hermosa', 'zoocriadero' => 'El Paraíso', 'tipo' => 'Estanque natural', 'tanques' => 3, 'estado' => 'Activo', 'fecha' => '02/02/2024'],
        ['id' => 'ST-009', 'nombre' => 'Puerto Nuevo', 'zoocriadero' => 'Rio Claro', 'tipo' => 'Tanque de concreto', 'tanques' => 3, 'estado' => 'Activo', 'fecha' => '05/02/2024'],
        ['id' => 'ST-010', 'nombre' => 'Buena Vista', 'zoocriadero' => 'El Bosque', 'tipo' => '', 'tanques' => 0, 'estado' => 'Inactivo', 'fecha' => '08/02/2024'],
        ['id' => 'ST-011', 'nombre' => 'San Isidro', 'zoocriadero' => 'Selva Viva', 'tipo' => 'Tanque de plástico', 'tanques' => 4, 'estado' => 'Activo', 'fecha' => '10/02/2024'],
        ['id' => 'ST-012', 'nombre' => 'Los Robles', 'zoocriadero' => 'Rio Claro', 'tipo' => 'Tanque de concreto', 'tanques' => 3, 'estado' => 'Activo', 'fecha' => '15/02/2024'],
    ];

    $filtroZoocriadero = $_GET['zoocriadero'] ?? '';
    $filtroEstado = $_GET['estado'] ?? '';
    $filtroTipo = $_GET['tipo_deposito'] ?? '';
    $filtroFechaInicio = $_GET['fecha_inicio'] ?? '';
    $filtroFechaFin = $_GET['fecha_fin'] ?? '';

    $listaZoocriaderos = array_unique(array_column($sitios, 'zoocriadero'));
    $listaEstados = ['Activo', 'Inactivo'];

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
        'segmentos' => $segmentos,
        'valorMaximoBarra' => $valorMaximoBarra,
        'sitiosPagina' => $sitiosPagina,
        'totalPaginas' => $totalPaginas,
        'paginaActual' => $paginaActual,
        'desde' => $desde,
        'hasta' => $hasta,
    ];
}
$registros = [
    ['deposito' => 'Depósito A', 'sitio' => 'Sitio 1'],
    ['deposito' => 'Depósito A', 'sitio' => 'Sitio 2'],
    ['deposito' => 'Depósito A', 'sitio' => 'Sitio 3'],
    ['deposito' => 'Depósito B', 'sitio' => 'Sitio 4'],
    ['deposito' => 'Depósito B', 'sitio' => 'Sitio 5'],
    ['deposito' => 'Depósito C', 'sitio' => 'Sitio 6'],
    ['deposito' => 'Depósito C', 'sitio' => 'Sitio 7'],
    ['deposito' => 'Depósito C', 'sitio' => 'Sitio 8'],
    ['deposito' => 'Depósito D', 'sitio' => 'Sitio 9'],
    ['deposito' => 'Depósito D', 'sitio' => 'Sitio 10'],
];

return $registros;
