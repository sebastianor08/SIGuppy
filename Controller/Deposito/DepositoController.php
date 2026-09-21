<?php

include_once '../Model/Deposito/DepositoModel.php';

// Responde solo JSON. Se llama por Web/ajax.php:
//   ?modulo=Deposito&controlador=Deposito&funcion=lista
//
// Un depósito es un registro de la tabla "deposito" (tipo + sitio + descripción).
// La dirección no se captura aquí: viene del sitio al que pertenece.
class DepositoController
{
    public function lista()
    {
        $obj = new DepositoModel();
        jsonResponse(['ok' => true, 'data' => $obj->listar()]);
    }

    public function catalogos()
    {
        $obj = new DepositoModel();

        jsonResponse([
            'ok' => true,
            'data' => [
                'tipos' => $obj->tipos(),
                'sitios' => $obj->sitios()
            ]
        ]);
    }

    public function postCreate()
    {
        $obj = new DepositoModel();
        $body = requestJsonBody();
        $datos = $this->validar($body, $obj, null);

        $id = $obj->crear($datos);

        if ($id === null) {
            jsonResponse([
                'ok' => false,
                'message' => 'No se pudo registrar el depósito: ' . $obj->ultimoError()
            ], 500);
        }

        jsonResponse([
            'ok' => true,
            'message' => 'Depósito registrado correctamente.',
            'id_deposito' => (int)$id
        ], 201);
    }

    public function postUpdate()
    {
        $obj = new DepositoModel();
        $body = requestJsonBody();

        $id = filter_var($body['id_deposito'] ?? null, FILTER_VALIDATE_INT);
        if (!$id) {
            jsonResponse(['ok' => false, 'message' => 'id_deposito es obligatorio.'], 422);
        }

        $actual = $obj->buscar($id);
        if (!$actual) {
            jsonResponse(['ok' => false, 'message' => 'El depósito no existe.'], 404);
        }

        $datos = $this->validar($body, $obj, $actual);

        if (!$obj->actualizar($id, $datos)) {
            jsonResponse([
                'ok' => false,
                'message' => 'No se pudo actualizar el depósito: ' . $obj->ultimoError()
            ], 500);
        }

        jsonResponse(['ok' => true, 'message' => 'Depósito actualizado correctamente.']);
    }

    public function postEstado()
    {
        $obj = new DepositoModel();
        $body = requestJsonBody();

        $id = filter_var($body['id_deposito'] ?? null, FILTER_VALIDATE_INT);
        $estado = filter_var($body['estado'] ?? null, FILTER_VALIDATE_INT);

        if (!$id || ($estado !== 0 && $estado !== 1)) {
            jsonResponse(['ok' => false, 'message' => 'Datos incompletos para cambiar el estado.'], 422);
        }

        if (!$obj->buscar($id)) {
            jsonResponse(['ok' => false, 'message' => 'El depósito no existe.'], 404);
        }

        if (!$obj->cambiarEstado($id, $estado)) {
            jsonResponse([
                'ok' => false,
                'message' => 'No se pudo cambiar el estado: ' . $obj->ultimoError()
            ], 500);
        }

        jsonResponse([
            'ok' => true,
            'message' => $estado === 1
                ? 'Depósito habilitado.'
                : 'Depósito inhabilitado.'
        ]);
    }

    // $actual = fila actual del depósito al editar (null al crear).
    private function validar($body, $obj, $actual)
    {
        $idTipo = filter_var($body['id_tipo_deposito'] ?? null, FILTER_VALIDATE_INT);
        $idSitio = filter_var($body['id_sitio'] ?? null, FILTER_VALIDATE_INT);
        $descripcion = limpiar($body['descripcion'] ?? '');

        // Máximo coincide con la BD: descripcion varchar(200).
        $error = validarTexto($descripcion, 'Descripción', 1, 200);
        if ($error !== null) {
            jsonResponse(['ok' => false, 'message' => $error], 422);
        }

        if (!$idTipo || !$obj->tipoDisponible($idTipo, $actual['id_tipo_deposito'] ?? null)) {
            jsonResponse([
                'ok' => false,
                'message' => 'Debe seleccionar un tipo de depósito válido y habilitado.'
            ], 422);
        }

        if (!$idSitio || !$obj->sitioDisponible($idSitio, $actual['id_sitio'] ?? null)) {
            jsonResponse([
                'ok' => false,
                'message' => 'Debe seleccionar un sitio válido y habilitado.'
            ], 422);
        }

        return [
            'id_tipo_deposito' => $idTipo,
            'id_sitio' => $idSitio,
            'descripcion' => $descripcion
        ];
    }
}
