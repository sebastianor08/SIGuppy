<?php

require_once __DIR__ . '/../../Model/Auditoria/AuditoriaModel.php';
require_once __DIR__ . '/../../lib/validaciones.php';

function obtenerDatosAuditoria()
{
    $model = new AuditoriaModel();

    $filtros = [
        'modulo'       => $_GET['id_modulo']    ?? '',
        'operacion'    => $_GET['operacion']    ?? '',
        'id_usuario'   => $_GET['usuario']      ?? '',
        'fecha_inicio' => $_GET['fecha_inicio'] ?? '',
        'fecha_fin'    => $_GET['fecha_fin']    ?? '',
    ];


    $errorRangoFechas = validarRangoFechas($filtros['fecha_inicio'], $filtros['fecha_fin'], 'Desde', 'Hasta');
    if ($errorRangoFechas !== null) {
        $filtros['fecha_inicio'] = '';
        $filtros['fecha_fin']    = '';
    }

    return [
        'filtros'                => $filtros,
        'errorRangoFechas'       => $errorRangoFechas,
        'movimientosGeneral'     => $model->listarGeneral($filtros),
        'movimientosSeguimiento' => $model->listarSeguimientoZoocriadero($filtros),
        'movimientosDeposito'    => $model->listarSeguimientoDeposito($filtros),
        'modulosDisponibles'     => $model->modulosDisponibles(),
        'usuariosDisponibles'    => $model->usuariosDisponibles(),
    ];
}