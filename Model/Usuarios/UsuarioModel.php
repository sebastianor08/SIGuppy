<?php

include_once __DIR__ . '/../MasterModel.php';

class UsuarioModel extends MasterModel{

    // Listado para la tabla: nombre del rol y del tipo de documento ya resueltos.
    public function listar(){
        return $this->selectAll(
            "SELECT u.id_usuario, u.nombre, u.apellido, u.correo, u.estado,
                    TO_CHAR(u.creado_en, 'YYYY-MM-DD') AS creado_en,
                    u.id_rol, r.nombre_rol,
                    u.id_tipodocumento, td.nombre AS tipo_documento,
                    u.documento
             FROM usuario u
             INNER JOIN rol r ON r.id_rol = u.id_rol
             INNER JOIN tipo_documento td ON td.id_tipodocumento = u.id_tipodocumento
             ORDER BY u.nombre, u.apellido"
        );
    }

    public function buscar($idUsuario){
        return $this->selectOne(
            "SELECT id_usuario, id_tipodocumento, id_rol, nombre, apellido, correo, documento, estado
             FROM usuario
             WHERE id_usuario = $1",
            [$idUsuario]
        );
    }

    // Roles activos, para el <select> del formulario
    public function rolesActivos(){
        return $this->selectAll(
            "SELECT id_rol, nombre_rol FROM rol WHERE estado = 1 ORDER BY nombre_rol"
        );
    }

    // Tipos de documento, para el <select> del formulario (la tabla no maneja estado)
    public function tiposDocumento(){
        return $this->selectAll(
            "SELECT id_tipodocumento, nombre FROM tipo_documento ORDER BY nombre"
        );
    }

    public function rolExiste($idRol){
        return $this->selectValue(
            "SELECT 1 FROM rol WHERE id_rol = $1 AND estado = 1",
            [$idRol]
        ) !== null;
    }

    public function tipoDocumentoExiste($idTipoDocumento){
        return $this->selectValue(
            "SELECT 1 FROM tipo_documento WHERE id_tipodocumento = $1",
            [$idTipoDocumento]
        ) !== null;
    }

    // Nombre del tipo de documento (p.ej. "Cédula de Ciudadanía"), para
    // aplicar la regla de longitud/formato que corresponda según el tipo.
    public function nombreTipoDocumento($idTipoDocumento){
        return $this->selectValue(
            "SELECT nombre FROM tipo_documento WHERE id_tipodocumento = $1",
            [$idTipoDocumento]
        );
    }

    // El correo es único en toda la tabla (usuario_correo_key). Al editar se
    // excluye al propio usuario, igual que existeNombreRol en RolesModel.
    public function existeCorreo($correo, $idExcluir = null){
        if($idExcluir){
            $valor = $this->selectValue(
                "SELECT 1 FROM usuario WHERE LOWER(correo) = LOWER($1) AND id_usuario <> $2",
                [$correo, $idExcluir]
            );
        }else{
            $valor = $this->selectValue(
                "SELECT 1 FROM usuario WHERE LOWER(correo) = LOWER($1)",
                [$correo]
            );
        }
        return $valor !== null;
    }

    // El documento tampoco se puede repetir (usuario_documento_key).
    // Misma lógica que existeCorreo: al editar se excluye al propio usuario.
    public function existeDocumento($documento, $idExcluir = null){
        if($idExcluir){
            $valor = $this->selectValue(
                "SELECT 1 FROM usuario WHERE UPPER(documento) = UPPER($1) AND id_usuario <> $2",
                [$documento, $idExcluir]
            );
        }else{
            $valor = $this->selectValue(
                "SELECT 1 FROM usuario WHERE UPPER(documento) = UPPER($1)",
                [$documento]
            );
        }
        return $valor !== null;
    }

    // ---------------- INSERT ----------------
    // $datos ya trae la contraseña como hash (password_hash) del número de
    // documento del usuario, nunca en texto plano. debe_cambiar_contrasena
    // siempre entra en TRUE: todo usuario nuevo debe cambiar esa contraseña
    // inicial antes de poder usar el sistema (ver login_process.php).
    public function crear($datos){
        return $this->selectValue(
            "INSERT INTO usuario
             (id_tipodocumento, id_rol, nombre, apellido, documento, correo, contrasena, estado, debe_cambiar_contrasena)
             VALUES ($1, $2, $3, $4, $5, $6, $7, 1, TRUE)
             RETURNING id_usuario",
            [
                $datos['id_tipodocumento'],
                $datos['id_rol'],
                $datos['nombre'],
                $datos['apellido'],
                $datos['documento'],
                $datos['correo'],
                $datos['contrasena_hash'],
            ]
        );
    }

    // ---------------- UPDATE ----------------
    // Actualiza los datos del usuario sin tocar la contraseña.
    public function actualizar($idUsuario, $datos){
        return $this->update(
            "UPDATE usuario
             SET id_tipodocumento = $1, id_rol = $2, nombre = $3, apellido = $4,
                 documento = $5, correo = $6
             WHERE id_usuario = $7",
            [
                $datos['id_tipodocumento'],
                $datos['id_rol'],
                $datos['nombre'],
                $datos['apellido'],
                $datos['documento'],
                $datos['correo'],
                $idUsuario,
            ]
        );
    }

    // Cambio de contraseña. La opción "Restablecer contraseña" se quitó del
    // módulo de usuarios; este método queda disponible para el flujo de
    // "¿Olvidó su contraseña?" del login.
    public function actualizarContrasena($idUsuario, $hash){
        return $this->update(
            "UPDATE usuario
             SET contrasena = $1, intentos_fallidos = 0, bloqueo_hasta = NULL
             WHERE id_usuario = $2",
            [$hash, $idUsuario]
        );
    }

    // Habilitar / inhabilitar (no se borra, igual que en los demás módulos)
    public function cambiarEstado($idUsuario, $estado){
        return $this->update(
            "UPDATE usuario SET estado = $1 WHERE id_usuario = $2",
            [$estado, $idUsuario]
        );
    }
}