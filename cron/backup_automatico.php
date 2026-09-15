<?php

// ============================================================
// Respaldo automático diario de BD_Dengue_SIGuppy.
//
// Este script NO se abre desde el navegador: lo ejecuta el
// Programador de tareas de Windows una vez al día, llamando a
// php.exe directamente (ver backup_automatico.bat en esta misma
// carpeta y las instrucciones en LEEME_COPIA_SEGURIDAD.md).
//
// Qué hace, en orden:
//   1) Genera un .sql nuevo con pg_dump en BACKUP_DIR.
//   2) Dependiendo del resultado, lo copia a BACKUP_CLOUD_DIR
//      (la carpeta sincronizada con la nube gratuita), si ya
//      está configurada en lib/conf/backup_conf.php.
//   3) Registra la operación en copia_seguridad_historial, para
//      que aparezca en el módulo web como tipo "Automática".
//   4) Borra del disco local los .sql más viejos que
//      BACKUP_RETENTION_DIAS (el historial de auditoría NO se borra).
// ============================================================

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die('Este script solo puede ejecutarse desde la línea de comandos (Programador de tareas), no desde el navegador.');
}

require __DIR__ . '/../lib/conf/backup_conf.php';
require __DIR__ . '/../lib/conf/conf.php'; // $host, $user, $password, $database, $port
require __DIR__ . '/../Model/CopiaSeguridad/CopiaSeguridadModel.php';

function log_linea($mensaje) {
    echo '[' . date('Y-m-d H:i:s') . "] $mensaje\n";
}

// ---------- 1) Generar el dump ----------
if (!is_dir(BACKUP_DIR)) {
    mkdir(BACKUP_DIR, 0775, true);
}

$nombreArchivo = strtolower($database) . '_auto_' . date('Ymd_His') . '.sql';
$rutaDestino   = BACKUP_DIR . $nombreArchivo;
$pgDump        = PG_BIN_DIR . 'pg_dump.exe';

putenv('PGPASSWORD=' . $password);

$comando = escapeshellarg($pgDump)
    . ' -h ' . escapeshellarg($host)
    . ' -p ' . escapeshellarg($port)
    . ' -U ' . escapeshellarg($user)
    . ' -F p'
    . ' -f ' . escapeshellarg($rutaDestino)
    . ' ' . escapeshellarg($database);

exec($comando . ' 2>&1', $salida, $codigo);
putenv('PGPASSWORD');

$obj = new CopiaSeguridadModel();

if ($codigo !== 0 || !file_exists($rutaDestino) || filesize($rutaDestino) === 0) {
    $detalle = implode("\n", $salida);
    log_linea('ERROR generando el respaldo automático: ' . $detalle);
    @unlink($rutaDestino);
    $obj->registrarOperacion('automatica', null, null, 'Sistema (automático)', 'error', $detalle);
    exit(1);
}

log_linea("Respaldo generado: $nombreArchivo");

// ---------- 2) Copiar a la carpeta sincronizada con la nube ----------
$copiadoNube = null;
if (defined('BACKUP_CLOUD_DIR') && BACKUP_CLOUD_DIR) {
    if (is_dir(BACKUP_CLOUD_DIR)) {
        $copiadoNube = @copy($rutaDestino, rtrim(BACKUP_CLOUD_DIR, '\\/') . DIRECTORY_SEPARATOR . $nombreArchivo);
        log_linea($copiadoNube ? 'Copiado a la carpeta de nube.' : 'No se pudo copiar a la carpeta de nube (revisa la ruta y permisos).');
    } else {
        log_linea('BACKUP_CLOUD_DIR está configurada pero la carpeta no existe: ' . BACKUP_CLOUD_DIR);
    }
} else {
    log_linea('BACKUP_CLOUD_DIR no está configurada todavía: el respaldo solo queda en disco local.');
}

// ---------- 3) Registrar en el historial de auditoría ----------
$obj->registrarOperacion('automatica', $nombreArchivo, null, 'Sistema (automático)', 'exito');

// ---------- 4) Aplicar retención (borrar .sql locales viejos) ----------
if ((int) BACKUP_RETENTION_DIAS > 0) {
    $limite = time() - ((int) BACKUP_RETENTION_DIAS * 86400);
    foreach (glob(BACKUP_DIR . '*.sql') as $archivo) {
        if (filemtime($archivo) < $limite) {
            @unlink($archivo);
            log_linea('Eliminado por retención (' . BACKUP_RETENTION_DIAS . ' días): ' . basename($archivo));
        }
    }
}

log_linea('Proceso de respaldo automático finalizado correctamente.');
exit(0);
