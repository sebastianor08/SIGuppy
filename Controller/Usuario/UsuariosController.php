<?php

// Incluir helpers/funciones globales si tu editor no los detecta automáticamente
// require_once __DIR__ . '/../../Web/helpers.php'; 

require_once __DIR__ . '/../Model/Usuario/UsuariosModel.php';

/**
 * Controlador del módulo Usuarios.
 * Responde solo JSON, llamado por Web/ajax.php
 */
class UsuariosController {

    // ---------- Lecturas ----------

    public function listar(): void {
        $obj = new UsuariosModel();
        jsonResponse(['ok' => true, 'data' => $obj->listar()]);
    }

    public function roles(): void {
        $obj = new UsuariosModel();
        jsonResponse(['ok' => true, 'data' => $obj->roles()]);
    }

    // ---------- Escrituras ----------

    public function postCreate(): void {
        $obj  = new UsuariosModel();
        $body = requestJsonBody();

        $datos = $this->validarUsuario($body, $obj, null);

        // La contraseña es obligatoria al crear
        $contrasena = $body['contrasena'] ?? '';
        if (esVacio($contrasena) || mb_strlen((string)$contrasena) < 6) {
            jsonResponse(['ok' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres.'], 422);
        }
        
        $datos['contrasena'] = password_hash((string)$contrasena, PASSWORD_BCRYPT);
        $datos['id_tipodocumento'] = filter_var($body['id_tipodocumento'] ?? 1, FILTER_VALIDATE_INT) ?: 1;

        $id = $obj->crear($datos);
        if ($id === null) {
            jsonResponse(['ok' => false, 'message' => 'No se pudo registrar: ' . $obj->ultimoError()], 500);
        }

        jsonResponse([
            'ok' => true,
            'message' => 'Usuario registrado correctamente.',
            'id_usuario' => (int) $id,
        ], 201);
    }

    public function postUpdate(): void {
        $obj  = new UsuariosModel();
        $body = requestJsonBody();

        $idUsuario = filter_var($body['id_usuario'] ?? null, FILTER_VALIDATE_INT);
        if (!$idUsuario) {
            jsonResponse(['ok' => false, 'message' => 'id_usuario es obligatorio.'], 422);
        }
        if (!$obj->buscar($idUsuario)) {
            jsonResponse(['ok' => false, 'message' => 'El usuario no existe.'], 404);
        }

        $datos = $this->validarUsuario($body, $obj, (int)$idUsuario);

        // Permite actualizar contraseña solo si el cliente la envía opcionalmente
        if (!empty($body['contrasena'])) {
            if (mb_strlen((string)$body['contrasena']) < 6) {
                jsonResponse(['ok' => false, 'message' => 'La nueva contraseña debe tener al menos 6 caracteres.'], 422);
            }
            $datos['contrasena'] = password_hash((string)$body['contrasena'], PASSWORD_BCRYPT);
        }

        if ($obj->actualizar($idUsuario, $datos) === false) {
            jsonResponse(['ok' => false, 'message' => 'No se pudo actualizar: ' . $obj->ultimoError()], 500);
        }

        jsonResponse(['ok' => true, 'message' => 'Usuario actualizado correctamente.']);
    }

    public function postEstado(): void {
        $obj  = new UsuariosModel();
        $body = requestJsonBody();

        $idUsuario = filter_var($body['id_usuario'] ?? null, FILTER_VALIDATE_INT);
        $estado    = isset($body['estado']) ? filter_var($body['estado'], FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE) : null;

        if (!$idUsuario || $estado === null || ($estado !== 0 && $estado !== 1)) {
            jsonResponse(['ok' => false, 'message' => 'Datos incompletos o estado no válido.'], 422);
        }

        if ($obj->cambiarEstado($idUsuario, $estado) === false) {
            jsonResponse(['ok' => false, 'message' => 'No se pudo cambiar el estado.'], 500);
        }

        jsonResponse([
            'ok' => true,
            'message' => $estado === 1 ? 'Usuario activado.' : 'Usuario desactivado.',
        ]);
    }

    // ---------- Validación compartida por create y update ----------
    
    /**
     * @param array $body
     * @param UsuariosModel $obj
     * @param int|null $idExcluir
     * @return array
     */
    private function validarUsuario(array $body, UsuariosModel $obj, ?int $idExcluir): array {
        $nombre   = limpiar($body['nombre'] ?? '');
        $apellido = limpiar($body['apellido'] ?? '');
        $correo   = limpiar($body['correo'] ?? '');
        $telefono = limpiar($body['telefono'] ?? '');
        $idRol    = filter_var($body['id_rol'] ?? null, FILTER_VALIDATE_INT);

        $errores = [
            validarTexto($nombre, 'Nombres', 2, 80),
            validarTexto($apellido, 'Apellidos', 2, 80),
            validarTexto($correo, 'Correo', 5, 120),
            validarTextoOpcional($telefono, 'Teléfono', 30),
        ];

        foreach ($errores as $error) {
            if ($error !== null) {
                jsonResponse(['ok' => false, 'message' => $error], 422);
            }
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            jsonResponse(['ok' => false, 'message' => 'El correo no tiene un formato válido.'], 422);
        }
        if (!$idRol) {
            jsonResponse(['ok' => false, 'message' => 'Debe seleccionar un rol.'], 422);
        }
        if ($obj->existeCorreo($correo, $idExcluir)) {
            jsonResponse(['ok' => false, 'message' => 'Ya existe un usuario registrado con ese correo.'], 422);
        }

        return [
            'nombre'   => $nombre,
            'apellido' => $apellido,
            'correo'   => $correo,
            'telefono' => ($telefono !== '' ? $telefono : null),
            'id_rol'   => $idRol,
        ];
    }
}