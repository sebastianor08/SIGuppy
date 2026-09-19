<?php
ob_start();
session_start();

// Carga el MasterModel (conexión nativa pgsql, sin PDO) y las validaciones
// compartidas (aquí no se carga automático como en Web/ajax.php, así que
// se incluye directo).
require_once '../../Model/MasterModel.php';
require_once '../../lib/validaciones.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idUsuario = $_SESSION['id_recuperar'] ?? null;

    if (!$idUsuario) {
        header("Location: ../../View/login/login.php");
        exit();
    }

    $nueva = trim($_POST['nueva_contrasena'] ?? '');
    $confirmar = trim($_POST['confirmar_contrasena'] ?? '');

    if ($nueva !== $confirmar) {
        header("Location: ../../View/login/cambio_contrasena.php?error=no_coinciden");
        exit();
    }

    // Misma regla que al crear un usuario: mínimo 8 caracteres, una
    // minúscula, una mayúscula y un carácter especial.
    $errorClave = validarContrasena($nueva);
    if ($errorClave !== null) {
        header("Location: ../../View/login/cambio_contrasena.php?error=no_coinciden");
        exit();
    }

    try {
        $masterModel = new MasterModel();
        $hash = password_hash($nueva, PASSWORD_BCRYPT);

        $sql = "UPDATE usuario
                SET contrasena = $1, token_recuperacion = NULL, token_expira = NULL,
                    intentos_fallidos = 0, bloqueo_hasta = NULL
                WHERE id_usuario = $2";
        $masterModel->update($sql, [$hash, $idUsuario]);

        // Ya se usó: se descarta la sesión temporal de recuperación
        unset($_SESSION['id_recuperar']);

        header("Location: ../../View/login/login.php?status=changed");
        exit();

    } catch (Exception $e) {
        error_log("Error cambiando contraseña: " . $e->getMessage());
        header("Location: ../../View/login/cambio_contrasena.php?error=no_coinciden");
        exit();
    }
}
