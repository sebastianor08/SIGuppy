<?php

include_once '../Model/Roles/RolesModel.php';

// ============================================================
// Controlador del módulo Roles. Responde solo JSON:
//   Web/ajax.php?modulo=Roles&controlador=Roles&funcion=permisos&rol=...&nombreModulo=...
//
// Esta es la conexión que faltaba entre la pantalla "Roles y
// Permisos" (que escribe en la tabla rol_permiso) y las vistas de
// Depósitos / Tipos de Depósito / Actividades (que antes decidían
// qué botones mostrar con un objeto de permisos fijo en el JS,
// ignorando por completo lo que el usuario configuraba aquí).
// ============================================================
class RolesController
{
    // GET: ?rol=auxiliar&nombreModulo=Terreno
    // (se usa "nombreModulo" y no "modulo" para no chocar con el
    // parámetro "modulo" que ya usa Web/ajax.php para elegir el
    // controlador)
    public function permisos()
    {
        $obj = new RolesModel();

        $rol = trim($_GET['rol'] ?? '');
        $nombreModulo = trim($_GET['nombreModulo'] ?? '');

        if ($rol === '' || $nombreModulo === '') {
            jsonResponse([
                'ok' => false,
                'message' => 'Debe indicar el rol y el módulo a consultar.'
            ], 422);
        }

        jsonResponse([
            'ok' => true,
            'data' => $obj->permisosPorNombre($rol, $nombreModulo)
        ]);
    }
}
