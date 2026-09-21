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

    // Un punto por DEPÓSITO. El tipo vive en la tabla deposito (ya no en sitio) y la
    // ubicación es la de la dirección de su sitio: deposito -> sitio -> direccion.
    // Varios depósitos de un mismo sitio comparten coordenadas.
    public function depositos()
    {
        return $this->selectAll(
            "SELECT dep.id_deposito AS id, td.nombre AS tipo_deposito, dep.descripcion,
                    s.nombre AS sitio,
                    d.direccion, c.nombre AS comuna, b.nombre AS barrio,
                    d.latitud, d.longitud
             FROM deposito dep
             INNER JOIN tipo_deposito td ON td.id_tipo_deposito = dep.id_tipo_deposito
             INNER JOIN sitio s          ON s.id_sitio = dep.id_sitio
             INNER JOIN direccion d      ON d.id_direccion = s.id_direccion
             LEFT JOIN comuna c          ON c.id_comuna = d.id_comuna
             LEFT JOIN barrio b          ON b.id_barrio = d.id_barrio
             WHERE dep.estado = 1 AND s.estado = 1
               AND d.latitud IS NOT NULL AND d.longitud IS NOT NULL
               AND d.latitud <> 0 AND d.longitud <> 0
             ORDER BY dep.id_deposito"
        );
    }
}