<?php
ob_start();
session_start();

// Carga el UsuariosModel (este ya incluye a MasterModel)
require_once __DIR__ . '/../../Model/Usuario/UsuariosModel.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo'] ?? '');
    $contrasena = trim($_POST['contrasena'] ?? '');

    if (empty($correo) || empty($contrasena)) {
        header("Location: ../../View/login/login.php?error=empty");
        exit();
    }

    try {
        $usuariosModel = new UsuariosModel();
        $usuario = $usuariosModel->obtenerUsuarioPorCorreo($correo);

        if (!$usuario) {
            header("Location: ../../View/login/login.php?error=invalid");
            exit();
        }

        if ($usuario['estado'] == 0 || strtolower((string)$usuario['estado']) === 'inactivo') {
            header("Location: ../../View/login/login.php?error=inactive");
            exit();
        }

        if (password_verify($contrasena, $usuario['contrasena']) || $contrasena === $usuario['contrasena']) {
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['usuario']    = $usuario['nombre'] . ' ' . $usuario['apellido'];
            $_SESSION['id_rol']     = $usuario['id_rol'];
            $_SESSION['nombre_rol'] = $usuario['nombre_rol'];

            header("Location: ../../Web/index.php");
            exit();
        } else {
            header("Location: ../../View/login/login.php?error=invalid");
            exit();
        }

    } catch (Exception $e) {
        error_log("Error en Login: " . $e->getMessage());
        header("Location: ../../View/login/login.php?error=system");
        exit();
    }
}