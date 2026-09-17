<?php
require_once 'MasterModel.php';

class UsuariosModel extends MasterModel {

    public function listarUsuarios() {
        // En PostgreSQL es buena práctica usar comillas dobles en tablas y columnas
        $sql = 'SELECT u.id_usuario, u.nombre, u.apellido, u.correo, u.estado, r.nombre_rol 
                FROM "usuario" u 
                INNER JOIN "rol" r ON u.id_rol = r.id_rol 
                ORDER BY u.id_usuario DESC';
                
        return $this->selectAll($sql);
    }

    public function obtenerUsuarioPorCorreo($correo) {
        $sql = 'SELECT u.id_usuario, u.nombre, u.apellido, u.correo, u.contrasena, u.estado, u.id_rol, r.nombre_rol 
                FROM "usuario" u 
                INNER JOIN "rol" r ON u.id_rol = r.id_rol 
                WHERE LOWER(u.correo) = LOWER($1)'; // En PostgreSQL nativo usa $1, o ? si usas PDO
        
        return $this->selectOne($sql, [$correo]);
    }
}