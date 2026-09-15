<?php
session_start();
include_once 'lib/conf/conexion.php'; // Tu conexión básica ($con = conectar();)

$con = conectar();

// 1. Recibir y limpiar variables
$correo = isset($_POST['correo']) ? trim($_POST['correo']) : '';
$contrasena = isset($_POST['contrasena']) ? trim($_POST['contrasena']) : '';

// 2. Validar que no vengan vacíos
if (empty($correo) || empty($contrasena)) {
    header("Location: login.php?error=vacio");
    exit();
}

// 3. Consulta básica a la tabla 'usuario' de tu BD (BD_Dengue_SIGuppy)
// Nota: Traemos los datos asociando también el rol si es necesario
$sql = "SELECT id_usuario, id_rol, nombre, apellido, correo, contrasena, estado 
        FROM usuario 
        WHERE correo = :correo";

$stmt = $con->prepare($sql);
$stmt->bindParam(':correo', $correo);
$stmt->execute();

$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

// 4. Verificar si el usuario existe
if ($usuario) {

    // Validar estado de la cuenta (1 = Activo, 0 = Inactivo)
    if ($usuario['estado'] != 1) {
        header("Location: login.php?error=inactivo");
        exit();
    }

    // Comprobar la contraseña 
    // (Usa password_verify() si usas contraseñas cifradas, o comparación directa para pruebas)
    if ($contrasena === $usuario['contrasena'] || password_verify($contrasena, $usuario['contrasena'])) {
        
        // Guardar sesión del usuario
        $_SESSION['id_usuario'] = $usuario['id_usuario'];
        $_SESSION['nombre_completo'] = $usuario['nombre'] . ' ' . $usuario['apellido'];
        $_SESSION['id_rol'] = $usuario['id_rol'];
        $_SESSION['correo'] = $usuario['correo'];

        // Redireccionar al panel principal
        header("Location: view/usuarios.controller.php");
        exit();

    } else {
        // Contraseña incorrecta
        header("Location: login.php?error=invalid");
        exit();
    }

} else {
    // Usuario no existe
    header("Location: login.php?error=invalid");
    exit();
}
?>