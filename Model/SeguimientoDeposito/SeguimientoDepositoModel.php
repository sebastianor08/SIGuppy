<?php

include_once __DIR__ . '/../MasterModel.php';

// Tabla principal: seguimiento_deposito (ver Database/migracion_seguimiento_deposito.sql)
//   id_seguimiento_deposito bigint (identity), id_deposito bigint NOT NULL -> deposito,
//   id_usuario bigint NOT NULL -> usuario, id_actividad bigint NOT NULL -> actividad (ámbito 'terreno'),
//   fecha date NOT NULL, presencia_larvas smallint (0/1), numero_peces_sembrados integer,
//   observaciones varchar(300) NULL, estado smallint DEFAULT 1, creado_en timestamp
//
// Un seguimiento es UNA visita a UN depósito. La ubicación (dirección y
// coordenadas) no se guarda aquí: se obtiene por deposito -> sitio -> direccion.
//
// Los triggers de la tabla (trg_validar_seguimiento_deposito y
// trg_auditoria_seguimiento_deposito) repiten en la BD las reglas de negocio
// y llenan la auditoría; este modelo solo lee y escribe.
class SeguimientoDepositoModel extends MasterModel
{
    // ---- Depósitos con su ubicación (alimentan el <select> y el mapa) ----
    //
    // Solo depósitos habilitados de sitios habilitados. Devuelve TAMBIÉN los que
    // no tienen coordenadas (latitud/longitud NULL) para poder avisar en pantalla
    // que no se pueden dibujar en el mapa. "ultima_*" es el seguimiento activo
    // más reciente del depósito (NULL si nunca se le ha hecho seguimiento).
    public function depositos()
    {
        return $this->selectAll(
            "SELECT
                dep.id_deposito,
                dep.descripcion,
                td.nombre AS tipo_deposito,
                s.id_sitio,
                s.nombre AS sitio,
                d.direccion,
                b.nombre AS barrio,
                co.nombre AS comuna,
                ci.nombre AS ciudad,
                d.latitud,
                d.longitud,
                TO_CHAR(ult.fecha, 'YYYY-MM-DD') AS ultima_fecha,
                ult.presencia_larvas AS ultima_presencia_larvas,
                (SELECT COUNT(*)
                   FROM seguimiento_deposito x
                  WHERE x.id_deposito = dep.id_deposito
                    AND x.estado = 1) AS total_seguimientos
             FROM deposito dep
             INNER JOIN tipo_deposito td ON td.id_tipo_deposito = dep.id_tipo_deposito
             INNER JOIN sitio s ON s.id_sitio = dep.id_sitio
             INNER JOIN direccion d ON d.id_direccion = s.id_direccion
             LEFT JOIN barrio b ON b.id_barrio = d.id_barrio
             LEFT JOIN comuna co ON co.id_comuna = d.id_comuna
             LEFT JOIN ciudad ci ON ci.id_ciudad = d.id_ciudad
             LEFT JOIN LATERAL (
                SELECT sd.fecha, sd.presencia_larvas
                  FROM seguimiento_deposito sd
                 WHERE sd.id_deposito = dep.id_deposito
                   AND sd.estado = 1
                 ORDER BY sd.fecha DESC, sd.id_seguimiento_deposito DESC
                 LIMIT 1
             ) ult ON TRUE
             WHERE dep.estado = 1
               AND s.estado = 1
             ORDER BY dep.id_deposito DESC"
        );
    }

    // Acciones que se pueden registrar en un seguimiento: las actividades de terreno habilitadas.
    public function actividadesTerreno()
    {
        return $this->selectAll(
            "SELECT id_actividad, nombre, descripcion
             FROM actividad
             WHERE ambito = 'terreno' AND estado = 1
             ORDER BY nombre"
        );
    }

    // ---- Validaciones contra la base de datos ----

    // El depósito debe existir y estar habilitado, y su sitio también
    // (es lo mismo que exige el trigger trg_validar_seguimiento_deposito).
    public function depositoDisponible($idDeposito)
    {
        return $this->selectValue(
            "SELECT 1
             FROM deposito dep
             INNER JOIN sitio s ON s.id_sitio = dep.id_sitio
             WHERE dep.id_deposito = $1
               AND dep.estado = 1
               AND s.estado = 1",
            [$idDeposito]
        ) !== null;
    }

    // Válida = actividad de terreno habilitada. Al editar se acepta además la que
    // el seguimiento ya tenía ($idActual), para poder corregir otros datos aunque
    // esa actividad se haya inhabilitado después.
    public function actividadDisponible($idActividad, $idActual = null)
    {
        return $this->selectValue(
            "SELECT 1
             FROM actividad
             WHERE id_actividad = $1
               AND ambito = 'terreno'
               AND (estado = 1 OR id_actividad = $2)",
            [$idActividad, $idActual]
        ) !== null;
    }

    // ---- Lectura ----

    // Historial de seguimientos (los más recientes primero), con todo lo que la
    // tabla necesita mostrar. Trae también los inhabilitados: la pantalla los filtra.
    public function historial($limite = 200)
    {
        return $this->selectAll(
            "SELECT
                sd.id_seguimiento_deposito,
                TO_CHAR(sd.fecha, 'YYYY-MM-DD') AS fecha,
                sd.id_deposito,
                dep.descripcion AS deposito_descripcion,
                td.nombre AS tipo_deposito,
                s.nombre AS sitio,
                d.direccion,
                b.nombre AS barrio,
                co.nombre AS comuna,
                sd.id_actividad,
                a.nombre AS actividad,
                sd.presencia_larvas,
                sd.numero_peces_sembrados,
                sd.observaciones,
                sd.estado,
                u.nombre || ' ' || u.apellido AS usuario,
                TO_CHAR(sd.creado_en, 'DD/MM/YYYY HH24:MI') AS registrado
             FROM seguimiento_deposito sd
             INNER JOIN deposito dep ON dep.id_deposito = sd.id_deposito
             INNER JOIN tipo_deposito td ON td.id_tipo_deposito = dep.id_tipo_deposito
             INNER JOIN sitio s ON s.id_sitio = dep.id_sitio
             INNER JOIN direccion d ON d.id_direccion = s.id_direccion
             LEFT JOIN barrio b ON b.id_barrio = d.id_barrio
             LEFT JOIN comuna co ON co.id_comuna = d.id_comuna
             INNER JOIN actividad a ON a.id_actividad = sd.id_actividad
             LEFT JOIN usuario u ON u.id_usuario = sd.id_usuario
             ORDER BY sd.fecha DESC, sd.id_seguimiento_deposito DESC
             LIMIT $1",
            [$limite]
        );
    }

    public function buscar($idSeguimiento)
    {
        return $this->selectOne(
            "SELECT id_seguimiento_deposito, id_deposito, id_usuario, id_actividad,
                    TO_CHAR(fecha, 'YYYY-MM-DD') AS fecha,
                    presencia_larvas, numero_peces_sembrados, observaciones, estado
             FROM seguimiento_deposito
             WHERE id_seguimiento_deposito = $1",
            [$idSeguimiento]
        );
    }

    // ---- Escritura ----

    public function crear($datos)
    {
        // No se envía estado ni creado_en: la BD asigna sus DEFAULT.
        return $this->selectValue(
            "INSERT INTO seguimiento_deposito
                (id_deposito, id_usuario, id_actividad, fecha,
                 presencia_larvas, numero_peces_sembrados, observaciones)
             VALUES ($1, $2, $3, $4, $5, $6, $7)
             RETURNING id_seguimiento_deposito",
            [
                $datos['id_deposito'],
                $datos['id_usuario'],
                $datos['id_actividad'],
                $datos['fecha'],
                $datos['presencia_larvas'],
                $datos['numero_peces_sembrados'],
                ($datos['observaciones'] !== '' ? $datos['observaciones'] : null)
            ]
        );
    }

    // Al editar NO se cambian el depósito, la fecha ni el usuario que lo registró:
    // son la identidad del seguimiento.
    public function actualizar($idSeguimiento, $datos)
    {
        $resultado = $this->update(
            "UPDATE seguimiento_deposito
             SET id_actividad = $1,
                 presencia_larvas = $2,
                 numero_peces_sembrados = $3,
                 observaciones = $4
             WHERE id_seguimiento_deposito = $5",
            [
                $datos['id_actividad'],
                $datos['presencia_larvas'],
                $datos['numero_peces_sembrados'],
                ($datos['observaciones'] !== '' ? $datos['observaciones'] : null),
                $idSeguimiento
            ]
        );

        return $resultado !== false;
    }

    public function cambiarEstado($idSeguimiento, $estado)
    {
        $resultado = $this->update(
            "UPDATE seguimiento_deposito
             SET estado = $1
             WHERE id_seguimiento_deposito = $2",
            [$estado, $idSeguimiento]
        );

        return $resultado !== false;
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
            "UPDATE seguimiento_deposito SET estado = $1 WHERE id_seguimiento_deposito = ANY($2::bigint[])",
            [$estado, '{' . implode(',', $idsEnteros) . '}']
        );
        return $resultado !== false ? count($idsEnteros) : false;
    }

    // Mensaje de error de PostgreSQL apto para mostrar: solo la primera línea y
    // sin el prefijo "ERROR:  " (el resto es CONTEXT: PL/pgSQL function ...).
    // Así, si un trigger rechaza la operación, el usuario lee el texto del RAISE.
    public function mensajeError()
    {
        $error = trim((string) $this->ultimoError());
        if ($error === '') {
            return 'error desconocido de la base de datos.';
        }

        $primeraLinea = trim(strtok($error, "\n"));
        return trim(preg_replace('/^ERROR:\s*/', '', $primeraLinea));
    }
}
