<?php

include_once '../Model/SeguimientoDeposito/SeguimientoDepositoModel.php';

// Responde solo JSON. Se llama por Web/ajax.php:
//   ?modulo=SeguimientoDeposito&controlador=SeguimientoDeposito&funcion=catalogos
//
// Un seguimiento es una visita a UN depósito (tabla "seguimiento_deposito").
// La dirección y las coordenadas del depósito vienen de su sitio.
class SeguimientoDepositoController
{
    // Depósitos (con ubicación y estado del último seguimiento: alimentan el
    // <select> y el mapa) + acciones de terreno para el formulario.
    public function catalogos()
    {
        $this->idUsuarioSesion();
        $obj = new SeguimientoDepositoModel();

        jsonResponse([
            'ok' => true,
            'data' => [
                'depositos' => $obj->depositos(),
                'actividades' => $obj->actividadesTerreno()
            ]
        ]);
    }

    public function historial()
    {
        $this->idUsuarioSesion();
        $obj = new SeguimientoDepositoModel();
        jsonResponse(['ok' => true, 'data' => $obj->historial()]);
    }

    public function postCreate()
    {
        $idUsuario = $this->idUsuarioSesion();
        $obj = new SeguimientoDepositoModel();
        $body = requestJsonBody();

        $datos = $this->validar($body, $obj, null);
        $datos['id_usuario'] = $idUsuario;

        $id = $obj->crear($datos);

        if ($id === null) {
            jsonResponse([
                'ok' => false,
                'message' => 'No se pudo registrar el seguimiento: ' . $obj->mensajeError()
            ], 500);
        }

        jsonResponse([
            'ok' => true,
            'message' => 'Seguimiento de depósito registrado correctamente.',
            'id_seguimiento_deposito' => (int)$id
        ], 201);
    }

    public function postUpdate()
    {
        $this->idUsuarioSesion();
        $obj = new SeguimientoDepositoModel();
        $body = requestJsonBody();

        $id = filter_var($body['id_seguimiento_deposito'] ?? null, FILTER_VALIDATE_INT);
        if (!$id) {
            jsonResponse(['ok' => false, 'message' => 'id_seguimiento_deposito es obligatorio.'], 422);
        }

        $actual = $obj->buscar($id);
        if (!$actual) {
            jsonResponse(['ok' => false, 'message' => 'El seguimiento no existe.'], 404);
        }

        if ((int)$actual['estado'] !== 1) {
            jsonResponse([
                'ok' => false,
                'message' => 'El seguimiento está inhabilitado. Habilítelo antes de editarlo.'
            ], 422);
        }

        $datos = $this->validar($body, $obj, $actual);

        if (!$obj->actualizar($id, $datos)) {
            jsonResponse([
                'ok' => false,
                'message' => 'No se pudo actualizar el seguimiento: ' . $obj->mensajeError()
            ], 500);
        }

        jsonResponse(['ok' => true, 'message' => 'Seguimiento actualizado correctamente.']);
    }

    public function postEstado()
    {
        $this->idUsuarioSesion();
        $obj = new SeguimientoDepositoModel();
        $body = requestJsonBody();

        $id = filter_var($body['id_seguimiento_deposito'] ?? null, FILTER_VALIDATE_INT);
        $estado = filter_var($body['estado'] ?? null, FILTER_VALIDATE_INT);

        if (!$id || ($estado !== 0 && $estado !== 1)) {
            jsonResponse(['ok' => false, 'message' => 'Datos incompletos para cambiar el estado.'], 422);
        }

        if (!$obj->buscar($id)) {
            jsonResponse(['ok' => false, 'message' => 'El seguimiento no existe.'], 404);
        }

        if (!$obj->cambiarEstado($id, $estado)) {
            jsonResponse([
                'ok' => false,
                'message' => 'No se pudo cambiar el estado: ' . $obj->mensajeError()
            ], 500);
        }

        jsonResponse([
            'ok' => true,
            'message' => $estado === 1
                ? 'Seguimiento habilitado.'
                : 'Seguimiento inhabilitado.'
        ]);
    }

    // El seguimiento guarda quién lo hizo, así que exige sesión iniciada.
    // Devuelve el id del usuario; si no hay sesión responde 401 y termina.
    private function idUsuarioSesion()
    {
        $id = filter_var($_SESSION['id_usuario'] ?? null, FILTER_VALIDATE_INT);

        if (!$id) {
            jsonResponse([
                'ok' => false,
                'message' => 'Su sesión expiró. Inicie sesión nuevamente.'
            ], 401);
        }

        return $id;
    }

    // $actual = fila actual del seguimiento al editar (null al crear).
    // Al crear se valida depósito y fecha; al editar el depósito y la fecha se
    // conservan (son la identidad del seguimiento) y solo cambian los demás datos.
    private function validar($body, $obj, $actual)
    {
        $creando = ($actual === null);

        $idActividad = filter_var($body['id_actividad'] ?? null, FILTER_VALIDATE_INT);
        $larvas = filter_var($body['presencia_larvas'] ?? null, FILTER_VALIDATE_INT);
        $peces = $body['numero_peces_sembrados'] ?? 0;
        $peces = ($peces === '' || $peces === null) ? 0 : filter_var($peces, FILTER_VALIDATE_INT);
        $observaciones = limpiar($body['observaciones'] ?? '');

        if ($creando) {
            $idDeposito = filter_var($body['id_deposito'] ?? null, FILTER_VALIDATE_INT);
            if (!$idDeposito) {
                jsonResponse(['ok' => false, 'message' => 'Debe seleccionar un depósito.'], 422);
            }
            if (!$obj->depositoDisponible($idDeposito)) {
                jsonResponse([
                    'ok' => false,
                    'message' => 'El depósito seleccionado no existe o está inhabilitado.'
                ], 422);
            }

            $fecha = trim((string)($body['fecha'] ?? ''));
            $fechaObj = DateTime::createFromFormat('Y-m-d', $fecha);
            if (!$fechaObj || $fechaObj->format('Y-m-d') !== $fecha) {
                jsonResponse(['ok' => false, 'message' => 'La fecha no tiene un formato válido (YYYY-MM-DD).'], 422);
            }

            // Misma regla que el seguimiento de zoocriadero: la fecha es la de hoy.
            $hoy = date('Y-m-d');
            if ($fecha !== $hoy) {
                jsonResponse([
                    'ok' => false,
                    'message' => "La fecha del seguimiento debe ser la fecha actual ($hoy). No se permiten fechas pasadas ni futuras."
                ], 422);
            }
        } else {
            $idDeposito = (int)$actual['id_deposito'];
            $fecha = $actual['fecha'];
        }

        if (!$idActividad) {
            jsonResponse(['ok' => false, 'message' => 'Debe seleccionar una acción.'], 422);
        }
        if (!$obj->actividadDisponible($idActividad, $creando ? null : $actual['id_actividad'])) {
            jsonResponse([
                'ok' => false,
                'message' => 'La acción seleccionada no es válida o está inhabilitada.'
            ], 422);
        }

        if ($larvas !== 0 && $larvas !== 1) {
            jsonResponse([
                'ok' => false,
                'message' => 'Debe indicar si se encontraron larvas en el depósito.'
            ], 422);
        }

        // numero_peces_sembrados es integer en la BD (máx. 2147483647).
        if ($peces === false || $peces < 0 || $peces > 1000000) {
            jsonResponse([
                'ok' => false,
                'message' => 'Los peces sembrados deben ser un número entero entre 0 y 1.000.000.'
            ], 422);
        }

        // Máximo coincide con la BD: observaciones varchar(300).
        $errorObs = validarTextoOpcional($observaciones, 'Observaciones', 300);
        if ($errorObs !== null) {
            jsonResponse(['ok' => false, 'message' => $errorObs], 422);
        }

        return [
            'id_deposito' => $idDeposito,
            'id_actividad' => $idActividad,
            'fecha' => $fecha,
            'presencia_larvas' => $larvas,
            'numero_peces_sembrados' => $peces,
            'observaciones' => $observaciones
        ];
    }
}
