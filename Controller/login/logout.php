<?php
// Controller/login/logout.php
// Cierra la sesión del usuario y lo regresa al login.

session_start();

// Vacía todas las variables de sesión (id_usuario, usuario, id_rol, nombre_rol...)
$_SESSION = [];

// Si se usa cookie de sesión, la expira también en el navegador
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destruye la sesión en el servidor
session_destroy();

// Redirige al login
header("Location: ../../View/login/login.php");
exit();