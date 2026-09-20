<?php

include_once __DIR__ . '/../MasterModel.php';

// Tabla: tipo_deposito
//   id_tipo_deposito bigint (identity), nombre varchar(60) NOT NULL,
//   descripcion varchar(200) NULL, estado smallint NOT NULL DEFAULT 1
class TipoDepositoModel extends MasterModel
{
    public function listar()
    {
        return $this->selectAll(
            "SELECT id_tipo_deposito, nombre, descripcion, estado
             FROM tipo_deposito
             ORDER BY id_tipo_deposito"
        );
    }

    public function buscar($idTipoDeposito)
    {
        return $this->selectOne(
            "SELECT id_tipo_deposito, nombre, descripcion, estado
             FROM tipo_deposito
             WHERE id_tipo_deposito = $1",
            [$idTipoDeposito]
        );
    }

    public function existeNombre($nombre, $idExcluir = null)
    {
        if ($idExcluir) {
            return $this->selectValue(
                "SELECT 1 FROM tipo_deposito
                 WHERE LOWER(nombre) = LOWER($1) AND id_tipo_deposito <> $2",
                [$nombre, $idExcluir]
            ) !== null;
        }

        return $this->selectValue(
            "SELECT 1 FROM tipo_deposito WHERE LOWER(nombre) = LOWER($1)",
            [$nombre]
        ) !== null;
    }

    public function crear($datos)
    {
        // No se envía estado: la BD asigna 1 (activo) por DEFAULT.
        return $this->selectValue(
            "INSERT INTO tipo_deposito (nombre, descripcion)
             VALUES ($1, $2)
             RETURNING id_tipo_deposito",
            [$datos['nombre'], $datos['descripcion']]
        );
    }

    public function actualizar($idTipoDeposito, $datos)
    {
        $resultado = $this->update(
            "UPDATE tipo_deposito
             SET nombre = $1,
                 descripcion = $2
             WHERE id_tipo_deposito = $3",
            [$datos['nombre'], $datos['descripcion'], $idTipoDeposito]
        );

        return $resultado !== false;
    }

    public function cambiarEstado($idTipoDeposito, $estado)
    {
        $resultado = $this->update(
            "UPDATE tipo_deposito
             SET estado = $1
             WHERE id_tipo_deposito = $2",
            [$estado, $idTipoDeposito]
        );

        return $resultado !== false;
    }
}
