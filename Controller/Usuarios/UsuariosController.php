<?php

include_once '../Model/Usuarios/UsuarioModel.php';
include_once __DIR__ . '/../../lib/Mailer.php';

class UsuariosController
{


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


    public function postCreate()
    {
        sigExigirPermiso('Gestión de Usuarios', 'crear');
        $obj = new UsuarioModel();
        $body = requestJsonBody();
        $datos = $this->validarUsuario($body, $obj, null);

        // La contraseña inicial ya NO la escribe quien registra al usuario:
        // siempre es su número de documento. Es una contraseña temporal —
        // debe_cambiar_contrasena queda en TRUE, así que el sistema exige
        // cambiarla antes de dejar entrar al usuario (ver login_process.php).
        $contrasenaInicial = $datos['documento'];
        $datos['contrasena_hash'] = password_hash($contrasenaInicial, PASSWORD_BCRYPT);

        $id = $obj->crear($datos);
        if ($id === null) {
            jsonResponse(['ok' => false, 'message' => 'No se pudo registrar: ' . $obj->ultimoError()], 500);
        }

        // Envía al correo del nuevo usuario su usuario (= el correo) y su
        // contraseña inicial (el número de documento). Es lo último que se
        // hace: si el correo falla (sin internet, credenciales de Gmail
        // vencidas...) el usuario YA quedó creado, así que solo se avisa en
        // el mensaje en vez de devolver error.
        $correoEnviado = enviarCredencialesUsuario(
            $datos['correo'],
            $datos['nombre'] . ' ' . $datos['apellido'],
            $datos['correo'],
            $contrasenaInicial
        );

        jsonResponse([
            'ok' => true,
            'message' => $correoEnviado
                ? 'Usuario registrado correctamente. Sus credenciales (contraseña = su número de documento) se enviaron a ' . $datos['correo'] . '.'
                : 'Usuario registrado correctamente, pero NO se pudo enviar el correo con sus credenciales. '
                    . 'Su contraseña inicial es su número de documento (' . $contrasenaInicial . '); infórmesela manualmente y revise la configuración de correo (lib/conf/mail.php).',
            'id_usuario' => (int) $id,
            'correo_enviado' => $correoEnviado,
        ], 201);
    }

    public function postUpdate()
    {
        sigExigirPermiso('Gestión de Usuarios', 'editar');
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
        sigExigirPermiso('Gestión de Usuarios', 'inhabilitar');
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

    private function validarUsuario($body, $obj, $idExcluir)
    {
        $nombre = limpiar($body['nombre'] ?? '');
        $apellido = limpiar($body['apellido'] ?? '');
        $correo = normalizarCorreo($body['correo'] ?? '');
        $documento = normalizarDocumento($body['documento'] ?? '');
        $idRol = filter_var($body['id_rol'] ?? null, FILTER_VALIDATE_INT);
        $idTipoDocumento = filter_var($body['id_tipodocumento'] ?? null, FILTER_VALIDATE_INT);

        if (!$idTipoDocumento || !$obj->tipoDocumentoExiste($idTipoDocumento)) {
            jsonResponse(['ok' => false, 'message' => 'Debe seleccionar un tipo de documento válido.'], 422);
        }
        $nombreTipoDocumento = $obj->nombreTipoDocumento($idTipoDocumento);

        foreach ([
            validarNombrePropio($nombre, 'Nombres', 2, 50),
            validarNombrePropio($apellido, 'Apellidos', 2, 50),
            validarDocumentoPorTipo($documento, $nombreTipoDocumento, 'Número de documento'),
            validarCorreo($correo),
        ] as $error) {
            if ($error !== null) {
                jsonResponse(['ok' => false, 'message' => $error], 422);
            }
        }

        if ($obj->existeCorreo($correo, $idExcluir)) {
            jsonResponse(['ok' => false, 'message' => 'Ya existe un usuario registrado con ese correo.'], 422);
        }
        if ($obj->existeDocumento($documento, $idExcluir)) {
            jsonResponse(['ok' => false, 'message' => 'Ya existe un usuario registrado con ese número de documento.'], 422);
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