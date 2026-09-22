<?php

include_once __DIR__ . '/../MasterModel.php';
class DashboardModel extends MasterModel
{
    public function contarZoocriaderosActivos()
    {
        return (int) $this->selectValue(
            "SELECT COUNT(*) FROM zoocriadero WHERE estado = 1"
        );
    }

    // Seguimientos de zoocriadero registrados en el mes en curso
    // (del 1 del mes actual al 1 del mes siguiente, con la fecha del
    // servidor como referencia).
    public function contarSeguimientosEsteMes()
    {
        return (int) $this->selectValue(
            "SELECT COUNT(*) FROM seguimiento_zoocriadero
             WHERE fecha >= date_trunc('month', CURRENT_DATE)::date
               AND fecha <  (date_trunc('month', CURRENT_DATE) + INTERVAL '1 month')::date"
        );
    }

    // Total histórico de inspecciones de depósitos/sitios registradas: cada fila
    // activa de seguimiento_terreno (visita a un sitio) más cada fila activa de
    // seguimiento_deposito (visita a un depósito, módulo "Seguimiento de Depósito").
    // Son dos consultas separadas para que, si la migración de seguimiento_deposito
    // aún no se ha corrido, el conteo de seguimiento_terreno no se pierda.
    public function contarDepositosInspeccionados()
    {
        $porSitio = (int) $this->selectValue(
            "SELECT COUNT(*) FROM seguimiento_terreno WHERE estado = 1"
        );

        $porDeposito = (int) $this->selectValue(
            "SELECT COUNT(*) FROM seguimiento_deposito WHERE estado = 1"
        );

        return $porSitio + $porDeposito;
    }

    // Los zoocriaderos con el seguimiento más reciente, para la tabla
    // "Zoocriaderos con seguimiento reciente" del Resumen.
    public function zoocriaderosConSeguimientoReciente($limite = 4)
    {
        return $this->selectAll(
            "SELECT z.id_zoocriadero, z.nombre, z.comuna, z.barrio, z.estado,
                    ult.ultima_fecha
             FROM zoocriadero z
             INNER JOIN (
                 SELECT id_zoocriadero, MAX(fecha) AS ultima_fecha
                 FROM seguimiento_zoocriadero
                 GROUP BY id_zoocriadero
             ) ult ON ult.id_zoocriadero = z.id_zoocriadero
             ORDER BY ult.ultima_fecha DESC
             LIMIT $1",
            [$limite]
        );
    }
}