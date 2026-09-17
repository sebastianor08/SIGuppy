<?php

include_once __DIR__ . '/../MasterModel.php';

class DepositoModel extends MasterModel
{
    public function listar()
    {
        return $this->selectAll(
            "SELECT s.id_sitio, s.id_tipo_deposito, td.nombre AS tipo_deposito,
                    td.descripcion AS descripcion, s.direccion, s.comuna, s.barrio,
                    s.latitud, s.longitud, s.estado,
                    TO_CHAR(s.creado_en, 'YYYY-MM-DD') AS creado_en
             FROM sitio s
             INNER JOIN tipo_deposito td ON td.id_tipo_deposito = s.id_tipo_deposito
             ORDER BY s.id_sitio DESC"
        );
    }

    public function buscar($idSitio)
    {
        return $this->selectOne("SELECT * FROM sitio WHERE id_sitio = $1", [$idSitio]);
    }

    public function tiposActivos()
    {
        return $this->selectAll(
            "SELECT id_tipo_deposito, nombre, descripcion
             FROM tipo_deposito WHERE estado = 1 ORDER BY nombre"
        );
    }

    public function comunas()
    {
        return $this->selectAll("SELECT id_comuna, nombre FROM comuna ORDER BY id_comuna");
    }

    public function barriosDe($idComuna)
    {
        return $this->selectAll(
            "SELECT id_barrio, nombre FROM barrio WHERE id_comuna = $1 ORDER BY nombre",
            [$idComuna]
        );
    }

    public function barrioPerteneceAComuna($nombreBarrio, $nombreComuna)
    {
        return $this->selectValue(
            "SELECT 1 FROM barrio b
             INNER JOIN comuna c ON c.id_comuna = b.id_comuna
             WHERE b.nombre = $1 AND c.nombre = $2",
            [$nombreBarrio, $nombreComuna]
        ) !== null;
    }

    public function tipoExiste($idTipo)
    {
        return $this->selectValue(
            "SELECT 1 FROM tipo_deposito WHERE id_tipo_deposito = $1 AND estado = 1",
            [$idTipo]
        ) !== null;
    }

    public function crear($datos)
    {
        return $this->selectValue(
            "INSERT INTO sitio (id_tipo_deposito, direccion, comuna, barrio, latitud, longitud, estado)
             VALUES ($1, $2, $3, $4, $5, $6, 1)
             RETURNING id_sitio",
            [
                $datos['id_tipo_deposito'],
                $datos['direccion'],
                $datos['comuna'],
                $datos['barrio'],
                $datos['latitud'],
                $datos['longitud'],
            ]
        );
    }

    public function actualizar($idSitio, $datos)
    {
        return $this->update(
            "UPDATE sitio
             SET id_tipo_deposito = $1, direccion = $2, comuna = $3, barrio = $4,
                 latitud = $5, longitud = $6
             WHERE id_sitio = $7",
            [
                $datos['id_tipo_deposito'],
                $datos['direccion'],
                $datos['comuna'],
                $datos['barrio'],
                $datos['latitud'],
                $datos['longitud'],
                $idSitio,
            ]
        );
    }

    public function cambiarEstado($idSitio, $estado)
    {
        return $this->update("UPDATE sitio SET estado = $1 WHERE id_sitio = $2", [$estado, $idSitio]);
    }
}