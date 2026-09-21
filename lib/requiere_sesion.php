<?php

// ============================================================
// Exige sesión iniciada. Cualquier página que lo incluya (al principio,
// antes de imprimir nada) deja de ser accesible por URL directa sin
// haber pasado por el login: si no hay $_SESSION['id_usuario'], se
// redirige a login.php en vez de mostrar el contenido del módulo.
//
// $basePath debe venir ya definido por quien incluye este archivo (la
// misma variable que usan las vistas para sus assets), porque la
// profundidad de carpetas cambia según la página:
//   - View/Zoocriadero/zoocriaderos.php -> $basePath = '../../'
//   - Web/index.php                     -> $basePath = '../'
// Si no viene definido, se asume '../../' (el caso más común).
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['id_usuario'])) {
    $basePath = $basePath ?? '../../';
    header('Location: ' . $basePath . 'View/login/login.php');
    exit();
}
