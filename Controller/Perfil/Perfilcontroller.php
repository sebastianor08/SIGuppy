<?php

include_once '../Model/Perfil/PerfilModel.php';
include_once '../Model/Usuarios/UsuarioModel.php';
class PerfilController
{
    private function idUsuarioSesion()
    {
        $id = $_SESSION['id_usuario'] ?? null;
        if (!$id) {
            jsonResponse(['ok' => false, 'message' => 'No hay una sesión activa.'], 401);
        }
        return (int) $id;
    }

    public function datos()
    {
        $obj = new PerfilModel();
        $idUsuario = $this->idUsuarioSesion();

        $usuario = $obj->datos($idUsuario);
        if (!$usuario) {
            jsonResponse(['ok' => false, 'message' => 'No fue posible encontrar tu usuario.'], 404);
        }

        jsonResponse(['ok' => true, 'data' => $usuario]);
    }

    // Único campo editable desde este módulo: el correo electrónico.
    public function postActualizarCorreo()
    {
        $obj = new PerfilModel();
        $usuarioObj = new UsuarioModel();
        $idUsuario = $this->idUsuarioSesion();

        $body = requestJsonBody();
        $correo = normalizarCorreo($body['correo'] ?? '');

        $error = validarCorreo($correo);
        if ($error !== null) {
            jsonResponse(['ok' => false, 'message' => $error], 422);
        }
        if ($usuarioObj->existeCorreo($correo, $idUsuario)) {
            jsonResponse(['ok' => false, 'message' => 'Ya existe un usuario registrado con ese correo.'], 422);
        }

        if ($obj->actualizarCorreo($idUsuario, $correo) === false) {
            jsonResponse(['ok' => false, 'message' => 'No se pudo actualizar el correo: ' . $obj->ultimoError()], 500);
        }

        // El nombre mostrado en la sesión no depende del correo, pero si
        // más adelante se agrega el correo a la barra superior, ya queda
        // listo para leerlo actualizado sin tener que volver a iniciar sesión.
        $_SESSION['correo'] = $correo;

        jsonResponse(['ok' => true, 'message' => 'Correo actualizado correctamente.', 'correo' => $correo]);
    }
}