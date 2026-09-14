<?php

include_once __DIR__ . '/../MasterModel.php';

// Migrado de PDO a la extensión nativa pgsql.
// Los datos del formulario viajan como parámetros ($1, $2, $3...)
// con pg_query_params: nunca se concatenan dentro del texto del SQL.
class SeguimientoZoocriaderoModel extends MasterModel{

    public function zoocriaderosActivos(){
        return $this->selectAll(
            "SELECT id_zoocriadero, nombre, direccion, comuna, barrio
             FROM zoocriadero
             WHERE estado = 1
             ORDER BY nombre"
        );
    }

    public function tanquesPorZoocriadero($idZoocriadero){
        return $this->selectAll(
            "SELECT t.id_tanque, t.id_zoocriadero, t.numero_tanque, tt.nombre AS tipo_tanque
             FROM tanque t
             INNER JOIN tipo_tanque tt ON tt.id_tipo_tanque = t.id_tipo_tanque
             WHERE t.id_zoocriadero = $1 AND t.estado = 1
             ORDER BY t.numero_tanque",
            [$idZoocriadero]
        );
    }

    public function accionesActivas(){
        return $this->selectAll(
            "SELECT id_actividad, nombre, descripcion
             FROM actividad
             WHERE ambito = 'zoocriadero' AND estado = 1
             ORDER BY nombre"
        );
    }

    public function tanquePerteneceAZoocriadero($idTanque, $idZoocriadero){
        return $this->selectValue(
            "SELECT 1 FROM tanque
             WHERE id_tanque = $1 AND id_zoocriadero = $2 AND estado = 1",
            [$idTanque, $idZoocriadero]
        ) !== null;
    }

    public function zoocriaderoActivoExiste($idZoocriadero){
        return $this->selectValue(
            "SELECT 1 FROM zoocriadero WHERE id_zoocriadero = $1 AND estado = 1",
            [$idZoocriadero]
        ) !== null;
    }

    public function accionValida($idActividad){
        return $this->selectValue(
            "SELECT 1 FROM actividad
             WHERE id_actividad = $1 AND ambito = 'zoocriadero' AND estado = 1",
            [$idActividad]
        ) !== null;
    }

    public function primerUsuarioActivo(){
        return $this->selectValue(
            "SELECT id_usuario FROM usuario WHERE estado = 1 ORDER BY id_usuario LIMIT 1"
        );
    }

    // Inserta el seguimiento y devuelve el id generado (RETURNING de PostgreSQL).
    public function crearSeguimiento($datos){
        $id = $this->selectValue(
            "INSERT INTO seguimiento_zoocriadero
             (id_zoocriadero, id_tanque, id_usuario, fecha, numero_sembrados, numero_nacidos, numero_muertos, observaciones)
             VALUES ($1, $2, $3, $4, $5, $6, $7, $8)
             RETURNING id_seguimiento",
            [
                $datos['id_zoocriadero'],
                $datos['id_tanque'],
                $datos['id_usuario'],
                $datos['fecha'],
                $datos['numero_sembrados'],
                $datos['numero_nacidos'],
                $datos['numero_muertos'],
                ($datos['observaciones'] !== '' ? $datos['observaciones'] : null),
            ]
        );

        if($id === null){
            throw new Exception("No se pudo insertar el seguimiento: " . $this->ultimoError());
        }
        return (int) $id;
    }

    public function vincularActividad($idSeguimiento, $idActividad){
        $ok = $this->insert(
            "INSERT INTO actividad_zoocriadero (id_seguimiento, id_actividad)
             VALUES ($1, $2)",
            [$idSeguimiento, $idActividad]
        );
        if($ok === false){
            throw new Exception("No se pudo vincular la actividad: " . $this->ultimoError());
        }
    }

    // Historial para la tabla de consulta (RF002).
    public function historial($limite = 50){
        return $this->selectAll(
            "SELECT s.id_seguimiento, s.fecha, z.nombre AS zoocriadero,
                    t.numero_tanque, s.numero_nacidos, s.numero_muertos, s.observaciones
             FROM seguimiento_zoocriadero s
             INNER JOIN zoocriadero z ON z.id_zoocriadero = s.id_zoocriadero
             INNER JOIN tanque t      ON t.id_tanque = s.id_tanque
             WHERE s.estado = 1
             ORDER BY s.fecha DESC, s.id_seguimiento DESC
             LIMIT $1",
            [$limite]
        );
    }
}

?>
