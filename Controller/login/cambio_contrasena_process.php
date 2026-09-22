<?php
ob_start();
session_start();
require_once '../../Model/MasterModel.php';
require_once '../../lib/validaciones.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = trim($_POST['token'] ?? '');

    if ($token === '') {
        header("Location: ../../View/login/recuperar.php?status=token_invalido");
        exit();
    }

    $nueva = trim($_POST['nueva_contrasena'] ?? '');
    $confirmar = trim($_POST['confirmar_contrasena'] ?? '');

    if ($nueva !== $confirmar) {
        header("Location: ../../View/login/cambio_contrasena.php?token=" . urlencode($token) . "&error=no_coinciden");
        exit();
    }

    $errorClave = validarContrasena($nueva);
    if ($errorClave !== null) {
        header("Location: ../../View/login/cambio_contrasena.php?token=" . urlencode($token) . "&error=requisitos&mensaje=" . urlencode($errorClave));
        exit();
    }

    try {
        $masterModel = new MasterModel();

        // Se revalida el token aquí también (existencia y vigencia): pudo
        // vencerse justo entre que se mostró el formulario y que se envió.
        $usuario = $masterModel->selectOne(
            "SELECT id_usuario FROM usuario WHERE token_recuperacion = $1 AND token_expira > NOW()",
            [$token]
        );

        if (!$usuario) {
            header("Location: ../../View/login/recuperar.php?status=token_invalido");
            exit();
        }

        $idUsuario = $usuario['id_usuario'];
        $hash = password_hash($nueva, PASSWORD_BCRYPT);

        $sql = "UPDATE usuario
                SET contrasena = $1, token_recuperacion = NULL, token_expira = NULL,
                    intentos_fallidos = 0, bloqueo_hasta = NULL,
                    debe_cambiar_contrasena = FALSE
                WHERE id_usuario = $2";
        $masterModel->update($sql, [$hash, $idUsuario]);

        header("Location: ../../View/login/login.php?status=changed");
        exit();

    } catch (Throwable $e) {
        error_log("Error cambiando contraseña: " . $e->getMessage());
        header("Location: ../../View/login/cambio_contrasena.php?token=" . urlencode($token) . "&error=sistema");
        exit();
    }
}