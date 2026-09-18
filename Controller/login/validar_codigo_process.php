<?php
ob_start();
session_start();

// Carga el MasterModel (conexión nativa pgsql, sin PDO)
require_once '../../Model/MasterModel.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = trim($_POST['codigo'] ?? '');

    if ($codigo === '') {
        header("Location: ../../View/login/validar_codigo.php?error=codigo_invalido");
        exit();
    }

    try {
        $masterModel = new MasterModel();

        // El código debe existir y no haber vencido
        $sql = "SELECT id_usuario FROM usuario
                WHERE token_recuperacion = $1 AND token_expira > NOW()";
        $usuario = $masterModel->selectOne($sql, [$codigo]);

        if (!$usuario) {
            header("Location: ../../View/login/validar_codigo.php?error=codigo_invalido");
            exit();
        }

        // Guardamos temporalmente el ID de usuario validado en la sesión
        $_SESSION['id_recuperar'] = $usuario['id_usuario'];
        header("Location: ../../View/login/cambio_contrasena.php");
        exit();

    } catch (Exception $e) {
        error_log("Error validando código de recuperación: " . $e->getMessage());
        header("Location: ../../View/login/validar_codigo.php?error=codigo_invalido");
        exit();
    }
}
