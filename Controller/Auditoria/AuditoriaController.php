<?php

require_once __DIR__ . '/../../Model/Auditoria/AuditoriaModel.php';

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

    return [
        'filtros'                => $filtros,
        'movimientosGeneral'     => $model->listarGeneral($filtros),
        'movimientosSeguimiento' => $model->listarSeguimientoZoocriadero($filtros),
        'modulosDisponibles'     => $model->modulosDisponibles(),
        'usuariosDisponibles'    => $model->usuariosDisponibles(),
    ];
}