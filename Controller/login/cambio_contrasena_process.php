<?php
session_start();
require_once '../../model/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nueva = trim($_POST['nueva_contrasena'] ?? '');
    $confirmar = trim($_POST['confirmar_contrasena'] ?? '');
    $id_usuario = $_SESSION['id_recuperar'] ?? null;

    if (!$id_usuario) {
        header("Location: ../../login.php");
        exit();
    }

    // Validar que coincidan las dos contraseñas
    if ($nueva !== $confirmar || empty($nueva)) {
        header("Location: ../../cambio_contrasena.php?error=no_coinciden");
        exit();
    }

    try {
        // Encriptar la contraseña con password_hash (Buena práctica de seguridad)
        $passwordHash = password_hash($nueva, PASSWORD_BCRYPT);

        // Actualizar en PostgreSQL y limpiar tokens e intentos
        $sql = "UPDATE usuario 
                SET contrasena = :contrasena, token_recuperacion = NULL, intentos_fallidos = 0, bloqueo_hasta = NULL 
                WHERE id_usuario = :id";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':contrasena' => $passwordHash,
            ':id' => $id_usuario
        ]);

        // Destruir la variable de sesión temporal de recuperación
        unset($_SESSION['id_recuperar']);

        // Redirigir al login con éxito
        header("Location: ../../login.php?status=changed");
        exit();

    } catch (PDOException $e) {
        error_log("Error: " . $e->getMessage());
        header("Location: ../../cambio_contrasena.php?error=no_coinciden");
        exit();
    }
}