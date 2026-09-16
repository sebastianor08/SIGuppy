<?php
session_start();
require_once '../../model/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = trim($_POST['codigo'] ?? '');

    if (empty($codigo)) {
        header("Location: ../../validar_codigo.php?error=codigo_invalido");
        exit();
    }

    // Verificar si el token/código existe en PostgreSQL
    $sql = "SELECT id_usuario FROM usuario WHERE token_recuperacion = :codigo";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([':codigo' => $codigo]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        // Guardamos temporalmente el ID de usuario validado en la sesión
        $_SESSION['id_recuperar'] = $usuario['id_usuario'];
        header("Location: ../../cambio_contrasena.php");
        exit();
    } else {
        header("Location: ../../validar_codigo.php?error=codigo_invalido");
        exit();
    }
}