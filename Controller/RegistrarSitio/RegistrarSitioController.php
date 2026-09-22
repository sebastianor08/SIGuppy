<?php

include_once '../Model/RegistrarSitio/RegistrarSitioModel.php';
include_once __DIR__ . '/../../lib/geocodificador.php';

class RegistrarSitioController
{
    public function catalogos()
    {
        $obj = new RegistrarSitioModel();

        jsonResponse([
            'ok' => true,
            'data' => [
                'departamentos' => $obj->departamentos(),
                'nomenclaturas' => $obj->nomenclaturas()
            ]
        ]);
    }

    public function ciudades()
    {
        $obj = new RegistrarSitioModel();
        $id = filter_var($_GET['id_departamento'] ?? null, FILTER_VALIDATE_INT);

        if (!$id) {
            jsonResponse(['ok' => false, 'message' => 'Debe indicar el departamento.'], 422);
        }

        jsonResponse(['ok' => true, 'data' => $obj->ciudadesDeDepartamento($id)]);
    }

    public function comunas()
    {
        $obj = new RegistrarSitioModel();
        $id = filter_var($_GET['id_ciudad'] ?? null, FILTER_VALIDATE_INT);

        if (!$id) {
            jsonResponse(['ok' => false, 'message' => 'Debe indicar la ciudad.'], 422);
        }

        jsonResponse(['ok' => true, 'data' => $obj->comunasDeCiudad($id)]);
    }

    public function buscar()
    {
        $obj = new RegistrarSitioModel();
        $id = filter_var($_GET['id_sitio'] ?? null, FILTER_VALIDATE_INT);

        if (!$id) {
            jsonResponse(['ok' => false, 'message' => 'id_sitio es obligatorio.'], 422);
        }

        $sitio = $obj->buscar($id);
        if (!$sitio) {
            jsonResponse(['ok' => false, 'message' => 'El sitio no existe.'], 404);
        }

        jsonResponse(['ok' => true, 'data' => $sitio]);
    }

    public function barrios()
    {
        $obj = new RegistrarSitioModel();
        $id = filter_var($_GET['id_comuna'] ?? null, FILTER_VALIDATE_INT);

        if (!$id) {
            jsonResponse(['ok' => false, 'message' => 'Debe indicar la comuna.'], 422);
        }

        jsonResponse(['ok' => true, 'data' => $obj->barriosDeComuna($id)]);
    }

    public function postCreate()
    {
        $obj = new RegistrarSitioModel();
        $body = requestJsonBody();
        $datos = $this->validar($body, $obj);

        // Ubica la dirección en el mapa. Si no hay internet o no la encuentra, el
        // sitio se registra igual, solo que sin coordenadas (no sale en el mapa).
        $punto = $this->geocodificar($obj, $datos);

        try {
            $obj->beginTransaction();

            $idDireccion = $obj->crearDireccion($datos);
            if (!$idDireccion) {
                throw new Exception('No se pudo registrar la dirección: ' . $obj->ultimoError());
            }

            if ($punto !== null) {
                $obj->guardarCoordenadas($idDireccion, $punto['lat'], $punto['lng']);
            }

            $datos['id_direccion'] = $idDireccion;

            // estado y fecha NO se envían: PostgreSQL usa sus valores DEFAULT.
            $idSitio = $obj->crearSitio($datos);
            if (!$idSitio) {
                throw new Exception('No se pudo registrar el sitio: ' . $obj->ultimoError());
            }

            $obj->commit();
        } catch (Throwable $e) {
            $obj->rollBack();
            error_log('Error al registrar sitio: ' . $e->getMessage());
            jsonResponse(['ok' => false, 'message' => $e->getMessage()], 500);
        }

        jsonResponse([
            'ok' => true,
            'message' => 'Sitio registrado correctamente.',
            'id_sitio' => (int)$idSitio
        ], 201);
    }

    public function postUpdate()
    {
        $obj = new RegistrarSitioModel();
        $body = requestJsonBody();

        $idSitio = filter_var($body['id_sitio'] ?? null, FILTER_VALIDATE_INT);
        if (!$idSitio) {
            jsonResponse(['ok' => false, 'message' => 'id_sitio es obligatorio.'], 422);
        }

        $actual = $obj->buscar($idSitio);
        if (!$actual) {
            jsonResponse(['ok' => false, 'message' => 'El sitio no existe.'], 404);
        }

        $datos = $this->validar($body, $obj);

        $cambioDireccion = $this->direccionCambio($actual, $datos);
        $sinCoordenadas = ($actual['latitud'] === null || $actual['longitud'] === null);
        $punto = ($cambioDireccion || $sinCoordenadas) ? $this->geocodificar($obj, $datos) : null;

        try {
            $obj->beginTransaction();

            if (!$obj->actualizarDireccion($actual['id_direccion'], $datos)) {
                throw new Exception('No se pudo actualizar la dirección: ' . $obj->ultimoError());
            }

            if ($punto !== null) {
                $obj->guardarCoordenadas($actual['id_direccion'], $punto['lat'], $punto['lng']);
            } elseif ($cambioDireccion) {
                $obj->guardarCoordenadas($actual['id_direccion'], null, null);
            }

            if (!$obj->actualizarSitio($idSitio, $datos)) {
                throw new Exception('No se pudo actualizar el sitio: ' . $obj->ultimoError());
            }

            $obj->commit();
        } catch (Throwable $e) {
            $obj->rollBack();
            error_log('Error al actualizar sitio: ' . $e->getMessage());
            jsonResponse(['ok' => false, 'message' => $e->getMessage()], 500);
        }

        jsonResponse([
            'ok' => true,
            'message' => 'Sitio actualizado correctamente.'
        ]);
    }

    // Devuelve ['lat' => float, 'lng' => float] o null si no se pudo ubicar.
    private function geocodificar($obj, $datos)
    {
        $nombres = $obj->nombresUbicacion($datos['id_barrio'], $datos['id_comuna']);

        return geocodificarDireccion(
            $datos['direccion'],
            $nombres['barrio'] ?? null,
            $nombres['comuna'] ?? null
        );
    }

    // ¿La dirección del formulario es distinta a la guardada?
    private function direccionCambio($actual, $datos)
    {
        return trim((string)$actual['direccion']) !== $datos['direccion']
            || (int)$actual['id_barrio'] !== (int)$datos['id_barrio']
            || (int)$actual['id_comuna'] !== (int)$datos['id_comuna']
            || (int)$actual['id_ciudad'] !== (int)$datos['id_ciudad']
            || (int)$actual['id_departamento'] !== (int)$datos['id_departamento'];
    }

    private function quitarNomenclaturaRepetida($numero, $nomenclatura)
    {
        $abreviaturas = [
            'calle'       => 'calle|cll|cl|clle',
            'carrera'     => 'carrera|cra|cr|kra|kr|carr|crr',
            'avenida'     => 'avenida|av|avda|avd',
            'diagonal'    => 'diagonal|dg|diag',
            'transversal' => 'transversal|tv|tr|trans|transv',
        ];
        $clave = mb_strtolower(trim((string) $nomenclatura), 'UTF-8');
        $patron = $abreviaturas[$clave] ?? preg_quote($clave, '/');

        return trim(preg_replace('/^(?:(?:' . $patron . ')\.?\s+)+/iu', '', $numero));
    }

    private function validar($body, $obj)
    {
        $nombre = limpiar($body['nombre'] ?? '');
        $descripcion = limpiar($body['descripcion'] ?? '');

        $idDepartamento = filter_var($body['id_departamento'] ?? null, FILTER_VALIDATE_INT);
        $idCiudad = filter_var($body['id_ciudad'] ?? null, FILTER_VALIDATE_INT);
        $idComuna = filter_var($body['id_comuna'] ?? null, FILTER_VALIDATE_INT);
        $idBarrio = filter_var($body['id_barrio'] ?? null, FILTER_VALIDATE_INT);
        $idNomenclatura = filter_var($body['id_nomenclatura'] ?? null, FILTER_VALIDATE_INT);

        // Se mantiene como texto porque una dirección puede contener letras,
        // espacios, guiones o numeración como "12 Oeste # 4-20".
        $numeroDireccion = limpiar($body['numero_direccion'] ?? '');

        foreach ([
            validarTexto($nombre, 'Nombre', 3, 100),
            validarTexto($descripcion, 'Descripción', 1, 200),
            validarTexto($numeroDireccion, 'Número de dirección', 1, 200)
        ] as $error) {
            if ($error !== null) {
                jsonResponse(['ok' => false, 'message' => $error], 422);
            }
        }

        if (!$idDepartamento || !$idCiudad || !$idComuna || !$idBarrio || !$idNomenclatura) {
            jsonResponse([
                'ok' => false,
                'message' => 'Debe completar todos los campos de la dirección.'
            ], 422);
        }

        if (!$obj->ubicacionValida($idDepartamento, $idCiudad, $idComuna, $idBarrio)) {
            jsonResponse([
                'ok' => false,
                'message' => 'La combinación de departamento, ciudad, comuna y barrio no es válida.'
            ], 422);
        }

        if (!$obj->nomenclaturaExiste($idNomenclatura)) {
            jsonResponse([
                'ok' => false,
                'message' => 'La nomenclatura seleccionada no existe.'
            ], 422);
        }

        $nomenclatura = $obj->nombreNomenclatura($idNomenclatura);
        if (!$nomenclatura) {
            jsonResponse(['ok' => false, 'message' => 'No se pudo obtener la nomenclatura.'], 422);
        }
        $numeroDireccion = $this->quitarNomenclaturaRepetida($numeroDireccion, $nomenclatura);
        if ($numeroDireccion === '') {
            jsonResponse(['ok' => false, 'message' => 'Escriba el número de la dirección (por ejemplo: 7 # 8-75).'], 422);
        }

        return [
            'nombre' => $nombre,
            'descripcion' => $descripcion,
            'id_departamento' => $idDepartamento,
            'id_ciudad' => $idCiudad,
            'id_comuna' => $idComuna,
            'id_barrio' => $idBarrio,
            'id_nomenclatura' => $idNomenclatura,
            'direccion' => trim($nomenclatura . ' ' . $numeroDireccion)
        ];
    }
}