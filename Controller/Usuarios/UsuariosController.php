<?php

include_once '../Model/Usuarios/UsuarioModel.php';

// ============================================================
// Controlador del módulo Gestión de Usuarios. Responde solo JSON,
// así que se llama siempre por Web/ajax.php:
//   Web/ajax.php?modulo=Usuarios&controlador=Usuarios&funcion=lista
//
// Nota: la función postPassword ("Restablecer contraseña") se
// eliminó. La contraseña solo se define al registrar el usuario;
// para cambiarla se usa "¿Olvidó su contraseña?" en el login.
// ============================================================
class UsuariosController
{

    // ---------- Lecturas ----------

    public function lista()
    {
        $obj = new UsuarioModel();
        jsonResponse(['ok' => true, 'data' => $obj->listar()]);
    }

    public function roles()
    {
        $obj = new UsuarioModel();
        jsonResponse(['ok' => true, 'data' => $obj->rolesActivos()]);
    }

    public function tiposDocumento()
    {
        $obj = new UsuarioModel();
        jsonResponse(['ok' => true, 'data' => $obj->tiposDocumento()]);
    }

    // ---------- Escrituras ----------

    public function postCreate()
    {
        $obj = new UsuarioModel();
        $body = requestJsonBody();
        $datos = $this->validarUsuario($body, $obj, null);

        // Contraseña: mínimo 8 caracteres, una minúscula, una mayúscula
        // y un carácter especial (ver validarContrasena en lib/validaciones.php).
        $contrasena = (string) ($body['contrasena'] ?? '');
        $errorClave = validarContrasena($contrasena);
        if ($errorClave !== null) {
            jsonResponse(['ok' => false, 'message' => $errorClave], 422);
        }
        $datos['contrasena_hash'] = password_hash($contrasena, PASSWORD_BCRYPT);

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

    public function postUpdate()
    {
        $obj = new UsuarioModel();
        $body = requestJsonBody();

        $idUsuario = filter_var($body['id_usuario'] ?? null, FILTER_VALIDATE_INT);
        if (!$idUsuario) {
            jsonResponse(['ok' => false, 'message' => 'id_usuario es obligatorio.'], 422);
        }
        if (!$obj->buscar($idUsuario)) {
            jsonResponse(['ok' => false, 'message' => 'El usuario no existe.'], 404);
        }

        $datos = $this->validarUsuario($body, $obj, $idUsuario);

        if ($obj->actualizar($idUsuario, $datos) === false) {
            jsonResponse(['ok' => false, 'message' => 'No se pudo actualizar: ' . $obj->ultimoError()], 500);
        }

        jsonResponse(['ok' => true, 'message' => 'Usuario actualizado correctamente.']);
    }

    public function postEstado()
    {
        $obj = new UsuarioModel();
        $body = requestJsonBody();

        $idUsuario = filter_var($body['id_usuario'] ?? null, FILTER_VALIDATE_INT);
        $estado = filter_var($body['estado'] ?? null, FILTER_VALIDATE_INT);

        if (!$idUsuario || ($estado !== 0 && $estado !== 1)) {
            jsonResponse(['ok' => false, 'message' => 'Datos incompletos para cambiar el estado.'], 422);
        }

        if ($estado === 0 && isset($_SESSION['id_usuario']) && (int) $_SESSION['id_usuario'] === $idUsuario) {
            jsonResponse(['ok' => false, 'message' => 'No puedes inhabilitar tu propio usuario.'], 422);
        }

        if ($obj->cambiarEstado($idUsuario, $estado) === false) {
            jsonResponse(['ok' => false, 'message' => 'No se pudo cambiar el estado.'], 500);
        }

        jsonResponse([
            'ok' => true,
            'message' => $estado === 1 ? 'Usuario habilitado.' : 'Usuario inhabilitado.',
        ]);
    }

    // ---------- Validación compartida por create y update ----------
    private function validarUsuario($body, $obj, $idExcluir)
    {
        $nombre = limpiar($body['nombre'] ?? '');
        $apellido = limpiar($body['apellido'] ?? '');
        $correo = strtolower(trim($body['correo'] ?? ''));
        $documento = normalizarDocumento($body['documento'] ?? '');
        $idRol = filter_var($body['id_rol'] ?? null, FILTER_VALIDATE_INT);
        $idTipoDocumento = filter_var($body['id_tipodocumento'] ?? null, FILTER_VALIDATE_INT);

        foreach ([
            validarTexto($nombre, 'Nombres', 2, 80),
            validarTexto($apellido, 'Apellidos', 2, 80),
            validarDocumento($documento, 'Número de documento'),
        ] as $error) {
            if ($error !== null) {
                jsonResponse(['ok' => false, 'message' => $error], 422);
            }
        }

        if ($correo === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            jsonResponse(['ok' => false, 'message' => 'El correo no es válido.'], 422);
        }
        if (mb_strlen($correo) > 120) {
            jsonResponse(['ok' => false, 'message' => 'El correo no puede superar 120 caracteres.'], 422);
        }
        if ($obj->existeCorreo($correo, $idExcluir)) {
            jsonResponse(['ok' => false, 'message' => 'Ya existe un usuario registrado con ese correo.'], 422);
        }
        if ($obj->existeDocumento($documento, $idExcluir)) {
            jsonResponse(['ok' => false, 'message' => 'Ya existe un usuario registrado con ese número de documento.'], 422);
        }

        if (!$idTipoDocumento || !$obj->tipoDocumentoExiste($idTipoDocumento)) {
            jsonResponse(['ok' => false, 'message' => 'Debe seleccionar un tipo de documento válido.'], 422);
        }
        if (!$idRol || !$obj->rolExiste($idRol)) {
            jsonResponse(['ok' => false, 'message' => 'Debe seleccionar un rol válido.'], 422);
        }

        return [
            'nombre' => $nombre,
            'apellido' => $apellido,
            'correo' => $correo,
            'documento' => $documento,
            'id_rol' => $idRol,
            'id_tipodocumento' => $idTipoDocumento,
        ];
    }
}