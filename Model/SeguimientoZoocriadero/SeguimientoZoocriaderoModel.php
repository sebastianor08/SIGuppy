<?php

include_once __DIR__ . '/../MasterModel.php';

// Migrado de PDO a la extensión nativa pgsql.
// Los datos del formulario viajan como parámetros ($1, $2, $3...)
// con pg_query_params: nunca se concatenan dentro del texto del SQL.
class SeguimientoZoocriaderoModel extends MasterModel
{

    public function zoocriaderosActivos()
    {
        return $this->selectAll(
            "SELECT id_zoocriadero, nombre, direccion, comuna, barrio
            FROM zoocriadero
            WHERE estado = 1
            ORDER BY nombre"
        );
    }

    public function tanquesPorZoocriadero($idZoocriadero)
    {
        return $this->selectAll(
            "SELECT t.id_tanque, t.id_zoocriadero, t.nombre_tanque, tt.nombre AS tipo_tanque
            FROM tanque t
            INNER JOIN tipo_tanque tt ON tt.id_tipo_tanque = t.id_tipo_tanque
            WHERE t.id_zoocriadero = $1 AND t.estado = 1
            ORDER BY t.nombre_tanque",
            [$idZoocriadero]
        );
    }

    public function accionesActivas()
    {
        return $this->selectAll(
            "SELECT id_actividad, nombre, descripcion
            FROM actividad
            WHERE ambito = 'zoocriadero' AND estado = 1
            ORDER BY nombre"
        );
    }

    public function tanquePerteneceAZoocriadero($idTanque, $idZoocriadero)
    {
        return $this->selectValue(
            "SELECT 1 FROM tanque
            WHERE id_tanque = $1 AND id_zoocriadero = $2 AND estado = 1",
            [$idTanque, $idZoocriadero]
        ) !== null;
    }

    // Igual que la de arriba pero SIN filtrar por estado: sirve solo
    // para distinguir "el tanque no existe en ese zoocriadero" de
    // "el tanque existe pero está inhabilitado" y dar un mensaje claro.
    public function tanqueExisteEnZoocriadero($idTanque, $idZoocriadero)
    {
        return $this->selectValue(
            "SELECT 1 FROM tanque
            WHERE id_tanque = $1 AND id_zoocriadero = $2",
            [$idTanque, $idZoocriadero]
        ) !== null;
    }

    public function zoocriaderoActivoExiste($idZoocriadero)
    {
        return $this->selectValue(
            "SELECT 1 FROM zoocriadero WHERE id_zoocriadero = $1 AND estado = 1",
            [$idZoocriadero]
        ) !== null;
    }

    public function accionValida($idActividad)
    {
        return $this->selectValue(
            "SELECT 1 FROM actividad
            WHERE id_actividad = $1 AND ambito = 'zoocriadero' AND estado = 1",
            [$idActividad]
        ) !== null;
    }

    public function primerUsuarioActivo()
    {
        return $this->selectValue(
            "SELECT id_usuario FROM usuario WHERE estado = 1 ORDER BY id_usuario LIMIT 1"
        );
    }

    // Inserta el seguimiento y devuelve el id generado (RETURNING de PostgreSQL).
    public function crearSeguimiento($datos)
    {
        $id = $this->selectValue(
            "INSERT INTO seguimiento_zoocriadero
            (id_zoocriadero, id_tanque, id_usuario, fecha, ph, temperatura,
            numero_sembrados, numero_nacidos, numero_muertos,
            numero_nacidos_hembra, numero_nacidos_macho,
            numero_muertos_hembra, numero_muertos_macho, observaciones)
            VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14)
            RETURNING id_seguimiento",
            [
                $datos['id_zoocriadero'],
                $datos['id_tanque'],
                $datos['id_usuario'],
                $datos['fecha'],
                $datos['ph'],
                $datos['temperatura'],
                $datos['numero_sembrados'],
                $datos['numero_nacidos'],
                $datos['numero_muertos'],
                $datos['numero_nacidos_hembra'],
                $datos['numero_nacidos_macho'],
                $datos['numero_muertos_hembra'],
                $datos['numero_muertos_macho'],
                ($datos['observaciones'] !== '' ? $datos['observaciones'] : null),
            ]
        );

        if ($id === null) {
            throw new Exception("No se pudo insertar el seguimiento: " . $this->ultimoError());
        }
        return (int) $id;
    }

    public function vincularActividad($idSeguimiento, $idActividad)
    {
        $ok = $this->insert(
            "INSERT INTO actividad_zoocriadero (id_seguimiento, id_actividad)
            VALUES ($1, $2)",
            [$idSeguimiento, $idActividad]
        );
        if ($ok === false) {
            throw new Exception("No se pudo vincular la actividad: " . $this->ultimoError());
        }
    }

    // Vincula varias acciones a un mismo seguimiento (el formulario ahora
    // permite elegir más de una acción por registro).
    public function vincularActividades($idSeguimiento, array $idsActividad)
    {
        foreach ($idsActividad as $idActividad) {
            $this->vincularActividad($idSeguimiento, $idActividad);
        }
    }

    // Historial para la tabla de consulta (RF002).
    // Trae también los ids para poder cargar un registro en el formulario y editarlo.
    // Un seguimiento puede tener varias acciones vinculadas (tabla
    // actividad_zoocriadero), así que se agrupan en una sola fila:
    // "actividad" trae los nombres separados por coma para mostrar en la
    // tabla, e "id_actividades" trae el arreglo de ids para poder
    // precargar el formulario al editar.
    public function historial($limite = 50)
    {
        $filas = $this->selectAll(
            "SELECT s.id_seguimiento, TO_CHAR(s.fecha, 'YYYY-MM-DD') AS fecha,
                    s.id_zoocriadero, z.nombre AS zoocriadero,
                    s.id_tanque, t.nombre_tanque,
                    s.ph, s.temperatura,
                    s.numero_sembrados, s.numero_nacidos, s.numero_muertos,
                    s.numero_nacidos_hembra, s.numero_nacidos_macho,
                    s.numero_muertos_hembra, s.numero_muertos_macho,
                    s.observaciones, s.estado,
                    u.nombre || ' ' || u.apellido AS responsable,
                    STRING_AGG(a.nombre, ', ' ORDER BY a.nombre) AS actividad,
                    STRING_AGG(az.id_actividad::text, ',' ORDER BY az.id_actividad) AS id_actividades_csv
            FROM seguimiento_zoocriadero s
            INNER JOIN zoocriadero z ON z.id_zoocriadero = s.id_zoocriadero
            INNER JOIN tanque t      ON t.id_tanque = s.id_tanque
            LEFT JOIN usuario u      ON u.id_usuario = s.id_usuario
            LEFT JOIN actividad_zoocriadero az ON az.id_seguimiento = s.id_seguimiento
            LEFT JOIN actividad a    ON a.id_actividad = az.id_actividad
            GROUP BY s.id_seguimiento, z.id_zoocriadero, t.id_tanque, u.id_usuario, u.nombre, u.apellido
            ORDER BY s.fecha DESC, s.id_seguimiento DESC
            LIMIT $1",
            [$limite]
        );

        // STRING_AGG de Postgres llega como texto "3,7,9": se convierte
        // aquí a un arreglo real de enteros para que el JSON de salida
        // ya traiga id_actividades como lista, lista para el formulario.
        foreach ($filas as &$fila) {
            $csv = $fila['id_actividades_csv'] ?? '';
            $fila['id_actividades'] = ($csv === '' || $csv === null)
                ? []
                : array_map('intval', explode(',', $csv));
            unset($fila['id_actividades_csv']);
        }
        unset($fila);

        return $filas;
    }

    public function buscarSeguimiento($idSeguimiento)
    {
        return $this->selectOne(
            "SELECT s.*, az.id_actividad
            FROM seguimiento_zoocriadero s
            LEFT JOIN actividad_zoocriadero az ON az.id_seguimiento = s.id_seguimiento
            WHERE s.id_seguimiento = $1",
            [$idSeguimiento]
        );
    }

    // ---------------- UPDATE ----------------
    public function actualizarSeguimiento($idSeguimiento, $datos)
    {
        $ok = $this->update(
            "UPDATE seguimiento_zoocriadero
            SET id_zoocriadero = $1, id_tanque = $2, fecha = $3,
                ph = $4, temperatura = $5,
                numero_sembrados = $6, numero_nacidos = $7, numero_muertos = $8,
                numero_nacidos_hembra = $9, numero_nacidos_macho = $10,
                numero_muertos_hembra = $11, numero_muertos_macho = $12,
                observaciones = $13
            WHERE id_seguimiento = $14",
            [
                $datos['id_zoocriadero'],
                $datos['id_tanque'],
                $datos['fecha'],
                $datos['ph'],
                $datos['temperatura'],
                $datos['numero_sembrados'],
                $datos['numero_nacidos'],
                $datos['numero_muertos'],
                $datos['numero_nacidos_hembra'],
                $datos['numero_nacidos_macho'],
                $datos['numero_muertos_hembra'],
                $datos['numero_muertos_macho'],
                ($datos['observaciones'] !== '' ? $datos['observaciones'] : null),
                $idSeguimiento,
            ]
        );

        if ($ok === false) {
            throw new Exception("No se pudo actualizar el seguimiento: " . $this->ultimoError());
        }
        return true;
    }

    // Las actividades viven en una tabla aparte: se borran las anteriores y
    // se ponen las nuevas (puede ser una o varias).
    public function reemplazarActividades($idSeguimiento, array $idsActividad)
    {
        $this->delete(
            "DELETE FROM actividad_zoocriadero WHERE id_seguimiento = $1",
            [$idSeguimiento]
        );
        $this->vincularActividades($idSeguimiento, $idsActividad);
    }

    public function existeSeguimiento($idSeguimiento)
    {
        return $this->selectValue(
            "SELECT 1 FROM seguimiento_zoocriadero WHERE id_seguimiento = $1",
            [$idSeguimiento]
        ) !== null;
    }

    // Descartar / restaurar UN seguimiento (no se borra, se cambia el estado,
    // igual que en el resto de módulos del sistema).
    public function cambiarEstado($idSeguimiento, $estado)
    {
        return $this->update(
            "UPDATE seguimiento_zoocriadero SET estado = $1 WHERE id_seguimiento = $2",
            [$estado, $idSeguimiento]
        ) !== false;
    }

    // Descartar / restaurar VARIOS seguimientos a la vez, seleccionados en
    // la tabla en vez de tener que abrir uno por uno.
    public function cambiarEstadoMasivo(array $ids, $estado)
    {
        if (empty($ids)) {
            return 0;
        }
        $idsEnteros = array_values(array_map('intval', $ids));
        $resultado = $this->update(
            "UPDATE seguimiento_zoocriadero SET estado = $1 WHERE id_seguimiento = ANY($2::bigint[])",
            [$estado, '{' . implode(',', $idsEnteros) . '}']
        );
        return $resultado !== false ? count($idsEnteros) : false;
    }
}
