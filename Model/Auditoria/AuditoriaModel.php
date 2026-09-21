<?php

include_once __DIR__ . '/../MasterModel.php';

class AuditoriaModel extends MasterModel
{
    // Auditoría general: cualquier módulo con trigger, más los inicios
    // de sesión (modulo = 'login').
    public function listarGeneral($filtros = [])
    {
        $condiciones = ["1 = 1"];
        $parametros  = [];

        if (!empty($filtros['tabla'])) {
            $parametros[]  = $filtros['tabla'];
            $condiciones[] = "a.modulo = $" . count($parametros);
        }
        if (!empty($filtros['operacion'])) {
            $parametros[]  = $filtros['operacion'];
            $condiciones[] = "a.accion = $" . count($parametros);
        }
        if (!empty($filtros['id_usuario'])) {
            $parametros[]  = $filtros['id_usuario'];
            $condiciones[] = "a.id_usuario = $" . count($parametros);
        }
        if (!empty($filtros['fecha_inicio'])) {
            $parametros[]  = $filtros['fecha_inicio'];
            $condiciones[] = "a.fecha_hora >= $" . count($parametros);
        }
        if (!empty($filtros['fecha_fin'])) {
            $parametros[]  = $filtros['fecha_fin'] . ' 23:59:59';
            $condiciones[] = "a.fecha_hora <= $" . count($parametros);
        }

        $sql = "SELECT a.id_auditoria, a.modulo, a.accion, a.id_registro, a.id_usuario,
                       COALESCE(u.nombre || ' ' || u.apellido, 'Usuario no identificado') AS usuario_responsable,
                       TO_CHAR(a.fecha_hora, 'DD/MM/YYYY HH24:MI') AS fecha_hora,
                       a.detalle
                FROM auditoria a
                LEFT JOIN usuario u ON u.id_usuario = a.id_usuario
                WHERE " . implode(' AND ', $condiciones) . "
                ORDER BY a.fecha_hora DESC
                LIMIT 300";

        return $this->selectAll($sql, $parametros);
    }

    // Auditoría de seguimiento de zoocriaderos: mismo `auditoria`,
    // filtrado a ese módulo (antes se leía de una tabla separada,
    // auditoria_seguimiento_zoocriadero, que ningún trigger llenaba).
    public function listarSeguimientoZoocriadero($filtros = [])
    {
        $condiciones = ["a.modulo = 'seguimiento_zoocriadero'"];
        $parametros  = [];

        if (!empty($filtros['operacion'])) {
            $parametros[]  = $filtros['operacion'];
            $condiciones[] = "a.accion = $" . count($parametros);
        }
        if (!empty($filtros['id_usuario'])) {
            $parametros[]  = $filtros['id_usuario'];
            $condiciones[] = "a.id_usuario = $" . count($parametros);
        }
        if (!empty($filtros['fecha_inicio'])) {
            $parametros[]  = $filtros['fecha_inicio'];
            $condiciones[] = "a.fecha_hora >= $" . count($parametros);
        }
        if (!empty($filtros['fecha_fin'])) {
            $parametros[]  = $filtros['fecha_fin'] . ' 23:59:59';
            $condiciones[] = "a.fecha_hora <= $" . count($parametros);
        }

        $sql = "SELECT a.id_auditoria, a.id_registro AS id_seguimiento, a.accion, a.id_usuario,
                       COALESCE(u.nombre || ' ' || u.apellido, 'Usuario no identificado') AS usuario_responsable,
                       TO_CHAR(a.fecha_hora, 'DD/MM/YYYY HH24:MI') AS fecha_hora,
                       a.detalle
                FROM auditoria a
                LEFT JOIN usuario u ON u.id_usuario = a.id_usuario
                WHERE " . implode(' AND ', $condiciones) . "
                ORDER BY a.fecha_hora DESC
                LIMIT 300";

        return $this->selectAll($sql, $parametros);
    }

    // Para llenar el <select> de "Tabla / módulo" en los filtros
    public function tablasDisponibles()
    {
        return $this->selectAll(
            "SELECT DISTINCT modulo FROM auditoria ORDER BY modulo"
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