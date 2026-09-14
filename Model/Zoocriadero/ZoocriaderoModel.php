<?php

include_once __DIR__ . '/../MasterModel.php';

// ============================================================
// Modelo del módulo Zoocriaderos.
// Tablas: zoocriadero, tanque, tipo_tanque, usuario
// ============================================================
class ZoocriaderoModel extends MasterModel{

    // Listado para la tabla: trae el nombre de la persona a cargo
    // y cuántos tanques activos tiene cada zoocriadero.
    public function listar(){
        return $this->selectAll(
            "SELECT z.id_zoocriadero,
                    z.nombre,
                    z.direccion,
                    z.comuna,
                    z.barrio,
                    z.id_persona_cargo,
                    COALESCE(u.nombre || ' ' || u.apellido, 'Sin asignar') AS persona_cargo,
                    z.latitud,
                    z.longitud,
                    z.estado,
                    TO_CHAR(z.creado_en, 'YYYY-MM-DD') AS creado_en,
                    (SELECT COUNT(*) FROM tanque t
                      WHERE t.id_zoocriadero = z.id_zoocriadero AND t.estado = 1) AS total_tanques
             FROM zoocriadero z
             LEFT JOIN usuario u ON u.id_usuario = z.id_persona_cargo
             ORDER BY z.nombre"
        );
    }

    public function buscar($idZoocriadero){
        return $this->selectOne(
            "SELECT * FROM zoocriadero WHERE id_zoocriadero = $1",
            [$idZoocriadero]
        );
    }

    // Usuarios activos para el select "Persona a cargo"
    public function usuariosActivos(){
        return $this->selectAll(
            "SELECT u.id_usuario,
                    u.nombre || ' ' || u.apellido AS nombre_completo,
                    r.nombre_rol
             FROM usuario u
             INNER JOIN rol r ON r.id_rol = u.id_rol
             WHERE u.estado = 1
             ORDER BY u.nombre, u.apellido"
        );
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
            "SELECT t.id_tanque, t.numero_tanque, t.estado,
                    tt.nombre AS tipo_tanque
             FROM tanque t
             INNER JOIN tipo_tanque tt ON tt.id_tipo_tanque = t.id_tipo_tanque
             WHERE t.id_zoocriadero = $1
             ORDER BY t.numero_tanque",
            [$idZoocriadero]
        );
    }

    // ---------------- INSERT ----------------
    public function crear($datos){
        return $this->selectValue(
            "INSERT INTO zoocriadero
             (nombre, direccion, comuna, barrio, id_persona_cargo, latitud, longitud, estado)
             VALUES ($1, $2, $3, $4, $5, $6, $7, 1)
             RETURNING id_zoocriadero",
            [
                $datos['nombre'],
                $datos['direccion'],
                $datos['comuna'],
                $datos['barrio'],
                $datos['id_persona_cargo'],
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
                 id_persona_cargo = $5, latitud = $6, longitud = $7
             WHERE id_zoocriadero = $8",
            [
                $datos['nombre'],
                $datos['direccion'],
                $datos['comuna'],
                $datos['barrio'],
                $datos['id_persona_cargo'],
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

    public function existeNumeroTanque($idZoocriadero, $numero){
        return $this->selectValue(
            "SELECT 1 FROM tanque WHERE id_zoocriadero = $1 AND numero_tanque = $2",
            [$idZoocriadero, $numero]
        ) !== null;
    }

    public function crearTanque($idZoocriadero, $idTipoTanque, $numero){
        return $this->selectValue(
            "INSERT INTO tanque (id_zoocriadero, id_tipo_tanque, numero_tanque, estado)
             VALUES ($1, $2, $3, 1)
             RETURNING id_tanque",
            [$idZoocriadero, $idTipoTanque, $numero]
        );
    }

    public function usuarioExiste($idUsuario){
        return $this->selectValue(
            "SELECT 1 FROM usuario WHERE id_usuario = $1 AND estado = 1",
            [$idUsuario]
        ) !== null;
    }

    public function tipoTanqueExiste($idTipoTanque){
        return $this->selectValue(
            "SELECT 1 FROM tipo_tanque WHERE id_tipo_tanque = $1 AND estado = 1",
            [$idTipoTanque]
        ) !== null;
    }
}

?>
