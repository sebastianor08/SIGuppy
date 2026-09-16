<?php

include_once __DIR__ . '/../MasterModel.php';

// ============================================================
// Modelo del módulo Territorio Priorizado (submódulo de "Terreno").
// Trabaja sobre la tabla "territorio_priorizado" que ya existe en
// BD_Dengue_SIGuppy.sql. Cada territorio priorizado queda asociado
// a un funcionario de ecosalud (tabla usuario).
// ============================================================
class TerritorioPriorizadoModel extends MasterModel
{
    public function listar()
    {
        return $this->selectAll(
            "SELECT t.id_territorio, t.comuna, t.barrio, t.sitio, t.direccion_sitio,
                    t.nombre_lider, t.telefono_lider, t.correo_lider, t.clase_liderazgo,
                    t.latitud, t.longitud, t.fecha_registro, t.estado,
                    u.nombre AS funcionario_nombre, u.apellido AS funcionario_apellido
             FROM territorio_priorizado t
             INNER JOIN usuario u ON u.id_usuario = t.id_funcionario_ecosalud
             ORDER BY t.id_territorio DESC"
        );
    }

    public function buscarPorId($id)
    {
        return $this->selectOne(
            "SELECT * FROM territorio_priorizado WHERE id_territorio = $1",
            [$id]
        );
    }

    // Para llenar el <select> de "Funcionario de ecosalud responsable"
    public function funcionarios()
    {
        return $this->selectAll(
            "SELECT id_usuario, nombre, apellido FROM usuario WHERE estado = 1 ORDER BY nombre"
        );
    }

    public function crear($d)
    {
        return $this->selectValue(
            "INSERT INTO territorio_priorizado
                (comuna, barrio, sitio, direccion_sitio, nombre_lider, direccion_lider,
                 telefono_lider, correo_lider, clase_liderazgo, id_funcionario_ecosalud,
                 latitud, longitud, estado)
             VALUES ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11,$12,1)
             RETURNING id_territorio",
            [
                $d['comuna'], $d['barrio'], $d['sitio'], $d['direccion_sitio'],
                $d['nombre_lider'], $d['direccion_lider'], $d['telefono_lider'], $d['correo_lider'],
                $d['clase_liderazgo'], $d['id_funcionario_ecosalud'],
                $d['latitud'] !== '' ? $d['latitud'] : null,
                $d['longitud'] !== '' ? $d['longitud'] : null,
            ]
        );
    }

    public function actualizar($id, $d)
    {
        return $this->update(
            "UPDATE territorio_priorizado SET
                comuna=$1, barrio=$2, sitio=$3, direccion_sitio=$4, nombre_lider=$5,
                direccion_lider=$6, telefono_lider=$7, correo_lider=$8, clase_liderazgo=$9,
                id_funcionario_ecosalud=$10, latitud=$11, longitud=$12
             WHERE id_territorio = $13",
            [
                $d['comuna'], $d['barrio'], $d['sitio'], $d['direccion_sitio'],
                $d['nombre_lider'], $d['direccion_lider'], $d['telefono_lider'], $d['correo_lider'],
                $d['clase_liderazgo'], $d['id_funcionario_ecosalud'],
                $d['latitud'] !== '' ? $d['latitud'] : null,
                $d['longitud'] !== '' ? $d['longitud'] : null,
                $id,
            ]
        );
    }

    public function eliminar($id)
    {
        return $this->update("UPDATE territorio_priorizado SET estado = 0 WHERE id_territorio = $1", [$id]);
    }
}

?>
