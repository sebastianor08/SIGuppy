<?php

include_once '../Model/Acciones/AccionesModel.php';


class AccionesController{

 

    public function lista(){
        $obj = new AccionesModel();
        jsonResponse(['ok' => true, 'data' => $obj->listar()]);
    }

 

    public function postCreate(){
        $obj   = new AccionesModel();
        $body  = requestJsonBody();
        $datos = $this->validarAccion($body, $obj, null);

        $id = $obj->crear($datos);
        if($id === null){
            jsonResponse(['ok' => false, 'message' => 'No se pudo registrar: ' . $obj->ultimoError()], 500);
        }

        jsonResponse([
            'ok' => true,
            'message' => 'Acción registrada correctamente.',
            'id_actividad' => (int) $id,
        ], 201);
    }

    public function postUpdate(){
        $obj  = new AccionesModel();
        $body = requestJsonBody();

        $idActividad = filter_var($body['id_actividad'] ?? null, FILTER_VALIDATE_INT);
        if(!$idActividad){
            jsonResponse(['ok' => false, 'message' => 'id_actividad es obligatorio.'], 422);
        }
        if(!$obj->buscar($idActividad)){
            jsonResponse(['ok' => false, 'message' => 'La acción no existe.'], 404);
        }

        $datos = $this->validarAccion($body, $obj, $idActividad);

        if($obj->actualizar($idActividad, $datos) === false){
            jsonResponse(['ok' => false, 'message' => 'No se pudo actualizar: ' . $obj->ultimoError()], 500);
        }

        jsonResponse(['ok' => true, 'message' => 'Acción actualizada correctamente.']);
    }

    public function postEstado(){
        $obj  = new AccionesModel();
        $body = requestJsonBody();

        $idActividad = filter_var($body['id_actividad'] ?? null, FILTER_VALIDATE_INT);
        $estado      = filter_var($body['estado'] ?? null, FILTER_VALIDATE_INT);

        if(!$idActividad || ($estado !== 0 && $estado !== 1)){
            jsonResponse(['ok' => false, 'message' => 'Datos incompletos para cambiar el estado.'], 422);
        }
        if(!$obj->buscar($idActividad)){
            jsonResponse(['ok' => false, 'message' => 'La acción no existe.'], 404);
        }

        if($obj->cambiarEstado($idActividad, $estado) === false){
            jsonResponse(['ok' => false, 'message' => 'No se pudo cambiar el estado.'], 500);
        }

        jsonResponse([
            'ok' => true,
            'message' => $estado === 1 ? 'Acción habilitada.' : 'Acción inhabilitada.',
        ]);
    }


    private function validarAccion($body, $obj, $idExcluir){
        $nombre      = trim((string) ($body['nombre'] ?? ''));
        $descripcion = trim((string) ($body['descripcion'] ?? ''));
        $estado      = filter_var($body['estado'] ?? 1, FILTER_VALIDATE_INT);

        if($nombre === ''){
            jsonResponse(['ok' => false, 'message' => 'El nombre de la acción es obligatorio.'], 422);
        }
        if($estado !== 0 && $estado !== 1){
            $estado = 1;
        }
        if($obj->existeNombre($nombre, $idExcluir)){
            jsonResponse(['ok' => false, 'message' => 'Ya existe una acción con ese nombre.'], 422);
        }

        return [
            'nombre'      => $nombre,
            'descripcion' => ($descripcion !== '' ? $descripcion : null),
            'estado'      => $estado,
        ];
    }
}

?>