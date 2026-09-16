<?php

include_once __DIR__ . '/../MasterModel.php';

// ============================================================
// Modelo del módulo Usuarios (Gestión de Usuarios).
// Trabaja sobre la tabla "usuario" que ya existe en
// BD_Dengue_SIGuppy.sql. El registro de usuarios y la lista ya
// existían como enlaces "muertos" en el sidebar (Controller/
// Gestion de usuario/ quedó a medio hacer, sin conectar); este
// modelo/controlador nuevo es el que realmente queda activo.
//
// El módulo de "Roles y Permisos" (Model/Roles/RolesModel.php,
// View/Roles/...) NO se toca: sigue funcionando exactamente
// igual. Este módulo solo administra la tabla usuario y usa la
// tabla rol para el <select> de "Rol", tal como ya lo hacía
// RolesModel para sus propios reportes.
// ============================================================
class UsuariosModel extends MasterModel
{
    public function listar()
    {
        return $this->selectAll(
            "SELECT u.id_usuario, u.nombre, u.apellido, u.correo, u.estado, u.creado_en,
                    r.id_rol, r.nombre_rol,
                    td.nombre AS tipo_documento
             FROM usuario u
             INNER JOIN rol r ON r.id_rol = u.id_rol
             INNER JOIN tipo_documento td ON td.id_tipodocumento = u.id_tipodocumento
             ORDER BY u.id_usuario DESC"
        );
    }

    public function buscarPorId($id)
    {
        return $this->selectOne("SELECT * FROM usuario WHERE id_usuario = $1", [$id]);
    }

    public function existeCorreo($correo, $idExcluir = null)
    {
        if ($idExcluir) {
            $fila = $this->selectOne(
                "SELECT id_usuario FROM usuario WHERE LOWER(correo) = LOWER($1) AND id_usuario <> $2",
                [$correo, $idExcluir]
            );
        } else {
            $fila = $this->selectOne("SELECT id_usuario FROM usuario WHERE LOWER(correo) = LOWER($1)", [$correo]);
        }
        return $fila !== null;
    }

    // Para los <select> del formulario
    public function roles()
    {
        return $this->selectAll("SELECT id_rol, nombre_rol FROM rol WHERE estado = 1 ORDER BY nombre_rol");
    }

    public function tiposDocumento()
    {
        return $this->selectAll("SELECT id_tipodocumento, nombre FROM tipo_documento ORDER BY nombre");
    }

    // $contrasenaHash ya debe venir cifrada con password_hash() (el
    // controlador la genera, igual que hace cambio_contrasena_process.php).
    public function crear($d, $contrasenaHash)
    {
        return $this->selectValue(
            "INSERT INTO usuario (id_tipodocumento, id_rol, nombre, apellido, correo, contrasena, estado)
             VALUES ($1, $2, $3, $4, $5, $6, 1)
             RETURNING id_usuario",
            [$d['id_tipodocumento'], $d['id_rol'], $d['nombre'], $d['apellido'], $d['correo'], $contrasenaHash]
        );
    }

    // Edita los datos del usuario SIN tocar la contraseña.
    public function actualizar($id, $d)
    {
        return $this->update(
            "UPDATE usuario SET id_tipodocumento = $1, id_rol = $2, nombre = $3, apellido = $4, correo = $5
             WHERE id_usuario = $6",
            [$d['id_tipodocumento'], $d['id_rol'], $d['nombre'], $d['apellido'], $d['correo'], $id]
        );
    }

    public function cambiarEstado($id, $estado)
    {
        return $this->update("UPDATE usuario SET estado = $1 WHERE id_usuario = $2", [$estado, $id]);
    }
}

?>
