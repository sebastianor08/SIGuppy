<?php

include_once '../Model/Actividad/ActividadModel.php';

// Responde solo JSON. Se llama por Web/ajax.php:
//   ?modulo=Actividad&controlador=Actividad&funcion=lista
class ActividadController
{
    public function lista()
    {
        $obj = new ActividadModel();
        jsonResponse(['ok' => true, 'data' => $obj->listar()]);
    }

    public function postCreate()
    {
        $obj = new ActividadModel();
        $body = requestJsonBody();
        $datos = $this->validar($body, $obj, null);

        $id = $obj->crear($datos);

        if ($id === null) {
            jsonResponse([
                'ok' => false,
                'message' => 'No se pudo registrar la actividad: ' . $obj->ultimoError()
            ], 500);
        }

        jsonResponse([
            'ok' => true,
            'message' => 'Actividad registrada correctamente.',
            'id_actividad' => (int)$id
        ], 201);
    }

    public function postUpdate()
    {
        $obj = new ActividadModel();
        $body = requestJsonBody();

        $id = filter_var($body['id_actividad'] ?? null, FILTER_VALIDATE_INT);
        if (!$id) {
            jsonResponse(['ok' => false, 'message' => 'id_actividad es obligatorio.'], 422);
        }

        if (!$obj->buscar($id)) {
            jsonResponse(['ok' => false, 'message' => 'La actividad no existe.'], 404);
        }

        $datos = $this->validar($body, $obj, $id);

        if (!$obj->actualizar($id, $datos)) {
            jsonResponse([
                'ok' => false,
                'message' => 'No se pudo actualizar la actividad: ' . $obj->ultimoError()
            ], 500);
        }

        jsonResponse(['ok' => true, 'message' => 'Actividad actualizada correctamente.']);
    }

    public function postEstado()
    {
        $obj = new ActividadModel();
        $body = requestJsonBody();

        $id = filter_var($body['id_actividad'] ?? null, FILTER_VALIDATE_INT);
        $estado = filter_var($body['estado'] ?? null, FILTER_VALIDATE_INT);

        if (!$id || ($estado !== 0 && $estado !== 1)) {
            jsonResponse(['ok' => false, 'message' => 'Datos incompletos para cambiar el estado.'], 422);
        }

        if (!$obj->buscar($id)) {
            jsonResponse(['ok' => false, 'message' => 'La actividad no existe.'], 404);
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
                ? 'Actividad habilitada.'
                : 'Actividad inhabilitada.'
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
                'message' => 'Ya existe una actividad con ese nombre.'
            ], 422);
        }

        return [
            'nombre' => $nombre,
            'descripcion' => $descripcion
        ];
    }
}
