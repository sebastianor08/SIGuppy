<?php

include_once '../Model/Dashboard/DashboardModel.php';

class DashboardController
{
    public function resumen()
    {
        $obj = new DashboardModel();

        jsonResponse([
            'ok' => true,
            'data' => [
                'zoocriaderos_activos'      => $obj->contarZoocriaderosActivos(),
                'seguimientos_mes'          => $obj->contarSeguimientosEsteMes(),
                'depositos_inspeccionados'  => $obj->contarDepositosInspeccionados(),
                'recientes'                 => $obj->zoocriaderosConSeguimientoReciente(4),
            ],
        ]);
    }
}