<?php

include_once __DIR__ . '/../MasterModel.php';

class ZoocriaderoModel extends MasterModel{

    public function listar(){
        return $this->selectAll(
            "SELECT z.id_zoocriadero,
                    z.nombre,
                    z.direccion,
                    z.comuna,
                    z.barrio,
                    z.latitud,
                    z.longitud,
                    z.estado,
                    TO_CHAR(z.creado_en, 'YYYY-MM-DD') AS creado_en,
                    (SELECT COUNT(*) FROM tanque t
                      WHERE t.id_zoocriadero = z.id_zoocriadero AND t.estado = 1) AS total_tanques
             FROM zoocriadero z
             ORDER BY z.nombre"
        );
    }

    public function buscar($idZoocriadero){
        return $this->selectOne(
            "SELECT * FROM zoocriadero WHERE id_zoocriadero = $1",
            [$idZoocriadero]
        );
    }

    // Comunas para el primer select
    public function comunas(){
        return $this->selectAll(
            "SELECT id_comuna, nombre FROM comuna ORDER BY id_comuna"
        );
    }

    // Barrios de una comuna (el segundo select depende del primero)
    public function barriosDe($idComuna){
        return $this->selectAll(
            "SELECT id_barrio, nombre
             FROM barrio
             WHERE id_comuna = $1
             ORDER BY nombre",
            [$idComuna]
        );
    }

    // Valida que el barrio realmente pertenezca a esa comuna
    public function barrioPerteneceAComuna($nombreBarrio, $nombreComuna){
        return $this->selectValue(
            "SELECT 1
             FROM barrio b
             INNER JOIN comuna c ON c.id_comuna = b.id_comuna
             WHERE b.nombre = $1 AND c.nombre = $2",
            [$nombreBarrio, $nombreComuna]
        ) !== null;
    }

    public function tiposTanque(){
        return $this->selectAll(
            "SELECT id_tipo_tanque, nombre
             FROM tipo_tanque
             WHERE estado = 1
             ORDER BY nombre"
        );
    }

    // Tanques de un zoocriadero (para el modal de detalle)
    public function tanquesDe($idZoocriadero){
        return $this->selectAll(
            "SELECT t.id_tanque, t.nombre_tanque, t.estado,
                    tt.nombre AS tipo_tanque
             FROM tanque t
             INNER JOIN tipo_tanque tt ON tt.id_tipo_tanque = t.id_tipo_tanque
             WHERE t.id_zoocriadero = $1
             ORDER BY t.nombre_tanque",
            [$idZoocriadero]
        );
    }

    // ---------------- INSERT ----------------
    public function crear($datos){
        return $this->selectValue(
            "INSERT INTO zoocriadero
             (nombre, direccion, comuna, barrio, latitud, longitud, estado)
             VALUES ($1, $2, $3, $4, $5, $6, 1)
             RETURNING id_zoocriadero",
            [
                $datos['nombre'],
                $datos['direccion'],
                $datos['comuna'],
                $datos['barrio'],
                $datos['latitud'],
                $datos['longitud'],
            ]
        );
    }

    // ---------------- UPDATE ----------------
    public function actualizar($idZoocriadero, $datos){
        return $this->update(
            "UPDATE zoocriadero
             SET nombre = $1, direccion = $2, comuna = $3, barrio = $4,
                 latitud = $5, longitud = $6
             WHERE id_zoocriadero = $7",
            [
                $datos['nombre'],
                $datos['direccion'],
                $datos['comuna'],
                $datos['barrio'],
                $datos['latitud'],
                $datos['longitud'],
                $idZoocriadero,
            ]
        );
    }

    // Habilitar / inhabilitar (no se borra, se cambia el estado)
    public function cambiarEstado($idZoocriadero, $estado){
        return $this->update(
            "UPDATE zoocriadero SET estado = $1 WHERE id_zoocriadero = $2",
            [$estado, $idZoocriadero]
        );
    }

    public function existeNombreTanque($idZoocriadero, $nombre){
        return $this->selectValue(
            "SELECT 1 FROM tanque WHERE id_zoocriadero = $1 AND LOWER(nombre_tanque) = LOWER($2)",
            [$idZoocriadero, $nombre]
        ) !== null;
    }

    public function tipoTanqueExiste($idTipoTanque){
        return $this->selectValue(
            "SELECT 1 FROM tipo_tanque WHERE id_tipo_tanque = $1 AND estado = 1",
            [$idTipoTanque]
        ) !== null;
    }
}