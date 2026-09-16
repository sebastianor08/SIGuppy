<?php

include_once __DIR__ . '/../MasterModel.php';

// ============================================================
// Modelo del módulo Sitio (submódulo de "Terreno").
// Trabaja sobre la tabla "sitio" que ya existe en
// BD_Dengue_SIGuppy.sql. Sigue el mismo patrón que el resto de
// modelos del proyecto (extiende MasterModel, usa selectAll /
// selectOne / insert / update con parámetros $1, $2... para que
// PostgreSQL evite la inyección SQL).
// ============================================================
class SitioModel extends MasterModel
{
    // Listado completo, con el nombre del tipo de depósito ya resuelto
    public function listar()
    {
        return $this->selectAll(
            "SELECT s.id_sitio, s.direccion, s.comuna, s.barrio, s.latitud, s.longitud,
                    s.estado, s.creado_en, s.id_tipo_deposito,
                    td.nombre AS tipo_deposito
             FROM sitio s
             INNER JOIN tipo_deposito td ON td.id_tipo_deposito = s.id_tipo_deposito
             ORDER BY s.id_sitio DESC"
        );
    }

    public function buscarPorId($id)
    {
        return $this->selectOne(
            "SELECT * FROM sitio WHERE id_sitio = $1",
            [$id]
        );
    }

    // Para llenar el <select> del formulario
    public function tiposDeposito()
    {
        return $this->selectAll(
            "SELECT id_tipo_deposito, nombre FROM tipo_deposito WHERE estado = 1 ORDER BY nombre"
        );
    }

    public function crear($datos)
    {
        return $this->selectValue(
            "INSERT INTO sitio (id_tipo_deposito, direccion, comuna, barrio, latitud, longitud, estado)
             VALUES ($1, $2, $3, $4, $5, $6, 1)
             RETURNING id_sitio",
            [
                $datos['id_tipo_deposito'],
                $datos['direccion'],
                $datos['comuna'],
                $datos['barrio'],
                $datos['latitud'] !== '' ? $datos['latitud'] : null,
                $datos['longitud'] !== '' ? $datos['longitud'] : null,
            ]
        );
    }

    public function actualizar($id, $datos)
    {
        return $this->update(
            "UPDATE sitio
             SET id_tipo_deposito = $1, direccion = $2, comuna = $3, barrio = $4,
                 latitud = $5, longitud = $6
             WHERE id_sitio = $7",
            [
                $datos['id_tipo_deposito'],
                $datos['direccion'],
                $datos['comuna'],
                $datos['barrio'],
                $datos['latitud'] !== '' ? $datos['latitud'] : null,
                $datos['longitud'] !== '' ? $datos['longitud'] : null,
                $id,
            ]
        );
    }

    // Eliminación lógica (igual que el resto del sistema: se cambia
    // el estado, no se borra la fila, para no perder el historial
    // de seguimientos de terreno que apuntan a este sitio).
    public function eliminar($id)
    {
        return $this->update("UPDATE sitio SET estado = 0 WHERE id_sitio = $1", [$id]);
    }
}

?>
