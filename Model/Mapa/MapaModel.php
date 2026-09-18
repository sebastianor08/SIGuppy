<?php

include_once __DIR__ . '/../MasterModel.php';

class MapaModel extends MasterModel
{
    public function zoocriaderos()
    {
        return $this->selectAll(
            "SELECT id_zoocriadero AS id, nombre, direccion, comuna, barrio, latitud, longitud
             FROM zoocriadero
             WHERE estado = 1 AND latitud IS NOT NULL AND longitud IS NOT NULL
               AND latitud <> 0 AND longitud <> 0"
        );
    }

    public function depositos()
    {
        return $this->selectAll(
            "SELECT s.id_sitio AS id, td.nombre AS tipo_deposito, s.direccion, s.comuna, s.barrio,
                    s.latitud, s.longitud
             FROM sitio s
             INNER JOIN tipo_deposito td ON td.id_tipo_deposito = s.id_tipo_deposito
             WHERE s.estado = 1 AND s.latitud IS NOT NULL AND s.longitud IS NOT NULL"
        );
    }
}