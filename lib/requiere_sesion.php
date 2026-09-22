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

require_once __DIR__ . '/sesion_config.php';

if (empty($_SESSION['id_usuario'])) {
    $basePath = $basePath ?? '../../';
    header('Location: ' . $basePath . 'View/login/login.php');
    exit();
}

// Contraseña temporal (número de documento) todavía sin cambiar: no se
// deja ver ningún módulo del sistema hasta que la actualice, ni siquiera
// entrando por una URL directa.
if (!empty($_SESSION['debe_cambiar_contrasena'])) {
    $basePath = $basePath ?? '../../';
    header('Location: ' . $basePath . 'View/login/cambio_obligatorio.php');
    exit();
}

// Cierre de sesión por inactividad (15 minutos sin actividad registrada
// ni en carga de página ni en peticiones AJAX). Se revisa ANTES de
// mostrar cualquier contenido del módulo.
if (sigSesionInactivaVencida()) {
    sigCerrarPorInactividad();
    $basePath = $basePath ?? '../../';
    header('Location: ' . $basePath . 'View/login/login.php?motivo=inactividad');
    exit();
}

sigRegistrarActividad();
