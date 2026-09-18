<?php
// Validar que solo un Administrador o Super Administrador realice estas acciones
if (!isset($_SESSION['nombre_rol']) || ($_SESSION['nombre_rol'] !== 'Administrador' && $_SESSION['nombre_rol'] !== 'Super Administrador')) {
    header("Location: index.php?error=acceso_denegado");
    exit();
}

$accion = $_GET['accion'] ?? '';

// ACCIÓN 1: Crear Usuario
if ($accion === 'crear' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $id_rol = $_POST['id_rol'] ?? '';
    $contrasena = trim($_POST['contrasena'] ?? '');

    // Validaciones de campos
    if (empty($nombre) || empty($apellido) || empty($correo) || empty($id_rol) || empty($contrasena)) {
        header("Location: index.php?modulo=usuarios&error=campos_vacios");
        exit();
    }

    // Validar si el correo ya existe
    $checkSql = "SELECT id_usuario FROM usuario WHERE correo = :correo";
    $checkStmt = $con->prepare($checkSql);
    $checkStmt->execute([':correo' => $correo]);

    if ($checkStmt->fetch()) {
        header("Location: index.php?modulo=usuarios&error=correo_existente");
        exit();
    }

    // Insertar en la BD
    $passHash = password_hash($contrasena, PASSWORD_BCRYPT);
    $insertSql = "INSERT INTO usuario (nombre, apellido, correo, id_rol, contrasena, estado) 
                  VALUES (:nombre, :apellido, :correo, :id_rol, :pass, 1)";
    $stmt = $con->prepare($insertSql);
    $stmt->execute([
        ':nombre' => $nombre,
        ':apellido' => $apellido,
        ':correo' => $correo,
        ':id_rol' => $id_rol,
        ':pass' => $passHash
    ]);

    header("Location: index.php?modulo=usuarios&status=creado");
    exit();
}

// ACCIÓN 2: Guardar Edición de Usuario
if ($accion === 'actualizar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_usuario = $_POST['id_usuario'] ?? '';
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $id_rol = $_POST['id_rol'] ?? '';
    $estado = isset($_POST['activa']) ? 1 : 0;

    $updateSql = "UPDATE usuario 
                  SET nombre = :nombre, apellido = :apellido, telefono = :telefono, correo = :correo, id_rol = :id_rol, estado = :estado 
                  WHERE id_usuario = :id";
    $stmt = $con->prepare($updateSql);
    $stmt->execute([
        ':nombre' => $nombre,
        ':apellido' => $apellido,
        ':telefono' => $telefono,
        ':correo' => $correo,
        ':id_rol' => $id_rol,
        ':estado' => $estado,
        ':id' => $id_usuario
    ]);

    header("Location: index.php?modulo=usuarios&status=actualizado");
    exit();
}

// ACCIÓN 3: Activar / Desactivar Estado
if ($accion === 'cambiar_estado') {
    $id_usuario = $_GET['id'] ?? '';
    $nuevo_estado = $_GET['estado'] ?? 0;

    $sql = "UPDATE usuario SET estado = :estado WHERE id_usuario = :id";
    $stmt = $con->prepare($sql);
    $stmt->execute([':estado' => $nuevo_estado, ':id' => $id_usuario]);

    header("Location: index.php?modulo=usuarios&status=estado_cambiado");
    exit();
}
?>