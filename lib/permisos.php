<?php

// ============================================================
// Permisos reales por módulo, según el rol de la sesión (tabla
// rol_permiso), en vez del selector de rol de mentira
// (localStorage "auxiliar"/"coordinador") que usaban algunos
// módulos. Se usa desde View/partials/footer.php: la vista declara
// $moduloPermisos = 'Nombre del módulo' (tal como está en la tabla
// "modulo") ANTES de incluir footer.php, y footer.php expone el
// resultado como window.SIG_PERMISOS para que el JS del módulo lo
// use en vez de PERMISOS/getRole().
// ============================================================

require_once __DIR__ . '/../Model/Roles/RolesModel.php';

// Devuelve ['ver'=>bool,'consultar'=>bool,'crear'=>bool,'editar'=>bool,
// 'inhabilitar'=>bool,'exportar'=>bool] para el rol de la sesión actual
// sobre $nombreModulo. Si no hay sesión o rol, todo queda en false (no
// se asume ningún permiso por defecto).
function sigPermisosDeModulo($nombreModulo)
{
    $vacio = [
        'ver' => false, 'consultar' => false, 'crear' => false,
        'editar' => false, 'inhabilitar' => false, 'exportar' => false,
    ];

    $idRol = $_SESSION['id_rol'] ?? null;
    if (!$idRol || $nombreModulo === '' || $nombreModulo === null) {
        return $vacio;
    }

    try {
        $modelo = new RolesModel();
        return $modelo->accionesDeModulo($idRol, $nombreModulo);
    } catch (Throwable $e) {
        error_log('Error consultando permisos de módulo "' . $nombreModulo . '": ' . $e->getMessage());
        return $vacio;
    }
}

// ------------------------------------------------------------
// Exigir permiso DEL LADO DEL SERVIDOR, no solo ocultar el botón.
// Se llama al principio de cada método de Controller que crea, edita,
// inhabilita o exporta algo, ANTES de tocar la base de datos:
//
//   sigExigirPermiso('Zoocriaderos', 'crear');
//
// Si el rol de la sesión no tiene ese permiso (o no hay sesión), corta
// la petición con un JSON de error 403 y termina ahí mismo — igual que
// si el usuario hubiera manipulado el HTML para mostrar un botón que
// la interfaz le había ocultado.
// ------------------------------------------------------------
function sigExigirPermiso($nombreModulo, $nombreAccion)
{
    $permisos = sigPermisosDeModulo($nombreModulo);
    $clave = mb_strtolower($nombreAccion, 'UTF-8');

    if (empty($permisos[$clave])) {
        jsonResponse([
            'ok' => false,
            'message' => 'No tienes permiso de "' . $nombreAccion . '" en el módulo "' . $nombreModulo . '".',
        ], 403);
    }
}
