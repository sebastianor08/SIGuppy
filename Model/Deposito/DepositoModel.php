<?php

include_once __DIR__ . '/../MasterModel.php';

class DepositoModel extends MasterModel
{
    /*
     * La tabla "sitio" representa los depósitos de terreno.
     *
     * sitio
     *   -> tipo_deposito
     *   -> direccion
     *       -> barrio
     *       -> nomenclatura
     */
    public function listar()
    {
        return $this->selectAll(
            "SELECT
                s.id_sitio,
                s.id_tipo_deposito,
                td.nombre AS tipo_deposito,
                td.descripcion AS descripcion,
                s.id_direccion,
                d.direccion,
                COALESCE(b.nombre, '') AS barrio,
                s.estado,
                TO_CHAR(s.creado_en, 'YYYY-MM-DD') AS creado_en
             FROM sitio s
             INNER JOIN tipo_deposito td
                ON td.id_tipo_deposito = s.id_tipo_deposito
             INNER JOIN direccion d
                ON d.id_direccion = s.id_direccion
             LEFT JOIN barrio b
                ON b.id_barrio = d.id_barrio
             ORDER BY s.id_sitio DESC"
        );
    }

    public function buscar($idSitio)
    {
        return $this->selectOne(
            "SELECT *
             FROM sitio
             WHERE id_sitio = $1",
            [$idSitio]
        );
    }

    public function tiposActivos()
    {
        return $this->selectAll(
            "SELECT
                id_tipo_deposito,
                nombre,
                descripcion
             FROM tipo_deposito
             WHERE estado = 1
             ORDER BY nombre"
        );
    }

    /*
     * Las direcciones pertenecen a la tabla "direccion".
     * Se muestran con barrio y ciudad cuando existen.
     */
    public function direcciones()
    {
        return $this->selectAll(
            "SELECT
                d.id_direccion,
                d.direccion,
                COALESCE(b.nombre, '') AS barrio,
                COALESCE(c.nombre, '') AS ciudad
             FROM direccion d
             LEFT JOIN barrio b
                ON b.id_barrio = d.id_barrio
             LEFT JOIN ciudad c
                ON c.id_ciudad = d.id_ciudad
             ORDER BY d.direccion"
        );
    }

    public function tipoExiste($idTipo)
    {
        return $this->selectValue(
            "SELECT 1
             FROM tipo_deposito
             WHERE id_tipo_deposito = $1
               AND estado = 1",
            [$idTipo]
        ) !== null;
    }

    public function direccionExiste($idDireccion)
    {
        return $this->selectValue(
            "SELECT 1
             FROM direccion
             WHERE id_direccion = $1",
            [$idDireccion]
        ) !== null;
    }

    public function crear($datos)
    {
        return $this->selectValue(
            "INSERT INTO sitio
                (id_tipo_deposito, id_direccion, estado)
             VALUES
                ($1, $2, 1)
             RETURNING id_sitio",
            [
                $datos['id_tipo_deposito'],
                $datos['id_direccion']
            ]
        );
    }

    public function actualizar($idSitio, $datos)
    {
        return $this->update(
            "UPDATE sitio
             SET id_tipo_deposito = $1,
                 id_direccion = $2
             WHERE id_sitio = $3",
            [
                $datos['id_tipo_deposito'],
                $datos['id_direccion'],
                $idSitio
            ]
        );
    }

    public function cambiarEstado($idSitio, $estado)
    {
        return $this->update(
            "UPDATE sitio
             SET estado = $1
             WHERE id_sitio = $2",
            [$estado, $idSitio]
        );
    }
}
?>
