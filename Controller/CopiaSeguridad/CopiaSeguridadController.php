<?php

include_once __DIR__ . '/../../Model/CopiaSeguridad/CopiaSeguridadModel.php';
include_once __DIR__ . '/../../lib/conf/backup_conf.php';

// ============================================================
// Controlador del módulo Copia de Seguridad.
// Se llama por Web/ajax.php:
//   ?modulo=CopiaSeguridad&controlador=CopiaSeguridad&funcion=...
//
// "descargar" y "descargarArchivo" son la excepción: en vez de
// jsonResponse() envían el .sql como archivo para que el navegador
// lo descargue (por eso el JS los abre con window.location, no fetch).
// ============================================================
class CopiaSeguridadController {

    private $ultimoErrorProceso = '';

    // ---------- Tarjetas superiores + estado de conexión ----------
    public function estado() {
        $obj = new CopiaSeguridadModel();
        $ultimo = $obj->ultimoBackupExitoso();

        jsonResponse([
            'ok' => true,
            'data' => [
                // Si esta línea se ejecuta, ya se abrió conexión a Postgres.
                'db_online'          => true,
                'tamano_bd'          => $obj->tamanoBDLegible(),
                'ultimo_backup'      => $ultimo['fecha_hora'] ?? null,
                'cantidad_respaldos' => count($this->listarArchivosBackup()),
            ],
        ]);
    }

    public function historial() {
        $obj = new CopiaSeguridadModel();
        jsonResponse(['ok' => true, 'data' => $obj->historial(50)]);
    }

    // Lista de .sql disponibles en BACKUP_DIR (para el selector del modal Restaurar)
    public function archivos() {
        $archivos = array_map(function ($ruta) {
            $nombre = basename($ruta);
            if (strpos($nombre, '_auto_') !== false) {
                $tipo = 'automatico';
            } elseif (strpos($nombre, 'subido_') === 0) {
                $tipo = 'subido';
            } else {
                $tipo = 'manual';
            }
            return [
                'nombre' => $nombre,
                'tipo'   => $tipo,
                'tamano' => filesize($ruta),
                'fecha'  => date('Y-m-d H:i:s', filemtime($ruta)),
            ];
        }, $this->listarArchivosBackup());

        jsonResponse(['ok' => true, 'data' => $archivos]);
    }

    private function listarArchivosBackup() {
        if (!is_dir(BACKUP_DIR)) {
            return [];
        }
        $archivos = glob(BACKUP_DIR . '*.sql') ?: [];

        // Se ordena por la fecha real del archivo (filemtime), de más
        // reciente a más antiguo. Antes se usaba rsort() sobre el nombre,
        // y como "..._auto_2026..." es alfabéticamente mayor que
        // "..._2026...", todos los automáticos salían primero y los
        // directos después, sin importar la fecha.
        usort($archivos, function ($a, $b) {
            $diferencia = filemtime($b) - filemtime($a);
            return $diferencia !== 0 ? $diferencia : strcmp(basename($b), basename($a));
        });
        return $archivos;
    }

    // ---------- Generar un respaldo nuevo Y entregarlo como descarga ----------
    // Se invoca con window.location.href (no fetch): el navegador debe
    // recibir el archivo, no una respuesta JSON.
    public function descargar() {
        sigExigirPermiso('Copia de seguridad', 'exportar');
        $obj = new CopiaSeguridadModel();
        $archivo = $this->generarDump();

        if ($archivo === false) {
            $obj->registrarOperacion('descarga', null, $this->idUsuarioActual(), $this->nombreUsuarioActual(), 'error', $this->ultimoErrorProceso);
            http_response_code(500);
            header('Content-Type: text/plain; charset=utf-8');
            echo "No se pudo generar la copia de seguridad.\n\n" . $this->ultimoErrorProceso .
                 "\n\nRevisa que la ruta configurada en PG_BIN_DIR (lib/conf/backup_conf.php) " .
                 "apunte a la carpeta bin de tu instalación de PostgreSQL.";
            exit;
        }

        $obj->registrarOperacion('descarga', basename($archivo), $this->idUsuarioActual(), $this->nombreUsuarioActual(), 'exito');

        $this->enviarArchivo($archivo);
    }

    // Descarga un respaldo YA existente (fila del historial o de la lista de archivos)
    public function descargarArchivo() {
        sigExigirPermiso('Copia de seguridad', 'exportar');
        $nombre = basename($_GET['archivo'] ?? '');
        $ruta = BACKUP_DIR . $nombre;

        if ($nombre === '' || pathinfo($nombre, PATHINFO_EXTENSION) !== 'sql' || !file_exists($ruta)) {
            http_response_code(404);
            header('Content-Type: text/plain; charset=utf-8');
            echo "Ese respaldo ya no está disponible en el servidor (puede haberse eliminado por la política de retención).";
            exit;
        }

        $this->enviarArchivo($ruta);
    }

    private function enviarArchivo($ruta) {
        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="' . basename($ruta) . '"');
        header('Content-Length: ' . filesize($ruta));
        header('Cache-Control: no-cache, must-revalidate');
        readfile($ruta);
        exit;
    }

    // Corre pg_dump sobre BD_Dengue_SIGuppy y devuelve la ruta del .sql generado
    // (o false si falló; el detalle queda en $this->ultimoErrorProceso).
    private function generarDump() {
        if (!is_dir(BACKUP_DIR) && !mkdir(BACKUP_DIR, 0775, true) && !is_dir(BACKUP_DIR)) {
            $this->ultimoErrorProceso = 'No se pudo crear la carpeta de respaldos: ' . BACKUP_DIR;
            return false;
        }

        require __DIR__ . '/../../lib/conf/conf.php'; // trae $host, $user, $password, $database, $port

        $nombreArchivo = strtolower($database) . '_' . date('Ymd_His') . '.sql';
        $rutaDestino   = BACKUP_DIR . $nombreArchivo;
        $pgDump        = PG_BIN_DIR . 'pg_dump.exe';

        putenv('PGPASSWORD=' . $password);

        $comando = escapeshellarg($pgDump)
            . ' -h ' . escapeshellarg($host)
            . ' -p ' . escapeshellarg($port)
            . ' -U ' . escapeshellarg($user)
            . ' -F p'                                   // formato de texto plano (.sql legible)
            . ' -f ' . escapeshellarg($rutaDestino)
            . ' ' . escapeshellarg($database);

        exec($comando . ' 2>&1', $salida, $codigo);
        putenv('PGPASSWORD'); // limpiar la variable de entorno apenas termina

        if ($codigo !== 0 || !file_exists($rutaDestino) || filesize($rutaDestino) === 0) {
            $this->ultimoErrorProceso = implode("\n", $salida);
            error_log('Error pg_dump (CopiaSeguridad): ' . $this->ultimoErrorProceso);
            @unlink($rutaDestino);
            return false;
        }

        return $rutaDestino;
    }

    // ---------- Restaurar ----------
    // Acepta un respaldo subido por el usuario (multipart) o el nombre
    // de uno que ya está en BACKUP_DIR (seleccionado del historial).
    public function restaurar() {
        sigExigirPermiso('Copia de seguridad', 'editar');
        $obj = new CopiaSeguridadModel();

        if (!empty($_FILES['archivo']['tmp_name'])) {
            $nombreOriginal = basename($_FILES['archivo']['name']);
            if (pathinfo($nombreOriginal, PATHINFO_EXTENSION) !== 'sql') {
                jsonResponse(['ok' => false, 'message' => 'Solo se aceptan archivos con extensión .sql'], 422);
            }
            if (!is_dir(BACKUP_DIR)) { mkdir(BACKUP_DIR, 0775, true); }

            $nombreArchivo = 'subido_' . date('Ymd_His') . '_' . $nombreOriginal;
            $rutaArchivo   = BACKUP_DIR . $nombreArchivo;

            if (!move_uploaded_file($_FILES['archivo']['tmp_name'], $rutaArchivo)) {
                jsonResponse(['ok' => false, 'message' => 'No se pudo recibir el archivo subido.'], 500);
            }
        } else {
            $nombreArchivo = basename($_POST['archivo_existente'] ?? '');
            $rutaArchivo   = BACKUP_DIR . $nombreArchivo;

            if ($nombreArchivo === '' || pathinfo($nombreArchivo, PATHINFO_EXTENSION) !== 'sql' || !file_exists($rutaArchivo)) {
                jsonResponse(['ok' => false, 'message' => 'Debes elegir un respaldo existente o subir un archivo .sql.'], 422);
            }
        }

        require __DIR__ . '/../../lib/conf/conf.php';

        $psql = PG_BIN_DIR . 'psql.exe';
        putenv('PGPASSWORD=' . $password);

        $comando = escapeshellarg($psql)
            . ' -h ' . escapeshellarg($host)
            . ' -p ' . escapeshellarg($port)
            . ' -U ' . escapeshellarg($user)
            . ' -d ' . escapeshellarg($database)
            . ' -v ON_ERROR_STOP=1'
            . ' -f ' . escapeshellarg($rutaArchivo);

        exec($comando . ' 2>&1', $salida, $codigo);
        putenv('PGPASSWORD');

        $estado  = ($codigo === 0) ? 'exito' : 'error';
        // Solo se guardan las últimas líneas: un script largo puede
        // generar cientos de líneas de salida y no todas son útiles.
        $detalle = ($codigo === 0) ? null : implode("\n", array_slice($salida, -25));

        $obj->registrarOperacion('restauracion', $nombreArchivo, $this->idUsuarioActual(), $this->nombreUsuarioActual(), $estado, $detalle);

        if ($estado === 'error') {
            jsonResponse(['ok' => false, 'message' => 'La restauración terminó con errores. Revisa el detalle en el historial.'], 500);
        }

        jsonResponse(['ok' => true, 'message' => 'Base de datos restaurada correctamente desde "' . $nombreArchivo . '".']);
    }

    // ---------- Usuario que ejecuta la acción ----------
    // El login (Controller/login/login_process.php) guarda en la sesión
    // 'id_usuario' y 'usuario' (nombre completo). Antes se leían 'id' y
    // 'nombre', que nunca existen, y por eso "Ejecutado por" siempre
    // decía "Sistema" y id_usuario quedaba en NULL.
    private function idUsuarioActual() {
        return isset($_SESSION['id_usuario']) ? (int) $_SESSION['id_usuario'] : null;
    }

    private function nombreUsuarioActual() {
        return $_SESSION['usuario'] ?? 'Sistema';
    }
}
