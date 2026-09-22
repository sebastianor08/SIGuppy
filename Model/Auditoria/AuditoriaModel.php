<?php

include_once __DIR__ . '/../MasterModel.php';

class AuditoriaModel extends MasterModel
{
    public function listarGeneral($filtros = [])
    {
        $condiciones = ["1 = 1"];
        $parametros  = [];

        if (!empty($filtros['modulo'])) {
            $condiciones[] = $this->condicionModulo($filtros['modulo'], $parametros);
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

        $sql = "SELECT a.id_auditoria, a.modulo,
                       COALESCE(a.datos_nuevos, a.datos_anteriores) ->> 'ambito' AS ambito,
                       a.accion, a.id_registro, a.id_usuario,
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

    public function listarSeguimientoDeposito($filtros = [])
    {
        $condiciones = ["a.modulo = 'seguimiento_deposito'"];
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

    private static $etiquetasTablas = [
        'actividad'                => 'Actividades',
        'actividad_terreno'        => 'Actividades de terreno',
        'actividad_zoocriadero'    => 'Actividades de zoocriadero',
        'deposito'                 => 'Depósitos',
        'direccion'                => 'Direcciones',
        'login'                    => 'Inicios de sesión',
        'modulo_accion_permitida'  => 'Acciones permitidas por módulo',
        'reportes'                 => 'Reportes',
        'rol'                      => 'Roles',
        'rol_permiso'              => 'Permisos de roles',
        'seguimiento_deposito'     => 'Seguimiento de depósitos',
        'seguimiento_terreno'      => 'Seguimiento de terreno',
        'seguimiento_zoocriadero'  => 'Seguimiento de zoocriaderos',
        'sitio'                    => 'Sitios',
        'tanque'                   => 'Tanques',
        'territorio_priorizado'    => 'Territorios priorizados',
        'tipo_deposito'            => 'Tipos de depósito',
        'usuario'                  => 'Usuarios',
        'zoocriadero'              => 'Zoocriaderos',
    ];

    public static function etiquetaModulo($tabla, $ambito = null)
    {
        if ($tabla === 'actividad') {
            if ($ambito === 'zoocriadero') {
                return 'Acciones de zoocriadero';
            }
            if ($ambito === 'terreno') {
                return 'Actividades de terreno';
            }
        }

        return self::$etiquetasTablas[$tabla]
            ?? ucfirst(str_replace('_', ' ', (string) $tabla));
    }


    private static $reglasPorModulo = [
        1  => [['tablas' => ['zoocriadero']]],                                  // Zoocriaderos
        7  => [['tablas' => ['actividad'], 'ambito' => 'terreno']],             // Actividades
        8  => [['tablas' => ['seguimiento_zoocriadero', 'actividad_zoocriadero']]], // Seguimiento de Zoocriadero
        9  => [['tablas' => ['tipo_deposito']]],                                // Tipo Depósitos
        10 => [['tablas' => ['sitio', 'direccion']]],                           // Sitio
        11 => [['tablas' => ['actividad'], 'ambito' => 'zoocriadero']],         // Acciones de Zoocriadero
        12 => [['tablas' => ['deposito']]],                                     // Depósitos
        17 => [['tablas' => ['copia_seguridad_historial']]],                   // Copia de seguridad
        18 => [['tablas' => ['reportes']]],                                     // Reportes (exportaciones)
        19 => [['tablas' => ['usuario']]],                                      // Gestión de Usuarios
        20 => [['tablas' => ['tanque']]],                                       // Tanque Zoocriadero
        21 => [['tablas' => ['rol', 'rol_permiso', 'modulo_accion_permitida']]], // Gestión de Roles (crear/editar/consultar/inhabilitar)
        25 => [['tablas' => ['seguimiento_deposito']]],                         // Seguimiento de Depósito
    ];


    private function condicionModulo($idModulo, array &$parametros)
    {
        $reglas = self::$reglasPorModulo[(int) $idModulo] ?? [];

        if (count($reglas) === 0) {
            return "1 = 0";
        }

        $alternativas = [];
        foreach ($reglas as $regla) {
            $parametros[] = '{' . implode(',', $regla['tablas']) . '}';
            $clausula     = "a.modulo = ANY($" . count($parametros) . "::text[])";

            if (!empty($regla['ambito'])) {
                $parametros[] = $regla['ambito'];
                $clausula    .= " AND COALESCE(a.datos_nuevos, a.datos_anteriores) ->> 'ambito' = $" . count($parametros);
            }

            $alternativas[] = "(" . $clausula . ")";
        }

        return "(" . implode(' OR ', $alternativas) . ")";
    }

    // Registra en la auditoría la exportación completa de un reporte
    // (el botón "Excel completo", que sí pasa por el servidor). El
    // "Excel sencillo" y el PDF se generan enteramente en el navegador
    // (SheetJS / window.print), así que no hay forma de auditarlos
    // desde aquí sin agregar una llamada AJAX nueva en esas pantallas.
    public function registrarExportacion($reporte, $idUsuario)
    {
        $sql = "INSERT INTO auditoria (modulo, accion, id_usuario, datos_nuevos, detalle)
                VALUES ('reportes', 'EXPORTAR', $1, $2, $3)";

        $datosNuevos = json_encode(['reporte' => $reporte]);
        $detalle     = 'Exportación completa a Excel del reporte "' . $reporte . '"';

        return $this->insert($sql, [$idUsuario, $datosNuevos, $detalle]);
    }

    public function modulosDisponibles()
    {
        return $this->selectAll(
            // id_modulo 13 (Territorio priorizado) está preparado pero sin
            // Controller/View propios todavía; id_modulo 5 es el propio
            // módulo "Auditoría", que no tiene una regla en
            // $reglasPorModulo (no hay una tabla "auditoria de la
            // auditoría") así que filtrar por él siempre da 0 resultados:
            // no tiene sentido ofrecerlo como opción de filtro.
            "SELECT id_modulo, nombre
             FROM modulo
             WHERE id_modulo NOT IN (5, 13)
             ORDER BY nombre"
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