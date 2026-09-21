<?php

include_once '../Model/TipoDeposito/TipoDepositoModel.php';

// Responde solo JSON. Se llama por Web/ajax.php:
//   ?modulo=TipoDeposito&controlador=TipoDeposito&funcion=lista
class TipoDepositoController
{
    public function lista()
    {
        $obj = new TipoDepositoModel();
        jsonResponse(['ok' => true, 'data' => $obj->listar()]);
    }

    public function postCreate()
    {
        $obj = new TipoDepositoModel();
        $body = requestJsonBody();
        $datos = $this->validar($body, $obj, null);

        $id = $obj->crear($datos);

        if ($id === null) {
            jsonResponse([
                'ok' => false,
                'message' => 'No se pudo registrar el tipo de depósito: ' . $obj->ultimoError()
            ], 500);
        }

        jsonResponse([
            'ok' => true,
            'message' => 'Tipo de depósito registrado correctamente.',
            'id_tipo_deposito' => (int)$id
        ], 201);
    }

    public function postUpdate()
    {
        $obj = new TipoDepositoModel();
        $body = requestJsonBody();

        $id = filter_var($body['id_tipo_deposito'] ?? null, FILTER_VALIDATE_INT);
        if (!$id) {
            jsonResponse(['ok' => false, 'message' => 'id_tipo_deposito es obligatorio.'], 422);
        }

        if (!$obj->buscar($id)) {
            jsonResponse(['ok' => false, 'message' => 'El tipo de depósito no existe.'], 404);
        }

        $datos = $this->validar($body, $obj, $id);

        if (!$obj->actualizar($id, $datos)) {
            jsonResponse([
                'ok' => false,
                'message' => 'No se pudo actualizar el tipo de depósito: ' . $obj->ultimoError()
            ], 500);
        }

        jsonResponse(['ok' => true, 'message' => 'Tipo de depósito actualizado correctamente.']);
    }

    public function postEstado()
    {
        $obj = new TipoDepositoModel();
        $body = requestJsonBody();

        $id = filter_var($body['id_tipo_deposito'] ?? null, FILTER_VALIDATE_INT);
        $estado = filter_var($body['estado'] ?? null, FILTER_VALIDATE_INT);

        if (!$id || ($estado !== 0 && $estado !== 1)) {
            jsonResponse(['ok' => false, 'message' => 'Datos incompletos para cambiar el estado.'], 422);
        }

        if (!$obj->buscar($id)) {
            jsonResponse(['ok' => false, 'message' => 'El tipo de depósito no existe.'], 404);
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
                ? 'Tipo de depósito habilitado.'
                : 'Tipo de depósito inhabilitado.'
        ]);
    }

    private function validar($body, $obj, $idExcluir)
    {
        $nombre = limpiar($body['nombre'] ?? '');
        $descripcion = limpiar($body['descripcion'] ?? '');

        // Los máximos coinciden con la BD: nombre varchar(60), descripcion varchar(200).
        foreach ([
            validarTexto($nombre, 'Nombre', 3, 60),
            validarTexto($descripcion, 'Descripción', 1, 200)
        ] as $error) {
            if ($error !== null) {
                jsonResponse(['ok' => false, 'message' => $error], 422);
            }
        }

        if ($obj->existeNombre($nombre, $idExcluir)) {
            jsonResponse([
                'ok' => false,
                'message' => 'Ya existe un tipo de depósito con ese nombre.'
            ], 422);
        }

        return [
            'nombre' => $nombre,
            'descripcion' => $descripcion
        ];
    }
}
