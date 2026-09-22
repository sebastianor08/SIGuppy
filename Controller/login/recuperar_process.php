<?php
ob_start();
session_start();

// Carga el MasterModel (conexión nativa pgsql, sin PDO) y el envío de correo
require_once '../../Model/MasterModel.php';
require_once '../../lib/Mailer.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo'] ?? '');

    if (empty($correo) || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../../View/login/recuperar.php?status=notfound");
        exit();
    }

    try {
        $masterModel = new MasterModel();

        $sql = "SELECT id_usuario, nombre, apellido, correo FROM usuario WHERE LOWER(correo) = LOWER($1)";
        $usuario = $masterModel->selectOne($sql, [$correo]);

        if (!$usuario) {
            header("Location: ../../View/login/recuperar.php?status=notfound");
            exit();
        }

        // Token de recuperación: 64 caracteres hexadecimales generados con
        // random_bytes (criptográficamente seguro), no un código de 6
        // dígitos. Válido por 30 minutos (reutiliza token_recuperacion /
        // token_expira, que ya existían para el código anterior).
        $codigo = bin2hex(random_bytes(32));
        $minutosExpiracion = 30;
        $codigoExpira = date('Y-m-d H:i:s', strtotime("+{$minutosExpiracion} minutes"));

        $sqlUpdate = "UPDATE usuario SET token_recuperacion = $1, token_expira = $2 WHERE id_usuario = $3";
        $masterModel->update($sqlUpdate, [$codigo, $codigoExpira, $usuario['id_usuario']]);

        // Enlace absoluto: se arma a partir del host y la ruta con la que
        // el navegador llegó a este script, para que funcione sin importar
        // el dominio o subcarpeta donde esté instalado el sistema.
        $protocolo    = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host         = $_SERVER['HTTP_HOST'] ?? 'localhost';
        // Este script vive en <raíz>/Controller/login/recuperar_process.php
        $raizProyecto = str_replace('\\', '/', dirname(dirname(dirname($_SERVER['SCRIPT_NAME']))));
        $raizProyecto = rtrim($raizProyecto, '/');
        $baseUrl      = $protocolo . '://' . $host . $raizProyecto . '/';
        $enlace       = $baseUrl . 'View/login/cambio_contrasena.php?token=' . $codigo;

        $nombreCompleto = $usuario['nombre'] . ' ' . $usuario['apellido'];
        $enviado = enviarEnlaceRecuperacion($usuario['correo'], $nombreCompleto, $enlace, $minutosExpiracion);

        if (!$enviado) {
            header("Location: ../../View/login/recuperar.php?status=system");
            exit();
        }

        header("Location: ../../View/login/recuperar.php?status=enviado");
        exit();

    } catch (Exception $e) {
        error_log("Error en recuperación de contraseña: " . $e->getMessage());
        header("Location: ../../View/login/recuperar.php?status=system");
        exit();
    }
}
