<?php

include_once __DIR__ . '/../MasterModel.php';

// ============================================================
// Modelo del módulo Roles y Permisos.
// Trabaja con las tablas que ya existen en BD_Dengue_SIGuppy.sql:
//   rol, modulo, accion_permiso, rol_permiso
// ============================================================
class RolesModel extends MasterModel{

    // Filas del formulario: Registrar / Consultar / Editar / Eliminar
    public function acciones(){
        return $this->selectAll(
            "SELECT id_accion_permiso, nombre
             FROM accion_permiso
             ORDER BY id_accion_permiso"
        );
    }

    // Columnas del formulario: los módulos del sistema
    public function modulos(){
        return $this->selectAll(
            "SELECT id_modulo, nombre, descripcion
             FROM modulo
             ORDER BY id_modulo"
        );
    }

    public function listarRoles(){
        return $this->selectAll(
            "SELECT r.id_rol, r.nombre_rol, r.descripcion, r.estado,
                    COUNT(rp.id_accion_permiso) AS total_permisos,
                    (SELECT COUNT(*) FROM usuario u WHERE u.id_rol = r.id_rol) AS total_usuarios
             FROM rol r
             LEFT JOIN rol_permiso rp ON rp.id_rol = r.id_rol
             GROUP BY r.id_rol, r.nombre_rol, r.descripcion
            "SELECT r.id_rol, r.nombre_rol, r.descripcion, r.estado,
                    COUNT(rp.id_accion_permiso) AS total_permisos,
                    (SELECT COUNT(*) FROM usuario u WHERE u.id_rol = r.id_rol) AS total_usuarios
             FROM rol r
             LEFT JOIN rol_permiso rp ON rp.id_rol = r.id_rol
             GROUP BY r.id_rol, r.nombre_rol, r.descripcion, r.estado
             ORDER BY r.id_rol"
        );
    }

    public function buscarRol($idRol){
        return $this->selectOne(
            "SELECT id_rol, nombre_rol, descripcion FROM rol WHERE id_rol = $1",
            "SELECT id_rol, nombre_rol, descripcion, estado FROM rol WHERE id_rol = $1",
            [$idRol]
        );
    }

    // Al editar se excluye el propio rol, si no siempre chocaría consigo mismo.
    public function existeNombreRol($nombre, $idExcluir = null){
        if($idExcluir){
            $valor = $this->selectValue(
                "SELECT 1 FROM rol WHERE LOWER(nombre_rol) = LOWER($1) AND id_rol <> $2",
                [$nombre, $idExcluir]
            );
        }else{
            $valor = $this->selectValue(
                "SELECT 1 FROM rol WHERE LOWER(nombre_rol) = LOWER($1)",
                [$nombre]
            );
        }
        return $valor !== null;
    }

    // Matriz de permisos ya marcados del rol, para precargar los checkbox:
    //   ['idModulo-idAccion' => true, ...]
    public function matrizDelRol($idRol){
        $filas = $this->selectAll(
            "SELECT id_modulo, id_accion_permiso FROM rol_permiso WHERE id_rol = $1",
            [$idRol]
        );

        $marcados = [];
        foreach($filas as $f){
            $marcados[] = $f['id_modulo'] . '-' . $f['id_accion_permiso'];
        }
        return $marcados;
    }

    // UPDATE de los datos del rol
    public function actualizarRol($idRol, $nombre, $descripcion){
        return $this->update(
            "UPDATE rol SET nombre_rol = $1, descripcion = $2 WHERE id_rol = $3",
            [$nombre, ($descripcion !== '' ? $descripcion : null), $idRol]
        );
    }

    // INSERT en la tabla rol. Devuelve el id generado.
    public function crearRol($nombre, $descripcion){
        return $this->selectValue(
            "INSERT INTO rol (nombre_rol, descripcion)
             VALUES ($1, $2)
    // Al editar se excluye el propio rol, si no siempre chocaría consigo mismo.
    public function existeNombreRol($nombre, $idExcluir = null){
        if($idExcluir){
            $valor = $this->selectValue(
                "SELECT 1 FROM rol WHERE LOWER(nombre_rol) = LOWER($1) AND id_rol <> $2",
                [$nombre, $idExcluir]
            );
        }else{
            $valor = $this->selectValue(
                "SELECT 1 FROM rol WHERE LOWER(nombre_rol) = LOWER($1)",
                [$nombre]
            );
        }
        return $valor !== null;
    }

    // Matriz de permisos ya marcados del rol, para precargar los checkbox:
    //   ['idModulo-idAccion' => true, ...]
    public function matrizDelRol($idRol){
        $filas = $this->selectAll(
            "SELECT id_modulo, id_accion_permiso FROM rol_permiso WHERE id_rol = $1",
            [$idRol]
        );

        $marcados = [];
        foreach($filas as $f){
            $marcados[] = $f['id_modulo'] . '-' . $f['id_accion_permiso'];
        }
        return $marcados;
    }

    // UPDATE de los datos del rol
    public function actualizarRol($idRol, $nombre, $descripcion){
        return $this->update(
            "UPDATE rol SET nombre_rol = $1, descripcion = $2 WHERE id_rol = $3",
            [$nombre, ($descripcion !== '' ? $descripcion : null), $idRol]
        );
    }

    // INSERT en la tabla rol. Devuelve el id generado.
    public function crearRol($nombre, $descripcion){
        return $this->selectValue(
            "INSERT INTO rol (nombre_rol, descripcion, estado)
             VALUES ($1, $2, 1)
             RETURNING id_rol",
            [$nombre, ($descripcion !== '' ? $descripcion : null)]
        );
    }

    // INSERT en rol_permiso: qué puede hacer el rol en cada módulo
    public function asignarPermiso($idRol, $idModulo, $idAccion){
        return $this->insert(
            "INSERT INTO rol_permiso (id_rol, id_modulo, id_accion_permiso)
             VALUES ($1, $2, $3)",
            [$idRol, $idModulo, $idAccion]
        );
    }

    // Detalle de lo que puede hacer un rol (para la pantalla de consulta)
    public function permisosDelRol($idRol){
        return $this->selectAll(
            "SELECT m.nombre AS modulo, a.nombre AS accion
             FROM rol_permiso rp
             INNER JOIN modulo         m ON m.id_modulo = rp.id_modulo
             INNER JOIN accion_permiso a ON a.id_accion_permiso = rp.id_accion_permiso
             WHERE rp.id_rol = $1
             ORDER BY m.id_modulo, a.id_accion_permiso",
            [$idRol]
        );
    }

    public function borrarPermisosDelRol($idRol){
        return $this->delete("DELETE FROM rol_permiso WHERE id_rol = $1", [$idRol]);
    }

    public function usuariosConRol($idRol){
        return (int) $this->selectValue(
            "SELECT COUNT(*) FROM usuario WHERE id_rol = $1",
            [$idRol]
        );
    }

    public function eliminarRol($idRol){
        // Primero los permisos (por la llave foránea), luego el rol
        $this->borrarPermisosDelRol($idRol);
        return $this->delete("DELETE FROM rol WHERE id_rol = $1", [$idRol]);
    // No se borra el rol: se inhabilita (estado = 0) o se habilita (estado = 1),
    // igual que en zoocriadero, tanque y las demás tablas del sistema.
    public function cambiarEstado($idRol, $estado){
        return $this->update(
            "UPDATE rol SET estado = $1 WHERE id_rol = $2",
            [$estado, $idRol]
        );
    }

    // ------------------------------------------------------------
    // Guarda el rol completo dentro de una transacción:
    // o se guardan el rol Y todos sus permisos, o no se guarda nada.
    // $seleccion llega del formulario como ['idModulo-idAccion', ...]
    // ------------------------------------------------------------
    public function registrarRolConPermisos($nombre, $descripcion, $seleccion){
        $this->beginTransaction();

        $idRol = $this->crearRol($nombre, $descripcion);
        if($idRol === null){
            $this->rollBack();
            return false;
        }

        foreach($seleccion as $par){
            $partes = explode('-', $par);
            if(count($partes) !== 2){ continue; }

            $idModulo = (int) $partes[0];
            $idAccion = (int) $partes[1];
            if($idModulo <= 0 || $idAccion <= 0){ continue; }

            if($this->asignarPermiso($idRol, $idModulo, $idAccion) === false){
                $this->rollBack();
                return false;
            }
        }

        $this->commit();
        return (int) $idRol;
    }

    // ------------------------------------------------------------
    // Edición: UPDATE del rol y reemplazo completo de sus permisos.
    // Se borran los permisos viejos y se vuelven a insertar los
    // marcados, todo dentro de la misma transacción.
    // ------------------------------------------------------------
    public function actualizarRolConPermisos($idRol, $nombre, $descripcion, $seleccion){
        $this->beginTransaction();

        if($this->actualizarRol($idRol, $nombre, $descripcion) === false){
            $this->rollBack();
            return false;
        }

        if($this->borrarPermisosDelRol($idRol) === false){
            $this->rollBack();
            return false;
        }

        foreach($seleccion as $par){
            $partes = explode('-', $par);
            if(count($partes) !== 2){ continue; }

            $idModulo = (int) $partes[0];
            $idAccion = (int) $partes[1];
            if($idModulo <= 0 || $idAccion <= 0){ continue; }

            if($this->asignarPermiso($idRol, $idModulo, $idAccion) === false){
                $this->rollBack();
                return false;
            }
        }

        $this->commit();
        return true;
    }
}

?>
