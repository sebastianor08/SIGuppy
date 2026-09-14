<?php

include_once __DIR__ . '/../MasterModel.php';


class AccionesModel extends MasterModel{

    const AMBITO = 'zoocriadero';

    public function listar(){
        return $this->selectAll(
            "SELECT id_actividad, nombre, descripcion, estado,
                    TO_CHAR(creado_en, 'YYYY-MM-DD HH24:MI') AS creado_en
             FROM actividad
             WHERE ambito = $1
             ORDER BY id_actividad",
            [self::AMBITO]
        );
    }

    public function buscar($idActividad){
        return $this->selectOne(
            "SELECT * FROM actividad WHERE id_actividad = $1 AND ambito = $2",
            [$idActividad, self::AMBITO]
        );
    }

    public function existeNombre($nombre, $idExcluir = null){
        if($idExcluir){
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

    public function crear($datos){
        return $this->selectValue(
            "INSERT INTO actividad (ambito, nombre, descripcion, estado, creado_en)
             VALUES ($1, $2, $3, $4, CURRENT_TIMESTAMP)
             RETURNING id_actividad",
            [self::AMBITO, $datos['nombre'], $datos['descripcion'], $datos['estado']]
        );
    }

    public function actualizar($idActividad, $datos){
        return $this->update(
            "UPDATE actividad
             SET nombre = $1, descripcion = $2, estado = $3
             WHERE id_actividad = $4 AND ambito = $5",
            [$datos['nombre'], $datos['descripcion'], $datos['estado'], $idActividad, self::AMBITO]
        );
    }


    public function cambiarEstado($idActividad, $estado){
        return $this->update(
            "UPDATE actividad SET estado = $1 WHERE id_actividad = $2 AND ambito = $3",
            [$estado, $idActividad, self::AMBITO]
        );
    }
}

?>