<?php

include_once __DIR__ . '/../MasterModel.php';

// Tabla: actividad
//   id_actividad bigint (identity), ambito varchar(20) NOT NULL,
//   nombre varchar(60) NOT NULL, descripcion varchar(200) NULL,
//   estado smallint NOT NULL DEFAULT 1
//
// La misma tabla la usa el módulo Acciones (ambito = 'zoocriadero').
// Este módulo trabaja SOLO con ambito = 'terreno'.
class ActividadModel extends MasterModel
{
    const AMBITO = 'terreno';

    public function listar()
    {
        return $this->selectAll(
            "SELECT id_actividad, nombre, descripcion, estado
             FROM actividad
             WHERE ambito = $1
             ORDER BY id_actividad",
            [self::AMBITO]
        );
    }

    public function buscar($idActividad)
    {
        return $this->selectOne(
            "SELECT id_actividad, nombre, descripcion, estado
             FROM actividad
             WHERE id_actividad = $1 AND ambito = $2",
            [$idActividad, self::AMBITO]
        );
    }

    public function existeNombre($nombre, $idExcluir = null)
    {
        if ($idExcluir) {
            return $this->selectValue(
                "SELECT 1 FROM actividad
                 WHERE ambito = $1 AND LOWER(nombre) = LOWER($2) AND id_actividad <> $3",
                [self::AMBITO, $nombre, $idExcluir]
            ) !== null;
        }

        return $this->selectValue(
            "SELECT 1 FROM actividad WHERE ambito = $1 AND LOWER(nombre) = LOWER($2)",
            [self::AMBITO, $nombre]
        ) !== null;
    }

    public function crear($datos)
    {
        // No se envía estado: la BD asigna 1 (activo) por DEFAULT.
        return $this->selectValue(
            "INSERT INTO actividad (ambito, nombre, descripcion)
             VALUES ($1, $2, $3)
             RETURNING id_actividad",
            [self::AMBITO, $datos['nombre'], $datos['descripcion']]
        );
    }

    public function actualizar($idActividad, $datos)
    {
        $resultado = $this->update(
            "UPDATE actividad
             SET nombre = $1,
                 descripcion = $2
             WHERE id_actividad = $3 AND ambito = $4",
            [$datos['nombre'], $datos['descripcion'], $idActividad, self::AMBITO]
        );

        return $resultado !== false;
    }

    public function cambiarEstado($idActividad, $estado)
    {
        $resultado = $this->update(
            "UPDATE actividad
             SET estado = $1
             WHERE id_actividad = $2 AND ambito = $3",
            [$estado, $idActividad, self::AMBITO]
        );

        return $resultado !== false;
    }
}
