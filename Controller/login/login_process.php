<?php
ob_start();
session_start();

// Carga el MasterModel
require_once '../../Model/MasterModel.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo'] ?? '');
    $contrasena = trim($_POST['contrasena'] ?? '');

    // Validación de campos vacíos
    if (empty($correo) || empty($contrasena)) {
        header("Location: ../../View/login/login.php?error=empty");
        exit();
    }

    try {
        // Instancia del MasterModel
        $masterModel = new MasterModel();

        // Consulta en PostgreSQL con marcadores $1 para pg_query_params
        $sql = "SELECT u.id_usuario, u.nombre, u.apellido, u.correo, u.contrasena, u.estado, u.id_rol, r.nombre_rol 
                FROM usuario u 
                INNER JOIN rol r ON u.id_rol = r.id_rol 
                WHERE LOWER(u.correo) = LOWER($1)";
        
        // Ejecuta la consulta usando selectOne
        $usuario = $masterModel->selectOne($sql, [$correo]);

        // Si no existe el usuario
        if (!$usuario) {
            header("Location: ../../View/login/login.php?error=invalid");
            exit();
        }

        // Validación de estado inactivo
        if ($usuario['estado'] == 0 || strtolower((string)$usuario['estado']) === 'inactivo') {
            header("Location: ../../View/login/login.php?error=inactive");
            exit();
        }

        // Verificación de contraseña
        if (password_verify($contrasena, $usuario['contrasena']) || $contrasena === $usuario['contrasena']) {
            
            // Guardar datos en la sesión
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['usuario']    = $usuario['nombre'] . ' ' . $usuario['apellido'];
            $_SESSION['id_rol']     = $usuario['id_rol'];
            $_SESSION['nombre_rol'] = $usuario['nombre_rol'];

            // Redirección exitosa al panel principal
            header("Location: ../../Web/index.php");
            exit();

        } else {
            // Contraseña incorrecta
            header("Location: ../../View/login/login.php?error=invalid");
            exit();
        }

    } catch (Exception $e) {
        error_log("Error en Login: " . $e->getMessage());
        header("Location: ../../View/login/login.php?error=system");
        exit();
    }
}