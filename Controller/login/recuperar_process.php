<?php
require_once '../model/conexion.php'; // Tu archivo con la conexión PDO a PostgreSQL

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo']);

    if (empty($correo)) {
        header("Location: ../recuperar.php?status=notfound");
        exit();
    }

    // Consulta segura con PDO para PostgreSQL
    $sql = "SELECT id_usuario FROM usuario WHERE correo = :correo";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([':correo' => $correo]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Generar un token único seguro
        $token = bin2hex(random_bytes(32));
        $expira = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Guardar el token en la base de datos
        $updateSql = "UPDATE usuario SET token_recuperacion = :token, token_expira = :expira WHERE id_usuario = :id";
        $updateStmt = $conexion->prepare($updateSql);
        $updateStmt->execute([
            ':token' => $token,
            ':expira' => $expira,
            ':id' => $user['id_usuario']
        ]);

        // Muestra el modal de confirmación 🎉
        header("Location: ../recuperar.php?status=enviado");
        exit();
    } else {
        header("Location: ../recuperar.php?status=notfound");
        exit();
    }
}