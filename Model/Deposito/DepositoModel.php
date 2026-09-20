<?php

include_once __DIR__ . '/../MasterModel.php';

// Tabla principal: deposito
//   id_deposito bigint (identity), id_tipo_deposito bigint NOT NULL -> tipo_deposito,
//   id_sitio bigint NOT NULL -> sitio, descripcion varchar(200) NULL,
//   estado smallint NOT NULL DEFAULT 1
//
// Un depósito pertenece a un SITIO (que a su vez tiene su dirección) y es de
// un TIPO de depósito. La dirección NO se guarda en el depósito: se obtiene
// por deposito -> sitio -> direccion.
class DepositoModel extends MasterModel
{
    public function listar()
    {
        return $this->selectAll(
            "SELECT
                dep.id_deposito,
                dep.id_tipo_deposito,
                td.nombre AS tipo_deposito,
                dep.id_sitio,
                s.nombre AS sitio,
                dep.descripcion,
                dep.estado,
                d.direccion,
                b.nombre AS barrio,
                co.nombre AS comuna,
                ci.nombre AS ciudad
             FROM deposito dep
             INNER JOIN tipo_deposito td ON td.id_tipo_deposito = dep.id_tipo_deposito
             INNER JOIN sitio s ON s.id_sitio = dep.id_sitio
             INNER JOIN direccion d ON d.id_direccion = s.id_direccion
             INNER JOIN barrio b ON b.id_barrio = d.id_barrio
             INNER JOIN comuna co ON co.id_comuna = d.id_comuna
             INNER JOIN ciudad ci ON ci.id_ciudad = d.id_ciudad
             ORDER BY dep.id_deposito DESC"
        );
    }

    public function buscar($idDeposito)
    {
        return $this->selectOne(
            "SELECT id_deposito, id_tipo_deposito, id_sitio, descripcion, estado
             FROM deposito
             WHERE id_deposito = $1",
            [$idDeposito]
        );
    }

    // ---- Catálogos para los <select> del formulario ----
    // Se devuelven con su estado; el JS muestra solo los habilitados
    // (más el que ya tiene asignado el depósito que se está editando).

    public function tipos()
    {
        return $this->selectAll(
            "SELECT id_tipo_deposito, nombre, estado
             FROM tipo_deposito
             ORDER BY nombre"
        );
    }

    public function sitios()
    {
        return $this->selectAll(
            "SELECT
                s.id_sitio,
                s.nombre,
                s.estado,
                d.direccion,
                b.nombre AS barrio,
                ci.nombre AS ciudad
             FROM sitio s
             INNER JOIN direccion d ON d.id_direccion = s.id_direccion
             INNER JOIN barrio b ON b.id_barrio = d.id_barrio
             INNER JOIN ciudad ci ON ci.id_ciudad = d.id_ciudad
             ORDER BY s.nombre"
        );
    }

    // ---- Validaciones de llaves foráneas ----
    // Un tipo/sitio es válido si existe y está habilitado. Al editar se
    // acepta además el que el depósito ya tenía ($idActual), para poder
    // cambiar solo la descripción aunque ese tipo/sitio se haya inhabilitado.

    public function tipoDisponible($idTipo, $idActual = null)
    {
        return $this->selectOne(
            "SELECT 1
             FROM tipo_deposito
             WHERE id_tipo_deposito = $1
               AND (estado = 1 OR id_tipo_deposito = $2)",
            [$idTipo, $idActual]
        ) !== null;
    }

    public function sitioDisponible($idSitio, $idActual = null)
    {
        return $this->selectOne(
            "SELECT 1
             FROM sitio
             WHERE id_sitio = $1
               AND (estado = 1 OR id_sitio = $2)",
            [$idSitio, $idActual]
        ) !== null;
    }

    // ---- Escritura ----

    public function crear($datos)
    {
        // No se envía estado: la BD asigna 1 (activo) por DEFAULT.
        return $this->selectValue(
            "INSERT INTO deposito (id_tipo_deposito, id_sitio, descripcion)
             VALUES ($1, $2, $3)
             RETURNING id_deposito",
            [$datos['id_tipo_deposito'], $datos['id_sitio'], $datos['descripcion']]
        );
    }

    public function actualizar($idDeposito, $datos)
    {
        $resultado = $this->update(
            "UPDATE deposito
             SET id_tipo_deposito = $1,
                 id_sitio = $2,
                 descripcion = $3
             WHERE id_deposito = $4",
            [
                $datos['id_tipo_deposito'],
                $datos['id_sitio'],
                $datos['descripcion'],
                $idDeposito
            ]
        );

        return $resultado !== false;
    }

    public function cambiarEstado($idDeposito, $estado)
    {
        $resultado = $this->update(
            "UPDATE deposito
             SET estado = $1
             WHERE id_deposito = $2",
            [$estado, $idDeposito]
        );

        return $resultado !== false;
    }
}
