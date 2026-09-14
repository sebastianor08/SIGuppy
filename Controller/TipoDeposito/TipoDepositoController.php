<?php

include_once '../Model/TipoDeposito/TipoDepositoModel.php';

// ============================================================
// Controlador del módulo Tipo Depósito. Responde solo JSON, así
// que se llama siempre por Web/ajax.php:
//   Web/ajax.php?modulo=TipoDeposito&controlador=TipoDeposito&funcion=lista
// ============================================================
class TipoDepositoController{

    public function lista(){
        $obj = new TipoDepositoModel();
        jsonResponse(['ok' => true, 'data' => $obj->listar()]);
    }

    public function postCreate(){
        $obj   = new TipoDepositoModel();
        $body  = requestJsonBody();
        $datos = $this->validarTipoDeposito($body, $obj, null);

        $id = $obj->crear($datos);
        if($id === null){
            jsonResponse(['ok' => false, 'message' => 'No se pudo registrar: ' . $obj->ultimoError()], 500);
        }

        jsonResponse([
            'ok' => true,
            'message' => 'Tipo de depósito registrado correctamente.',
            'id_tipo_deposito' => (int) $id,
        ], 201);
    }

    public function postUpdate(){
        $obj  = new TipoDepositoModel();
        $body = requestJsonBody();

        $idTipoDeposito = filter_var($body['id_tipo_deposito'] ?? null, FILTER_VALIDATE_INT);
        if(!$idTipoDeposito){
            jsonResponse(['ok' => false, 'message' => 'id_tipo_deposito es obligatorio.'], 422);
        }
        if(!$obj->buscar($idTipoDeposito)){
            jsonResponse(['ok' => false, 'message' => 'El tipo de depósito no existe.'], 404);
        }

        $datos = $this->validarTipoDeposito($body, $obj, $idTipoDeposito);

        if($obj->actualizar($idTipoDeposito, $datos) === false){
            jsonResponse(['ok' => false, 'message' => 'No se pudo actualizar: ' . $obj->ultimoError()], 500);
        }

        jsonResponse(['ok' => true, 'message' => 'Tipo de depósito actualizado correctamente.']);
    }

    public function postEstado(){
        $obj  = new TipoDepositoModel();
        $body = requestJsonBody();

        $idTipoDeposito = filter_var($body['id_tipo_deposito'] ?? null, FILTER_VALIDATE_INT);
        $estado         = filter_var($body['estado'] ?? null, FILTER_VALIDATE_INT);

        if(!$idTipoDeposito || ($estado !== 0 && $estado !== 1)){
            jsonResponse(['ok' => false, 'message' => 'Datos incompletos para cambiar el estado.'], 422);
        }
        if(!$obj->buscar($idTipoDeposito)){
            jsonResponse(['ok' => false, 'message' => 'El tipo de depósito no existe.'], 404);
        }

        if($obj->cambiarEstado($idTipoDeposito, $estado) === false){
            jsonResponse(['ok' => false, 'message' => 'No se pudo cambiar el estado.'], 500);
        }

        jsonResponse([
            'ok' => true,
            'message' => $estado === 1 ? 'Tipo de depósito habilitado.' : 'Tipo de depósito inhabilitado.',
        ]);
    }

    private function validarTipoDeposito($body, $obj, $idExcluir){
        $nombre      = limpiar($body['nombre'] ?? '');
        $descripcion = limpiar($body['descripcion'] ?? '');
        $estado      = filter_var($body['estado'] ?? 1, FILTER_VALIDATE_INT);

        $errorNombre = validarTexto($nombre, 'Nombre', 3, 100);
        if($errorNombre !== null){
            jsonResponse(['ok' => false, 'message' => $errorNombre], 422);
        }
        $errorDescripcion = validarTextoOpcional($descripcion, 'Descripción', 200);
        if($errorDescripcion !== null){
            jsonResponse(['ok' => false, 'message' => $errorDescripcion], 422);
        }
        if($estado !== 0 && $estado !== 1){
            $estado = 1;
        }
        if($obj->existeNombre($nombre, $idExcluir)){
            jsonResponse(['ok' => false, 'message' => 'Ya existe un tipo de depósito con ese nombre.'], 422);
        }

        return [
            'nombre'      => $nombre,
            'descripcion' => ($descripcion !== '' ? $descripcion : null),
            'estado'      => $estado,
        ];
    }
}
