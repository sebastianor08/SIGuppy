<?php


include_once '../Model/Zoocriadero/ZoocriaderoModel.php';
include_once __DIR__ . '/../../lib/geocodificador.php';

class ZoocriaderoController
{

    // ---------- Lecturas ----------

    public function lista()
    {
        $obj = new ZoocriaderoModel();
        jsonResponse(['ok' => true, 'data' => $obj->listar()]);
    }

    public function comunas()
    {
        $obj = new ZoocriaderoModel();
        jsonResponse(['ok' => true, 'data' => $obj->comunas()]);
    }

    public function barrios()
    {
        $obj = new ZoocriaderoModel();
        $idComuna = filter_var($_GET['id_comuna'] ?? null, FILTER_VALIDATE_INT);

        if (!$idComuna) {
            jsonResponse(['ok' => false, 'message' => 'Debe indicar la comuna.'], 422);
        }
        jsonResponse(['ok' => true, 'data' => $obj->barriosDe($idComuna)]);
    }

    public function tiposTanque()
    {
        $obj = new ZoocriaderoModel();
        jsonResponse(['ok' => true, 'data' => $obj->tiposTanque()]);
    }

    public function tanques()
    {
        $obj = new ZoocriaderoModel();
        $idZoo = filter_var($_GET['id_zoocriadero'] ?? null, FILTER_VALIDATE_INT);

        if (!$idZoo) {
            jsonResponse(['ok' => false, 'message' => 'id_zoocriadero es obligatorio.'], 422);
        }
        jsonResponse(['ok' => true, 'data' => $obj->tanquesDe($idZoo)]);
    }

    // ---------- Escrituras ----------

    public function postCreate()
    {
        sigExigirPermiso('Zoocriaderos', 'crear');
        $obj = new ZoocriaderoModel();
        $body = requestJsonBody();
        $datos = $this->validarZoocriadero($body, $obj, null);

        $id = $obj->crear($datos);
        if ($id === null) {
            jsonResponse(['ok' => false, 'message' => 'No se pudo registrar: ' . $obj->ultimoError()], 500);
        }

        jsonResponse([
            'ok' => true,
            'message' => 'Zoocriadero registrado correctamente.',
            'id_zoocriadero' => (int) $id,
        ], 201);
    }

    public function postUpdate()
    {
        sigExigirPermiso('Zoocriaderos', 'editar');
        $obj = new ZoocriaderoModel();
        $body = requestJsonBody();

        $idZoo = filter_var($body['id_zoocriadero'] ?? null, FILTER_VALIDATE_INT);
        if (!$idZoo) {
            jsonResponse(['ok' => false, 'message' => 'id_zoocriadero es obligatorio.'], 422);
        }
        if (!$obj->buscar($idZoo)) {
            jsonResponse(['ok' => false, 'message' => 'El zoocriadero no existe.'], 404);
        }

        $datos = $this->validarZoocriadero($body, $obj, $idZoo);

        if ($obj->actualizar($idZoo, $datos) === false) {
            jsonResponse(['ok' => false, 'message' => 'No se pudo actualizar: ' . $obj->ultimoError()], 500);
        }

        jsonResponse(['ok' => true, 'message' => 'Zoocriadero actualizado correctamente.']);
    }

    public function postEstado()
    {
        sigExigirPermiso('Zoocriaderos', 'inhabilitar');
        $obj = new ZoocriaderoModel();
        $body = requestJsonBody();

        $idZoo = filter_var($body['id_zoocriadero'] ?? null, FILTER_VALIDATE_INT);
        $estado = filter_var($body['estado'] ?? null, FILTER_VALIDATE_INT);

        if (!$idZoo || ($estado !== 0 && $estado !== 1)) {
            jsonResponse(['ok' => false, 'message' => 'Datos incompletos para cambiar el estado.'], 422);
        }

        if ($obj->cambiarEstado($idZoo, $estado) === false) {
            jsonResponse(['ok' => false, 'message' => 'No se pudo cambiar el estado.'], 500);
        }

        jsonResponse([
            'ok' => true,
            'message' => $estado === 1 ? 'Zoocriadero habilitado.' : 'Zoocriadero inhabilitado.',
        ]);
    }

    // ---------- Validación compartida por create y update ----------
    private function validarZoocriadero($body, $obj, $idActual = null)
    {
        $nombre = limpiar($body['nombre'] ?? '');
        $direccion = limpiar($body['direccion'] ?? '');
        $comuna = limpiar($body['comuna'] ?? '');
        $barrio = limpiar($body['barrio'] ?? '');
        $latitud = $body['latitud'] ?? null;
        $longitud = $body['longitud'] ?? null;

        foreach ([
            validarTexto($nombre, 'Nombre', 4, 100),
            validarTexto($direccion, 'Dirección', 5, 200),
            validarTextoOpcional($comuna, 'Comuna', 60),
            validarTextoOpcional($barrio, 'Barrio', 60),
        ] as $error) {
            if ($error !== null) {
                jsonResponse(['ok' => false, 'message' => $error], 422);
            }
        }

        // Nombre duplicado (se revisa antes de geocodificar para responder rápido)
        $duplicado = $obj->buscarDuplicado($nombre, $idActual);
        if ($duplicado) {
            $mensaje = 'Ya existe un zoocriadero llamado "' . $duplicado['nombre'] . '".';
            if ((int) $duplicado['estado'] !== 1) {
                $mensaje .= ' Está inhabilitado: puede habilitarlo desde la lista en lugar de crearlo de nuevo.';
            }
            jsonResponse(['ok' => false, 'message' => $mensaje], 409);
        }

        if ($comuna !== '' && $barrio !== '' && !$obj->barrioPerteneceAComuna($barrio, $comuna)) {
            jsonResponse(['ok' => false, 'message' => 'El barrio seleccionado no pertenece a esa comuna.'], 422);
        }

        // Si no llegan coordenadas manuales, se geocodifica la dirección
        // automáticamente (Nominatim/OpenStreetMap).
        if (!is_numeric($latitud) || !is_numeric($longitud)) {
            $punto = geocodificarDireccion($direccion, $barrio, $comuna);
            $latitud = $punto['lat'] ?? 0;
            $longitud = $punto['lng'] ?? 0;
        } else {
            $latitud = (float) $latitud;
            $longitud = (float) $longitud;
        }

        return [
            'nombre' => $nombre,
            'direccion' => $direccion,
            'comuna' => ($comuna !== '' ? $comuna : null),
            'barrio' => ($barrio !== '' ? $barrio : null),
            'latitud' => $latitud,
            'longitud' => $longitud,
        ];
    }
}