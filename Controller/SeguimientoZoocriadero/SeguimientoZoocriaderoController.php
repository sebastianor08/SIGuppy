<?php

include_once '../Model/SeguimientoZoocriadero/SeguimientoZoocriaderoModel.php';

// Este controlador solo responde JSON (nada de vistas HTML), así que se
// llama siempre a través de web/ajax.php, nunca de web/Index.php:
//   web/ajax.php?modulo=SeguimientoZoocriadero&controlador=SeguimientoZoocriadero&funcion=zoocriaderos
// web/Index.php envuelve la respuesta en el layout (head/navbar/footer),
// lo que rompería el JSON; ajax.php no agrega nada alrededor.
class SeguimientoZoocriaderoController{

    public function zoocriaderos(){
        $obj = new SeguimientoZoocriaderoModel();
        jsonResponse(['ok' => true, 'data' => $obj->zoocriaderosActivos()]);
    }

    public function tanques(){
        $obj = new SeguimientoZoocriaderoModel();
        $idZoo = filter_var($_GET['id_zoocriadero'] ?? null, FILTER_VALIDATE_INT);
        if($idZoo === false || $idZoo === null){
            jsonResponse(['ok' => false, 'message' => 'id_zoocriadero es obligatorio y debe ser entero.'], 422);
        }
        jsonResponse(['ok' => true, 'data' => $obj->tanquesPorZoocriadero($idZoo)]);
    }

    public function acciones(){
        $obj = new SeguimientoZoocriaderoModel();
        jsonResponse(['ok' => true, 'data' => $obj->accionesActivas()]);
    }

    public function historial(){
        $obj = new SeguimientoZoocriaderoModel();
        jsonResponse(['ok' => true, 'data' => $obj->historial()]);
    }

    public function postCreate(){
        $obj = new SeguimientoZoocriaderoModel();
        $body = requestJsonBody();

        $idZoo = filter_var($body['id_zoocriadero'] ?? null, FILTER_VALIDATE_INT);
        $idTanque = filter_var($body['id_tanque'] ?? null, FILTER_VALIDATE_INT);
        $idActividad = filter_var($body['id_actividad'] ?? null, FILTER_VALIDATE_INT);
        $numeroNacidos = filter_var($body['numero_nacidos'] ?? 0, FILTER_VALIDATE_INT);
        $numeroMuertos = filter_var($body['numero_muertos'] ?? 0, FILTER_VALIDATE_INT);
        $numeroSembrados = filter_var($body['numero_sembrados'] ?? 0, FILTER_VALIDATE_INT);
        $fecha = trim((string)($body['fecha'] ?? ''));
        $observaciones = trim((string)($body['observaciones'] ?? ''));

        if($idZoo === false || $idZoo === null){
            jsonResponse(['ok' => false, 'message' => 'id_zoocriadero es obligatorio.'], 422);
        }
        if($idTanque === false || $idTanque === null){
            jsonResponse(['ok' => false, 'message' => 'id_tanque es obligatorio.'], 422);
        }
        if($idActividad === false || $idActividad === null){
            jsonResponse(['ok' => false, 'message' => 'id_actividad (Acción) es obligatorio.'], 422);
        }
        if($numeroNacidos === false || $numeroNacidos < 0 || $numeroMuertos === false || $numeroMuertos < 0 || $numeroSembrados === false || $numeroSembrados < 0){
            jsonResponse(['ok' => false, 'message' => 'Los conteos de peces no pueden ser negativos.'], 422);
        }
        $fechaObj = DateTime::createFromFormat('Y-m-d', $fecha);
        if(!$fechaObj || $fechaObj->format('Y-m-d') !== $fecha){
            jsonResponse(['ok' => false, 'message' => 'La fecha no tiene un formato válido (YYYY-MM-DD).'], 422);
        }
        if(strlen($observaciones) > 300){
            jsonResponse(['ok' => false, 'message' => 'Las observaciones no pueden superar 300 caracteres.'], 422);
        }

        // Reglas de negocio: el tanque debe pertenecer al zoocriadero elegido,
        // el zoocriadero debe existir y estar activo, y la acción debe ser válida.
        // (El propio esquema también lo obliga con la FK compuesta
        // fk_seg_zoo_tanque_zoocriadero, esto es una segunda barrera con
        // mensajes claros para el usuario).
        if(!$obj->zoocriaderoActivoExiste($idZoo)){
            jsonResponse(['ok' => false, 'message' => 'El zoocriadero seleccionado no existe o está inhabilitado.'], 422);
        }
        if(!$obj->tanquePerteneceAZoocriadero($idTanque, $idZoo)){
            jsonResponse(['ok' => false, 'message' => 'El tanque seleccionado no pertenece al zoocriadero elegido.'], 422);
        }
        if(!$obj->accionValida($idActividad)){
            jsonResponse(['ok' => false, 'message' => 'La acción seleccionada no es válida.'], 422);
        }

        // Aún no hay login obligatorio en este módulo: si hay sesión iniciada
        // (AccesoController) se usa ese usuario; si no, se usa el primer
        // usuario activo de la BD como responsable del registro.
        $idUsuario = $_SESSION['id'] ?? $obj->primerUsuarioActivo();
        if(!$idUsuario){
            jsonResponse(['ok' => false, 'message' => 'No hay usuarios activos en la base de datos para registrar el seguimiento.'], 422);
        }

        try{
            $obj->beginTransaction();
            $idSeguimiento = $obj->crearSeguimiento([
                'id_zoocriadero' => $idZoo,
                'id_tanque' => $idTanque,
                'id_usuario' => $idUsuario,
                'fecha' => $fecha,
                'numero_sembrados' => $numeroSembrados,
                'numero_nacidos' => $numeroNacidos,
                'numero_muertos' => $numeroMuertos,
                'observaciones' => $observaciones,
            ]);
            $obj->vincularActividad($idSeguimiento, $idActividad);
            $obj->commit();
        }catch(Throwable $e){
            $obj->rollBack();
            error_log("Error al registrar seguimiento: " . $e->getMessage());
            jsonResponse(['ok' => false, 'message' => 'Error del servidor al guardar el seguimiento.'], 500);
        }

        jsonResponse([
            'ok' => true,
            'message' => 'Seguimiento de zoocriadero registrado correctamente.',
            'id_seguimiento' => $idSeguimiento,
        ], 201);
    }
}

?>
