<?php

include_once __DIR__ . '/../MasterModel.php';

// ============================================================
// Modelo del módulo Tanques.
// Tablas: tanque, zoocriadero, tipo_tanque
//
// La CREACIÓN de tanques (antes solo posible desde el módulo
// Zoocriadero) ahora se hace también desde este módulo, con su
// propio botón "Registrar Tanque". Este módulo sigue siendo además
// el lugar para ver TODOS los tanques de TODOS los zoocriaderos en
// un solo listado, editarlos y habilitar/inhabilitarlos.
// ============================================================
class TanqueModel extends MasterModel
{

    // Listado general: todos los tanques con el nombre del
    // zoocriadero y del tipo de tanque ya resueltos.
    public function listar()
    {
        return $this->selectAll(
            "SELECT t.id_tanque, t.nombre_tanque, t.estado,
                    t.id_zoocriadero, z.nombre AS zoocriadero,
                    t.id_tipo_tanque, tt.nombre AS tipo_tanque
             FROM tanque t
             INNER JOIN zoocriadero z  ON z.id_zoocriadero = t.id_zoocriadero
             INNER JOIN tipo_tanque tt ON tt.id_tipo_tanque = t.id_tipo_tanque
             ORDER BY z.nombre, t.nombre_tanque"
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

    // $idExcluir: al editar, no debe chocar consigo mismo. La comparación
    // es insensible a mayúsculas/minúsculas y a espacios de más, para que
    // "Tanque Norte" y "tanque   norte" cuenten como el mismo nombre.
    public function existeNombreTanque($idZoocriadero, $nombre, $idExcluir = null)
    {
        $nombreNormalizado = trim(preg_replace('/\s+/u', ' ', (string) $nombre));

        if ($idExcluir) {
            return $this->selectValue(
                "SELECT 1 FROM tanque
                 WHERE id_zoocriadero = $1 AND LOWER(TRIM(nombre_tanque)) = LOWER($2) AND id_tanque <> $3",
                [$idZoocriadero, $nombreNormalizado, $idExcluir]
            ) !== null;
        }
        return $this->selectValue(
            "SELECT 1 FROM tanque WHERE id_zoocriadero = $1 AND LOWER(TRIM(nombre_tanque)) = LOWER($2)",
            [$idZoocriadero, $nombreNormalizado]
        ) !== null;
    }

    // ---------------- CREATE ----------------
    public function crear($datos)
    {
        return $this->selectValue(
            "INSERT INTO tanque (id_zoocriadero, id_tipo_tanque, nombre_tanque, estado)
             VALUES ($1, $2, $3, 1)
             RETURNING id_tanque",
            [
                $datos['id_zoocriadero'],
                $datos['id_tipo_tanque'],
                $datos['nombre_tanque'],
            ]
        );
    }

    // ---------------- UPDATE ----------------
    public function actualizar($idTanque, $datos)
    {
        return $this->update(
            "UPDATE tanque
             SET id_zoocriadero = $1, id_tipo_tanque = $2, nombre_tanque = $3
             WHERE id_tanque = $4",
            [
                $datos['id_zoocriadero'],
                $datos['id_tipo_tanque'],
                $datos['nombre_tanque'],
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
