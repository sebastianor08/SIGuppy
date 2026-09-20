<?php

include_once __DIR__ . '/../MasterModel.php';

// ============================================================
// Modelo del módulo "Mi Perfil". A propósito NO reutiliza el
// UsuarioModel::actualizar() (que reescribe nombre/documento/rol/
// tipo de documento): este módulo solo puede tocar el correo, así
// que tiene su propio UPDATE de un solo campo para que sea
// imposible, incluso por error, escribir sobre el resto de los
// datos del usuario desde aquí.
// ============================================================
class PerfilModel extends MasterModel
{
    // Datos completos de un usuario (rol y tipo de documento ya
    // resueltos por nombre), para mostrarlos de solo lectura en el
    // perfil.
    public function datos($idUsuario)
    {
        return $this->selectOne(
            "SELECT u.id_usuario, u.nombre, u.apellido, u.correo, u.documento, u.estado,
                    TO_CHAR(u.creado_en, 'YYYY-MM-DD') AS creado_en,
                    r.nombre_rol,
                    td.nombre AS tipo_documento
             FROM usuario u
             INNER JOIN rol r ON r.id_rol = u.id_rol
             INNER JOIN tipo_documento td ON td.id_tipodocumento = u.id_tipodocumento
             WHERE u.id_usuario = $1",
            [$idUsuario]
        );
    }

    public function actualizarCorreo($idUsuario, $correo)
    {
        return $this->update(
            "UPDATE usuario SET correo = $1 WHERE id_usuario = $2",
            [$correo, $idUsuario]
        );
    }
}