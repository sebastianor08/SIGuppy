<?php
ob_start();
session_start();
require_once '../../Model/MasterModel.php';
require_once '../../lib/validaciones.php';

if (empty($_SESSION['id_usuario']) || empty($_SESSION['debe_cambiar_contrasena'])) {
    header("Location: ../../View/login/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idUsuario = $_SESSION['id_usuario'];
    $nueva = trim($_POST['nueva_contrasena'] ?? '');
    $confirmar = trim($_POST['confirmar_contrasena'] ?? '');

    if ($nueva !== $confirmar) {
        header("Location: ../../View/login/cambio_obligatorio.php?error=no_coinciden");
        exit();
    }

    $errorClave = validarContrasena($nueva);
    if ($errorClave !== null) {
        header("Location: ../../View/login/cambio_obligatorio.php?error=requisitos&mensaje=" . urlencode($errorClave));
        exit();
    }

    try {
        $masterModel = new MasterModel();

        // La nueva contraseña no puede ser el mismo número de documento
        // que se le asignó como contraseña temporal: forzaría a repetir
        // este mismo paso en el siguiente inicio de sesión.
        $usuario = $masterModel->selectOne(
            "SELECT documento FROM usuario WHERE id_usuario = $1",
            [$idUsuario]
        );
        if ($usuario && strcasecmp((string) $usuario['documento'], $nueva) === 0) {
            header("Location: ../../View/login/cambio_obligatorio.php?error=igual");
            exit();
        }

        $hash = password_hash($nueva, PASSWORD_BCRYPT);
        $sql = "UPDATE usuario
                SET contrasena = $1, debe_cambiar_contrasena = FALSE,
                    intentos_fallidos = 0, bloqueo_hasta = NULL
                WHERE id_usuario = $2";
        $masterModel->update($sql, [$hash, $idUsuario]);

        $_SESSION['debe_cambiar_contrasena'] = false;

        header("Location: ../../Web/index.php");
        exit();

    } catch (Throwable $e) {
        error_log("Error en cambio obligatorio de contraseña: " . $e->getMessage());
        header("Location: ../../View/login/cambio_obligatorio.php?error=sistema");
        exit();
    }
}
