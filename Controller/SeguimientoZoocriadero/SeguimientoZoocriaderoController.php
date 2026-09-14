<?php

include_once '../Model/SeguimientoZoocriadero/SeguimientoZoocriaderoModel.php';

// Este controlador solo responde JSON (nada de vistas HTML), así que se
// llama siempre a través de Web/ajax.php, nunca de Web/index.php:
//   Web/ajax.php?modulo=SeguimientoZoocriadero&controlador=SeguimientoZoocriadero&funcion=zoocriaderos
// Web/index.php envuelve la respuesta en el layout (head/navbar/footer),
// lo que rompería el JSON; ajax.php no agrega nada alrededor.
class SeguimientoZoocriaderoController {

    public function zoocriaderos() {
        $obj = new SeguimientoZoocriaderoModel();
        jsonResponse(['ok' => true, 'data' => $obj->zoocriaderosActivos()]);
    }

    public function tanques() {
        $obj = new SeguimientoZoocriaderoModel();
        $idZoo = filter_var($_GET['id_zoocriadero'] ?? null, FILTER_VALIDATE_INT);
        if ($idZoo === false || $idZoo === null) {
            jsonResponse(['ok' => false, 'message' => 'id_zoocriadero es obligatorio y debe ser entero.'], 422);
        }
        jsonResponse(['ok' => true, 'data' => $obj->tanquesPorZoocriadero($idZoo)]);
    }

    public function acciones() {
        $obj = new SeguimientoZoocriaderoModel();
        jsonResponse(['ok' => true, 'data' => $obj->accionesActivas()]);
    }

    public function historial() {
        $obj = new SeguimientoZoocriaderoModel();
        jsonResponse(['ok' => true, 'data' => $obj->historial()]);
    }

    public function postCreate() {
        $obj  = new SeguimientoZoocriaderoModel();
        $body = requestJsonBody();
        $datos = $this->validar($body, $obj);

        // Aún no hay login obligatorio en este módulo: si hay sesión iniciada
        // se usa ese usuario; si no, el primer usuario activo de la BD.
        $idUsuario = $_SESSION['id'] ?? $obj->primerUsuarioActivo();
        if (!$idUsuario) {
            jsonResponse(['ok' => false, 'message' => 'No hay usuarios activos en la base de datos para registrar el seguimiento.'], 422);
        }
        $datos['id_usuario'] = $idUsuario;

        try {
            $obj->beginTransaction();
            $idSeguimiento = $obj->crearSeguimiento($datos);      // INSERT
            $obj->vincularActividad($idSeguimiento, $datos['id_actividad']);
            $obj->commit();
        } catch (Throwable $e) {
            $obj->rollBack();
            error_log("Error al registrar seguimiento: " . $e->getMessage());
            jsonResponse(['ok' => false, 'message' => $e->getMessage()], 500);
        }

        jsonResponse([
            'ok' => true,
            'message' => 'Seguimiento de zoocriadero registrado correctamente.',
            'id_seguimiento' => $idSeguimiento,
        ], 201);
    }

    // Edición: mismo formulario, pero hace UPDATE en vez de INSERT.
    public function postUpdate() {
        $obj  = new SeguimientoZoocriaderoModel();
        $body = requestJsonBody();

        $idSeguimiento = filter_var($body['id_seguimiento'] ?? null, FILTER_VALIDATE_INT);
        if (!$idSeguimiento) {
            jsonResponse(['ok' => false, 'message' => 'id_seguimiento es obligatorio.'], 422);
        }
        if (!$obj->buscarSeguimiento($idSeguimiento)) {
            jsonResponse(['ok' => false, 'message' => 'El seguimiento no existe.'], 404);
        }

        $datos = $this->validar($body, $obj);

        try {
            $obj->beginTransaction();
            $obj->actualizarSeguimiento($idSeguimiento, $datos);   // UPDATE
            $obj->reemplazarActividad($idSeguimiento, $datos['id_actividad']);
            $obj->commit();
        } catch (Throwable $e) {
            $obj->rollBack();
            error_log("Error al actualizar seguimiento: " . $e->getMessage());
            jsonResponse(['ok' => false, 'message' => $e->getMessage()], 500);
        }

        jsonResponse([
            'ok' => true,
            'message' => 'Seguimiento actualizado correctamente.',
            'id_seguimiento' => $idSeguimiento,
        ]);
    }

    // ---------- Validación compartida por postCreate y postUpdate ----------
    private function validar($body, $obj) {
        $idZoo           = filter_var($body['id_zoocriadero'] ?? null, FILTER_VALIDATE_INT);
        $idTanque        = filter_var($body['id_tanque'] ?? null, FILTER_VALIDATE_INT);
        $idActividad     = filter_var($body['id_actividad'] ?? null, FILTER_VALIDATE_INT);
        $numeroNacidos   = filter_var($body['numero_nacidos'] ?? 0, FILTER_VALIDATE_INT);
        $numeroMuertos   = filter_var($body['numero_muertos'] ?? 0, FILTER_VALIDATE_INT);
        $numeroSembrados = filter_var($body['numero_sembrados'] ?? 0, FILTER_VALIDATE_INT);
        $fecha           = trim((string)($body['fecha'] ?? ''));
        $observaciones   = limpiar($body['observaciones'] ?? '');
        $ph              = $body['ph'] ?? null;
        $temperatura     = $body['temperatura'] ?? null;

        if (!$idZoo) {
            jsonResponse(['ok' => false, 'message' => 'Debe seleccionar un zoocriadero.'], 422);
        }
        if (!$idTanque) {
            jsonResponse(['ok' => false, 'message' => 'Debe seleccionar un tanque.'], 422);
        }
        if (!$idActividad) {
            jsonResponse(['ok' => false, 'message' => 'Debe seleccionar una acción.'], 422);
        }
        if ($numeroNacidos === false || $numeroNacidos < 0 ||
           $numeroMuertos === false || $numeroMuertos < 0 ||
           $numeroSembrados === false || $numeroSembrados < 0) {
            jsonResponse(['ok' => false, 'message' => 'Los conteos de peces no pueden ser negativos.'], 422);
        }

        // Validar formato de fecha YYYY-MM-DD
        $fechaObj = DateTime::createFromFormat('Y-m-d', $fecha);
        if (!$fechaObj || $fechaObj->format('Y-m-d') !== $fecha) {
            jsonResponse(['ok' => false, 'message' => 'La fecha no tiene un formato válido (YYYY-MM-DD).'], 422);
        }

        // Restricción: Debe ser estrictamente la fecha actual del servidor
        $hoy = date('Y-m-d');
        if ($fecha !== $hoy) {
            jsonResponse(['ok' => false, 'message' => "La fecha del seguimiento debe ser la fecha actual ($hoy). No se permiten fechas pasadas ni futuras."], 422);
        }

        $errorObs = validarTextoOpcional($observaciones, 'Observaciones', 300);
        if ($errorObs !== null) {
            jsonResponse(['ok' => false, 'message' => $errorObs], 422);
        }

        // ph NUMERIC(4,2) y temperatura NUMERIC(4,2): opcionales, pero si vienen deben ser números
        $ph = ($ph === '' || $ph === null) ? null : (is_numeric($ph) ? (float) $ph : false);
        if ($ph === false || ($ph !== null && ($ph < 0 || $ph > 14))) {
            jsonResponse(['ok' => false, 'message' => 'El pH debe ser un número entre 0 y 14.'], 422);
        }
        $temperatura = ($temperatura === '' || $temperatura === null)
            ? null
            : (is_numeric($temperatura) ? (float) $temperatura : false);
        if ($temperatura === false) {
            jsonResponse(['ok' => false, 'message' => 'La temperatura debe ser un número.'], 422);
        }

        // Reglas de negocio contra la base de datos
        if (!$obj->zoocriaderoActivoExiste($idZoo)) {
            jsonResponse(['ok' => false, 'message' => 'El zoocriadero seleccionado no existe o está inhabilitado.'], 422);
        }
        if (!$obj->tanquePerteneceAZoocriadero($idTanque, $idZoo)) {
            jsonResponse(['ok' => false, 'message' => 'El tanque seleccionado no pertenece al zoocriadero elegido.'], 422);
        }
        if (!$obj->accionValida($idActividad)) {
            jsonResponse(['ok' => false, 'message' => 'La acción seleccionada no es válida.'], 422);
        }

        return [
            'id_zoocriadero'   => $idZoo,
            'id_tanque'        => $idTanque,
            'id_actividad'     => $idActividad,
            'fecha'            => $fecha,
            'ph'               => $ph,
            'temperatura'      => $temperatura,
            'numero_sembrados' => $numeroSembrados,
            'numero_nacidos'   => $numeroNacidos,
            'numero_muertos'   => $numeroMuertos,
            'observaciones'    => $observaciones,
        ];
    }
}
