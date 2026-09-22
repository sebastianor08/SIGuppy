<?php

// ============================================================
// Configuración central del tiempo de inactividad de la sesión.
// Un único lugar para el valor en segundos, usado tanto por
// lib/requiere_sesion.php (carga de páginas) como por
// lib/helpers.php (peticiones AJAX vía Web/ajax.php) y expuesto
// también al JavaScript de inactividad (siguppys-inactividad.js)
// a través de $_SESSION/relojes del navegador.
// ============================================================

if (!defined('SIG_INACTIVIDAD_SEGUNDOS')) {
    define('SIG_INACTIVIDAD_SEGUNDOS', 15 * 60); // 15 minutos
}

// true si la sesión existe pero lleva más de SIG_INACTIVIDAD_SEGUNDOS
// sin actividad registrada.
function sigSesionInactivaVencida()
{
    if (empty($_SESSION['id_usuario'])) {
        return false;
    }
    if (empty($_SESSION['sig_ultima_actividad'])) {
        // Sesión existente de antes de este cambio, o primera petición:
        // se concede el beneficio de la duda y se marca actividad ahora.
        return false;
    }
    return (time() - (int) $_SESSION['sig_ultima_actividad']) > SIG_INACTIVIDAD_SEGUNDOS;
}

function sigRegistrarActividad()
{
    $_SESSION['sig_ultima_actividad'] = time();
}

// Cierra la sesión del servidor por inactividad (no borra la cookie
// explícitamente aquí: quien llama a esta función decide qué hacer
// después, por ejemplo redirigir con un mensaje).
function sigCerrarPorInactividad()
{
    $_SESSION = [];
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
}
