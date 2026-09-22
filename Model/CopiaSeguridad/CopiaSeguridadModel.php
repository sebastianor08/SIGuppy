<?php

include_once __DIR__ . '/../MasterModel.php';

// ============================================================
// Modelo del módulo Copia de Seguridad.
// Tabla: copia_seguridad_historial (ver Database/copia_seguridad_historial.sql)
// El archivo .sql en sí NO se guarda en la base de datos: vive en
// disco (BACKUP_DIR, ver lib/conf/backup_conf.php). Aquí solo se
// guarda el registro de auditoría de cada operación.
// ============================================================
class CopiaSeguridadModel extends MasterModel {

    // ---------- Metadatos de la base de datos ----------

    // Tamaño real de BD_Dengue_SIGuppy en bytes (pg_database_size).
    public function tamanoBDBytes() {
        return $this->selectValue("SELECT pg_database_size(current_database())");
    }

    public function tamanoBDLegible() {
        $bytes = (float) $this->tamanoBDBytes();
        $unidades = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($unidades) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, $i === 0 ? 0 : 2) . ' ' . $unidades[$i];
    }

    // ---------- Historial / auditoría ----------

    public function registrarOperacion($tipoOperacion, $nombreArchivo, $idUsuario, $usuarioNombre, $estado, $detalle = null) {
        return $this->insert(
            "INSERT INTO copia_seguridad_historial
                (tipo_operacion, nombre_archivo, id_usuario, usuario_nombre, estado, detalle)
             VALUES ($1, $2, $3, $4, $5, $6)",
            [$tipoOperacion, $nombreArchivo, $idUsuario, $usuarioNombre, $estado, $detalle]
        );
    }

    public function historial($limite = 50) {
        return $this->selectAll(
            "SELECT id_historial, fecha_hora, tipo_operacion, nombre_archivo,
                    usuario_nombre, estado, detalle
             FROM copia_seguridad_historial
             ORDER BY fecha_hora DESC
             LIMIT $1",
            [(int) $limite]
        );
    }

    public function ultimoBackupExitoso() {
        return $this->selectOne(
            "SELECT fecha_hora, nombre_archivo
             FROM copia_seguridad_historial
             WHERE tipo_operacion IN ('descarga', 'automatica') AND estado = 'exito'
             ORDER BY fecha_hora DESC
             LIMIT 1"
        );
    }
}
