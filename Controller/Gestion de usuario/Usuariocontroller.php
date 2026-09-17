<?php

include_once '../Model/Usuario/UsuariosModel.php';

// ============================================================
// Controlador del módulo Usuarios. Responde solo JSON, se llama
// siempre por Web/ajax.php:
//   Web/ajax.php?modulo=Usuario&controlador=Usuario&funcion=lista
// ============================================================
class Usuariocontroller{

    // ---------- Lecturas ----------

    public function lista(){
        $obj = new UsuariosModel();
        jsonResponse(['ok' => true, 'data' => $obj->listar()]);
    }

    public function roles(){
        $obj = new UsuariosModel();
        jsonResponse(['ok' => true, 'data' => $obj->roles()]);
    }

    // ---------- Escrituras ----------

    public function postCreate(){
        $obj  = new UsuariosModel();
        $body = requestJsonBody();

        $datos = $this->validarUsuario($body, $obj, null);

        // La contraseña sí es obligatoria al crear
        $contrasena = $body['contrasena'] ?? '';
        if(esVacio($contrasena) || mb_strlen($contrasena) < 6){
            jsonResponse(['ok' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres.'], 422);
        }
        $datos['contrasena'] = password_hash($contrasena, PASSWORD_BCRYPT);
        $datos['id_tipodocumento'] = filter_var($body['id_tipodocumento'] ?? 1, FILTER_VALIDATE_INT) ?: 1;

        $id = $obj->crear($datos);
        if($id === null){
            jsonResponse(['ok' => false, 'message' => 'No se pudo registrar: ' . $obj->ultimoError()], 500);
        }

        jsonResponse([
            'ok' => true,
            'message' => 'Usuario registrado correctamente.',
            'id_usuario' => (int) $id,
        ], 201);
    }

    public function postUpdate(){
        $obj  = new UsuariosModel();
        $body = requestJsonBody();

        $idUsuario = filter_var($body['id_usuario'] ?? null, FILTER_VALIDATE_INT);
        if(!$idUsuario){
            jsonResponse(['ok' => false, 'message' => 'id_usuario es obligatorio.'], 422);
        }
        if(!$obj->buscar($idUsuario)){
            jsonResponse(['ok' => false, 'message' => 'El usuario no existe.'], 404);
        }

        $datos = $this->validarUsuario($body, $obj, $idUsuario);

        if($obj->actualizar($idUsuario, $datos) === false){
            jsonResponse(['ok' => false, 'message' => 'No se pudo actualizar: ' . $obj->ultimoError()], 500);
        }

        jsonResponse(['ok' => true, 'message' => 'Usuario actualizado correctamente.']);
    }

    public function postEstado(){
        $obj  = new UsuariosModel();
        $body = requestJsonBody();

        $idUsuario = filter_var($body['id_usuario'] ?? null, FILTER_VALIDATE_INT);
        $estado    = filter_var($body['estado'] ?? null, FILTER_VALIDATE_INT);

        if(!$idUsuario || ($estado !== 0 && $estado !== 1)){
            jsonResponse(['ok' => false, 'message' => 'Datos incompletos para cambiar el estado.'], 422);
        }

        if($obj->cambiarEstado($idUsuario, $estado) === false){
            jsonResponse(['ok' => false, 'message' => 'No se pudo cambiar el estado.'], 500);
        }

        jsonResponse([
            'ok' => true,
            'message' => $estado === 1 ? 'Usuario activado.' : 'Usuario desactivado.',
        ]);
    }

    // ---------- Validación compartida por create y update ----------
    private function validarUsuario($body, $obj, $idExcluir){
        $nombre    = limpiar($body['nombre'] ?? '');
        $apellido  = limpiar($body['apellido'] ?? '');
        $correo    = limpiar($body['correo'] ?? '');
        $telefono  = limpiar($body['telefono'] ?? '');
        $idRol     = filter_var($body['id_rol'] ?? null, FILTER_VALIDATE_INT);

        foreach([
            validarTexto($nombre, 'Nombres', 2, 80),
            validarTexto($apellido, 'Apellidos', 2, 80),
            validarTexto($correo, 'Correo', 5, 120),
            validarTextoOpcional($telefono, 'Teléfono', 30),
        ] as $error){
            if($error !== null){
                jsonResponse(['ok' => false, 'message' => $error], 422);
            }
        }

        if(!filter_var($correo, FILTER_VALIDATE_EMAIL)){
            jsonResponse(['ok' => false, 'message' => 'El correo no tiene un formato válido.'], 422);
        }
        if(!$idRol){
            jsonResponse(['ok' => false, 'message' => 'Debe seleccionar un rol.'], 422);
        }
        if($obj->existeCorreo($correo, $idExcluir)){
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