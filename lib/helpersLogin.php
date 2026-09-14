<?php

    // Se exceptúa el propio módulo Acceso (login/logout): si no, esta
    // verificación redirige a login.php ANTES de que el usuario pueda
    // enviar sus credenciales, porque bloquea también la petición
    // que intenta iniciar sesión.
    $moduloActual = isset($_GET['modulo']) ? strtolower($_GET['modulo']) : '';
    if($moduloActual !== 'acceso'){
        if(!isset($_SESSION["auth"]) || $_SESSION["auth"] != "ok") {
            redirect("login.php");
        }
    }

?>