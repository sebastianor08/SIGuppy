<?php

include_once '../Model/Deposito/DepositoModel.php';

// ============================================================
// Controlador del módulo Depósitos. Responde solo JSON:
//   Web/ajax.php?modulo=Deposito&controlador=Deposito&funcion=lista
//
// El depósito es un registro de la tabla "sitio" (id_sitio,
// id_tipo_deposito, id_direccion, estado).
// ============================================================
class DepositoController
{
    public function lista()
    {
        $obj = new DepositoModel();

        jsonResponse([
            'ok' => true,
            'data' => $obj->listar()
        ]);
    }

    public function tipos()
    {
        $obj = new DepositoModel();

        jsonResponse([
            'ok' => true,
            'data' => $obj->tiposActivos()
        ]);
    }

    public function direcciones()
    {
        $obj = new DepositoModel();

        jsonResponse([
            'ok' => true,
            'data' => $obj->direcciones()
        ]);
    }

    public function postCreate()
    {
        $obj = new DepositoModel();
        $body = requestJsonBody();

        $datos = $this->validar($body, $obj);

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
            'id_sitio' => (int) $id
        ], 201);
    }

    public function postUpdate()
    {
        $obj = new DepositoModel();
        $body = requestJsonBody();

        $id = filter_var($body['id_sitio'] ?? null, FILTER_VALIDATE_INT);

        if (!$id) {
            jsonResponse([
                'ok' => false,
                'message' => 'id_sitio es obligatorio.'
            ], 422);
        }

        if (!$obj->buscar($id)) {
            jsonResponse([
                'ok' => false,
                'message' => 'El depósito no existe.'
            ], 404);
        }

        $datos = $this->validar($body, $obj);

        if ($obj->actualizar($id, $datos) === false) {
            jsonResponse([
                'ok' => false,
                'message' => 'No se pudo actualizar el depósito: ' . $obj->ultimoError()
            ], 500);
        }

        jsonResponse([
            'ok' => true,
            'message' => 'Depósito actualizado correctamente.'
        ]);
    }

    public function postEstado()
    {
        $obj = new DepositoModel();
        $body = requestJsonBody();

        $id = filter_var($body['id_sitio'] ?? null, FILTER_VALIDATE_INT);
        $estado = filter_var($body['estado'] ?? null, FILTER_VALIDATE_INT);

        if (!$id || ($estado !== 0 && $estado !== 1)) {
            jsonResponse([
                'ok' => false,
                'message' => 'Datos incompletos para cambiar el estado.'
            ], 422);
        }

        if (!$obj->buscar($id)) {
            jsonResponse([
                'ok' => false,
                'message' => 'El depósito no existe.'
            ], 404);
        }

        if ($obj->cambiarEstado($id, $estado) === false) {
            jsonResponse([
                'ok' => false,
                'message' => 'No se pudo cambiar el estado del depósito.'
            ], 500);
        }

        jsonResponse([
            'ok' => true,
            'message' => $estado === 1
                ? 'Depósito habilitado.'
                : 'Depósito inhabilitado.'
        ]);
    }

    private function validar($body, $obj)
    {
        $idTipo = filter_var(
            $body['id_tipo_deposito'] ?? null,
            FILTER_VALIDATE_INT
        );

        $idDireccion = filter_var(
            $body['id_direccion'] ?? null,
            FILTER_VALIDATE_INT
        );

        if (!$idTipo || !$obj->tipoExiste($idTipo)) {
            jsonResponse([
                'ok' => false,
                'message' => 'Debe seleccionar un tipo de depósito válido.'
            ], 422);
        }

        if (!$idDireccion || !$obj->direccionExiste($idDireccion)) {
            jsonResponse([
                'ok' => false,
                'message' => 'Debe seleccionar una dirección válida.'
            ], 422);
        }

        return [
            'id_tipo_deposito' => $idTipo,
            'id_direccion' => $idDireccion,
        ];
    }
}
