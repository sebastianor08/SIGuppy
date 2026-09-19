<?php

include_once __DIR__ . '/../MasterModel.php';

class RegistrarSitioModel extends MasterModel
{
    public function departamentos()
    {
        return $this->selectAll(
            "SELECT id_departamento, nombre
             FROM departamento
             ORDER BY nombre"
        );
    }

    public function ciudadesDeDepartamento($idDepartamento)
    {
        return $this->selectAll(
            "SELECT ci.id_ciudad, ci.nombre
             FROM ciudad ci
             INNER JOIN departamento dep ON dep.id_ciudad = ci.id_ciudad
             WHERE dep.id_departamento = $1
             ORDER BY ci.nombre",
            [$idDepartamento]
        );
    }

    public function comunasDeCiudad($idCiudad)
    {
        return $this->selectAll(
            "SELECT co.id_comuna, co.nombre
             FROM comuna co
             INNER JOIN ciudad ci ON ci.id_comuna = co.id_comuna
             WHERE ci.id_ciudad = $1
             ORDER BY co.nombre",
            [$idCiudad]
        );
    }

    public function barriosDeComuna($idComuna)
    {
        return $this->selectAll(
            "SELECT id_barrio, nombre
             FROM barrio
             WHERE id_comuna = $1
             ORDER BY nombre",
            [$idComuna]
        );
    }

    public function nomenclaturas()
    {
        return $this->selectAll(
            "SELECT id_nomenclatura, nomenclatura
             FROM nomenclatura
             ORDER BY id_nomenclatura"
        );
    }

    public function nombreNomenclatura($idNomenclatura)
    {
        $fila = $this->selectOne(
            "SELECT nomenclatura
             FROM nomenclatura
             WHERE id_nomenclatura = $1",
            [$idNomenclatura]
        );

        return $fila['nomenclatura'] ?? null;
    }

    public function nomenclaturaExiste($idNomenclatura)
    {
        return $this->selectOne(
            "SELECT 1
             FROM nomenclatura
             WHERE id_nomenclatura = $1",
            [$idNomenclatura]
        ) !== null;
    }

    public function ubicacionValida($idDepartamento, $idCiudad, $idComuna, $idBarrio)
    {
        return $this->selectOne(
            "SELECT 1
             FROM departamento dep
             INNER JOIN ciudad ci ON ci.id_ciudad = dep.id_ciudad
             INNER JOIN comuna co ON co.id_comuna = ci.id_comuna
             INNER JOIN barrio b ON b.id_comuna = co.id_comuna
             WHERE dep.id_departamento = $1
               AND ci.id_ciudad = $2
               AND co.id_comuna = $3
               AND b.id_barrio = $4",
            [$idDepartamento, $idCiudad, $idComuna, $idBarrio]
        ) !== null;
    }

    public function buscar($idSitio)
    {
        return $this->selectOne(
            "SELECT
                s.id_sitio,
                s.nombre,
                s.descripcion,
                s.estado,
                TO_CHAR(s.fecha, 'YYYY-MM-DD HH24:MI:SS') AS fecha,
                d.id_direccion,
                d.direccion,
                d.id_departamento,
                d.id_ciudad,
                d.id_comuna,
                d.id_barrio,
                d.id_nomenclatura
             FROM sitio s
             INNER JOIN direccion d ON d.id_direccion = s.id_direccion
             WHERE s.id_sitio = $1",
            [$idSitio]
        );
    }

    public function crearDireccion($datos)
    {
        return $this->selectValue(
            "INSERT INTO direccion
                (id_departamento, id_comuna, id_ciudad, id_barrio, direccion, id_nomenclatura)
             VALUES ($1, $2, $3, $4, $5, $6)
             RETURNING id_direccion",
            [
                $datos['id_departamento'],
                $datos['id_comuna'],
                $datos['id_ciudad'],
                $datos['id_barrio'],
                $datos['direccion'],
                $datos['id_nomenclatura']
            ]
        );
    }

    public function actualizarDireccion($idDireccion, $datos)
    {
        $resultado = $this->update(
            "UPDATE direccion
             SET id_departamento = $1,
                 id_comuna = $2,
                 id_ciudad = $3,
                 id_barrio = $4,
                 direccion = $5,
                 id_nomenclatura = $6
             WHERE id_direccion = $7",
            [
                $datos['id_departamento'],
                $datos['id_comuna'],
                $datos['id_ciudad'],
                $datos['id_barrio'],
                $datos['direccion'],
                $datos['id_nomenclatura'],
                $idDireccion
            ]
        );

        return $resultado !== false;
    }

    public function crearSitio($datos)
    {
        // No se envían estado ni fecha porque la BD los asigna automáticamente.
        return $this->selectValue(
            "INSERT INTO sitio (nombre, descripcion, id_direccion)
             VALUES ($1, $2, $3)
             RETURNING id_sitio",
            [$datos['nombre'], $datos['descripcion'], $datos['id_direccion']]
        );
    }

    public function actualizarSitio($idSitio, $datos)
    {
        $resultado = $this->update(
            "UPDATE sitio
             SET nombre = $1,
                 descripcion = $2
             WHERE id_sitio = $3",
            [$datos['nombre'], $datos['descripcion'], $idSitio]
        );

        return $resultado !== false;
    }

    public function cambiarEstado($idSitio, $estado)
    {
        $resultado = $this->update(
            "UPDATE sitio
             SET estado = $1
             WHERE id_sitio = $2",
            [$estado, $idSitio]
        );

        return $resultado !== false;
    }
}
