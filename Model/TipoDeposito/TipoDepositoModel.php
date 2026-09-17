<?php

include_once __DIR__ . '/../MasterModel.php';

// ============================================================
// Modelo del módulo Tipo Depósito.
// Tabla: tipo_deposito (id_tipo_deposito, nombre, descripcion, estado)
// ============================================================
class TipoDepositoModel extends MasterModel
{
    // NOTA: la tabla tipo_deposito no tiene columna creado_en (nunca la tuvo).
    // La consulta anterior la pedía igual, así que fallaba en silencio
    // (MasterModel::selectAll atrapa el error de SQL y devuelve []),
    // y por eso el listado siempre aparecía vacío.
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
            "SELECT * FROM tipo_deposito WHERE id_tipo_deposito = $1",
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
        return $this->selectValue(
            "INSERT INTO tipo_deposito (nombre, descripcion, estado)
             VALUES ($1, $2, $3)
             RETURNING id_tipo_deposito",
            [$datos['nombre'], $datos['descripcion'], $datos['estado']]
        );
    }

    public function actualizar($idTipoDeposito, $datos)
    {
        return $this->update(
            "UPDATE tipo_deposito
             SET nombre = $1, descripcion = $2, estado = $3
             WHERE id_tipo_deposito = $4",
            [$datos['nombre'], $datos['descripcion'], $datos['estado'], $idTipoDeposito]
        );
    }

    public function cambiarEstado($idTipoDeposito, $estado)
    {
        return $this->update(
            "UPDATE tipo_deposito SET estado = $1 WHERE id_tipo_deposito = $2",
            [$estado, $idTipoDeposito]
        );
    }
}
