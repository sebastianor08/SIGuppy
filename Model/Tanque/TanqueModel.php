<?php

include_once __DIR__ . '/../MasterModel.php';

// ============================================================
// Modelo del módulo Tanques.
// Tablas: tanque, zoocriadero, tipo_tanque
//
// La CREACIÓN de tanques se sigue haciendo desde el módulo
// Zoocriadero (botón "Registrar Tanque"). Este módulo es para
// ver TODOS los tanques de TODOS los zoocriaderos en un solo
// listado, editarlos y habilitar/inhabilitarlos.
// ============================================================
class TanqueModel extends MasterModel
{

    // Listado general: todos los tanques con el nombre del
    // zoocriadero y del tipo de tanque ya resueltos.
    public function listar()
    {
        return $this->selectAll(
            "SELECT t.id_tanque, t.numero_tanque, t.estado,
                    t.id_zoocriadero, z.nombre AS zoocriadero,
                    t.id_tipo_tanque, tt.nombre AS tipo_tanque
             FROM tanque t
             INNER JOIN zoocriadero z  ON z.id_zoocriadero = t.id_zoocriadero
             INNER JOIN tipo_tanque tt ON tt.id_tipo_tanque = t.id_tipo_tanque
             ORDER BY z.nombre, t.numero_tanque"
        );
    }

    public function buscar($idTanque)
    {
        return $this->selectOne(
            "SELECT * FROM tanque WHERE id_tanque = $1",
            [$idTanque]
        );
    }

    // Para el select "Zoocriadero" del modal de edición
    public function zoocriaderosActivos()
    {
        return $this->selectAll(
            "SELECT id_zoocriadero, nombre FROM zoocriadero WHERE estado = 1 ORDER BY nombre"
        );
    }

    // Para el select "Tipo de tanque" del modal de edición
    public function tiposTanque()
    {
        return $this->selectAll(
            "SELECT id_tipo_tanque, nombre FROM tipo_tanque WHERE estado = 1 ORDER BY nombre"
        );
    }

    public function zoocriaderoExiste($idZoocriadero)
    {
        return $this->selectValue(
            "SELECT 1 FROM zoocriadero WHERE id_zoocriadero = $1 AND estado = 1",
            [$idZoocriadero]
        ) !== null;
    }

    public function tipoTanqueExiste($idTipoTanque)
    {
        return $this->selectValue(
            "SELECT 1 FROM tipo_tanque WHERE id_tipo_tanque = $1 AND estado = 1",
            [$idTipoTanque]
        ) !== null;
    }

    // $idExcluir: al editar, no debe chocar consigo mismo
    public function existeNumeroTanque($idZoocriadero, $numero, $idExcluir = null)
    {
        if ($idExcluir) {
            return $this->selectValue(
                "SELECT 1 FROM tanque
                 WHERE id_zoocriadero = $1 AND numero_tanque = $2 AND id_tanque <> $3",
                [$idZoocriadero, $numero, $idExcluir]
            ) !== null;
        }
        return $this->selectValue(
            "SELECT 1 FROM tanque WHERE id_zoocriadero = $1 AND numero_tanque = $2",
            [$idZoocriadero, $numero]
        ) !== null;
    }

    // ---------------- UPDATE ----------------
    public function actualizar($idTanque, $datos)
    {
        return $this->update(
            "UPDATE tanque
             SET id_zoocriadero = $1, id_tipo_tanque = $2, numero_tanque = $3
             WHERE id_tanque = $4",
            [
                $datos['id_zoocriadero'],
                $datos['id_tipo_tanque'],
                $datos['numero_tanque'],
                $idTanque,
            ]
        );
    }

    // Habilitar / inhabilitar (no se borra, se cambia el estado).
    // Un tanque inhabilitado no puede usarse en nuevos seguimientos
    // (ver SeguimientoZoocriaderoModel::tanquePerteneceAZoocriadero).
    public function cambiarEstado($idTanque, $estado)
    {
        return $this->update(
            "UPDATE tanque SET estado = $1 WHERE id_tanque = $2",
            [$estado, $idTanque]
        );
    }
}
