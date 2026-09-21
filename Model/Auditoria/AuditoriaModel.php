<?php

include_once __DIR__ . '/../MasterModel.php';

// ============================================================
// Modelo del módulo Auditoría.
//
// No inserta nada: los triggers de la base (fn_auditoria_general y
// fn_auditoria_seguimiento_zoocriadero, ver BD_Dengue_SIGuppy.sql)
// ya escriben solos en estas dos tablas cada vez que alguien
// inserta, actualiza o elimina un registro en actividad,
// tipo_deposito, sitio, actividad_terreno o seguimiento_zoocriadero.
// Este modelo solo consulta lo que esos triggers ya guardaron.
//
// Cada cambio genera dos filas (momento = 'ANTES' y 'DESPUES', una
// por cada disparo BEFORE/AFTER del trigger); nos quedamos solo con
// 'DESPUES' para no duplicar cada movimiento en el listado.
// ============================================================
class AuditoriaModel extends MasterModel
{
    // Auditoría general: actividad, tipo_deposito, sitio, actividad_terreno...
    public function listarGeneral($filtros = [])
    {
        $condiciones = ["momento = 'DESPUES'"];
        $parametros  = [];

        if (!empty($filtros['tabla'])) {
            $parametros[]   = $filtros['tabla'];
            $condiciones[]  = "tabla_afectada = $" . count($parametros);
        }
        if (!empty($filtros['operacion'])) {
            $parametros[]   = $filtros['operacion'];
            $condiciones[]  = "operacion = $" . count($parametros);
        }
        if (!empty($filtros['id_usuario'])) {
            $parametros[]   = $filtros['id_usuario'];
            $condiciones[]  = "id_usuario = $" . count($parametros);
        }
        if (!empty($filtros['fecha_inicio'])) {
            $parametros[]   = $filtros['fecha_inicio'];
            $condiciones[]  = "fecha_evento >= $" . count($parametros);
        }
        if (!empty($filtros['fecha_fin'])) {
            $parametros[]   = $filtros['fecha_fin'];
            $condiciones[]  = "fecha_evento <= $" . count($parametros);
        }

        $sql = "SELECT id_auditoria, tabla_afectada, operacion,
                       usuario_responsable, id_usuario,
                       TO_CHAR(fecha_hora_evento, 'DD/MM/YYYY HH24:MI') AS fecha_hora,
                       detalle
                FROM auditoria_sistema
                WHERE " . implode(' AND ', $condiciones) . "
                ORDER BY fecha_hora_evento DESC
                LIMIT 300";

        return $this->selectAll($sql, $parametros);
    }

    // Auditoría específica de seguimiento_zoocriadero (con su propia tabla)
    public function listarSeguimientoZoocriadero($filtros = [])
    {
        $condiciones = ["az.momento = 'DESPUES'"];
        $parametros  = [];

        if (!empty($filtros['operacion'])) {
            $parametros[]   = $filtros['operacion'];
            $condiciones[]  = "az.operacion = $" . count($parametros);
        }
        if (!empty($filtros['id_usuario'])) {
            $parametros[]   = $filtros['id_usuario'];
            $condiciones[]  = "az.id_usuario = $" . count($parametros);
        }
        if (!empty($filtros['fecha_inicio'])) {
            $parametros[]   = $filtros['fecha_inicio'];
            $condiciones[]  = "az.fecha_evento >= $" . count($parametros);
        }
        if (!empty($filtros['fecha_fin'])) {
            $parametros[]   = $filtros['fecha_fin'];
            $condiciones[]  = "az.fecha_evento <= $" . count($parametros);
        }

        $sql = "SELECT az.id_auditoria, az.id_seguimiento, az.operacion, az.id_usuario,
                       COALESCE(u.nombre || ' ' || u.apellido, 'Usuario no identificado') AS usuario_responsable,
                       TO_CHAR(az.fecha_hora_evento, 'DD/MM/YYYY HH24:MI') AS fecha_hora,
                       az.detalle
                FROM auditoria_seguimiento_zoocriadero az
                LEFT JOIN usuario u ON u.id_usuario = az.id_usuario
                WHERE " . implode(' AND ', $condiciones) . "
                ORDER BY az.fecha_hora_evento DESC
                LIMIT 300";

        return $this->selectAll($sql, $parametros);
    }

    // Para llenar el <select> de "Tabla / módulo" en los filtros
    public function tablasDisponibles()
    {
        return $this->selectAll(
            "SELECT DISTINCT tabla_afectada FROM auditoria_sistema ORDER BY tabla_afectada"
        );
    }

    // Para llenar el <select> de "Usuario" en los filtros
    public function usuariosDisponibles()
    {
        return $this->selectAll(
            "SELECT id_usuario, nombre || ' ' || apellido AS nombre_completo
             FROM usuario
             ORDER BY nombre, apellido"
        );
    }
}
