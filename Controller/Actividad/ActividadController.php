<?php

include_once '../Model/Actividad/ActividadModel.php';

class ActividadController{

    public function lista(){
        $obj = new ActividadModel();
        jsonResponse(['ok' => true, 'data' => $obj->listar()]);
    }

    public function postCreate(){
        $obj   = new ActividadModel();
        $body  = requestJsonBody();
        $datos = $this->validarActividad($body, $obj, null);

        $id = $obj->crear($datos);
        if($id === null){
            jsonResponse(['ok' => false, 'message' => 'No se pudo registrar: ' . $obj->ultimoError()], 500);
        }

        jsonResponse([
            'ok' => true,
            'message' => 'Actividad registrada correctamente.',
            'id_actividad' => (int) $id,
        ], 201);
    }

    public function postUpdate(){
        $obj  = new ActividadModel();
        $body = requestJsonBody();

        $idActividad = filter_var($body['id_actividad'] ?? null, FILTER_VALIDATE_INT);
        if(!$idActividad){
            jsonResponse(['ok' => false, 'message' => 'id_actividad es obligatorio.'], 422);
        }
        if(!$obj->buscar($idActividad)){
            jsonResponse(['ok' => false, 'message' => 'La actividad no existe.'], 404);
        }

        $datos = $this->validarActividad($body, $obj, $idActividad);

        if($obj->actualizar($idActividad, $datos) === false){
            jsonResponse(['ok' => false, 'message' => 'No se pudo actualizar: ' . $obj->ultimoError()], 500);
        }

        jsonResponse(['ok' => true, 'message' => 'Actividad actualizada correctamente.']);
    }

    public function postEstado(){
        $obj  = new ActividadModel();
        $body = requestJsonBody();

        $idActividad = filter_var($body['id_actividad'] ?? null, FILTER_VALIDATE_INT);
        $estado      = filter_var($body['estado'] ?? null, FILTER_VALIDATE_INT);

        if(!$idActividad || ($estado !== 0 && $estado !== 1)){
            jsonResponse(['ok' => false, 'message' => 'Datos incompletos para cambiar el estado.'], 422);
        }
        if(!$obj->buscar($idActividad)){
            jsonResponse(['ok' => false, 'message' => 'La actividad no existe.'], 404);
        }

        if($obj->cambiarEstado($idActividad, $estado) === false){
            jsonResponse(['ok' => false, 'message' => 'No se pudo cambiar el estado.'], 500);
        }

        jsonResponse([
            'ok' => true,
            'message' => $estado === 1 ? 'Actividad habilitada.' : 'Actividad inhabilitada.',
        ]);
    }

    private function validarActividad($body, $obj, $idExcluir){
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
            jsonResponse(['ok' => false, 'message' => 'Ya existe una actividad con ese nombre.'], 422);
        }

        return [
            'nombre'      => $nombre,
            'descripcion' => ($descripcion !== '' ? $descripcion : null),
            'estado'      => $estado,
        ];
    }
}
