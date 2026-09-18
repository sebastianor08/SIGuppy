<?php

include_once '../Model/Tanque/TanqueModel.php';

// ============================================================
// Controlador del módulo Tanques. Responde solo JSON, así que
// se llama siempre por Web/ajax.php:
//   Web/ajax.php?modulo=Tanque&controlador=Tanque&funcion=lista
//
// La creación de tanques sigue viviendo en ZoocriaderoController
// (postTanque); aquí solo se listan, editan y cambian de estado.
// ============================================================
class TanqueController
{

    // ---------- Lecturas ----------

    public function lista()
    {
        $obj = new TanqueModel();
        jsonResponse(['ok' => true, 'data' => $obj->listar()]);
    }

    public function zoocriaderos()
    {
        $obj = new TanqueModel();
        jsonResponse(['ok' => true, 'data' => $obj->zoocriaderosActivos()]);
    }

    public function tiposTanque()
    {
        $obj = new TanqueModel();
        jsonResponse(['ok' => true, 'data' => $obj->tiposTanque()]);
    }

    // ---------- Escrituras ----------

    public function postUpdate()
    {
        $obj = new TanqueModel();
        $body = requestJsonBody();

        $idTanque = filter_var($body['id_tanque'] ?? null, FILTER_VALIDATE_INT);
        if (!$idTanque) {
            jsonResponse(['ok' => false, 'message' => 'id_tanque es obligatorio.'], 422);
        }
        if (!$obj->buscar($idTanque)) {
            jsonResponse(['ok' => false, 'message' => 'El tanque no existe.'], 404);
        }

        $datos = $this->validarTanque($body, $obj, $idTanque);

        if ($obj->actualizar($idTanque, $datos) === false) {
            jsonResponse(['ok' => false, 'message' => 'No se pudo actualizar: ' . $obj->ultimoError()], 500);
        }

        jsonResponse(['ok' => true, 'message' => 'Tanque actualizado correctamente.']);
    }

    public function postEstado()
    {
        $obj = new TanqueModel();
        $body = requestJsonBody();

        $idTanque = filter_var($body['id_tanque'] ?? null, FILTER_VALIDATE_INT);
        $estado   = filter_var($body['estado'] ?? null, FILTER_VALIDATE_INT);

        if (!$idTanque || ($estado !== 0 && $estado !== 1)) {
            jsonResponse(['ok' => false, 'message' => 'Datos incompletos para cambiar el estado.'], 422);
        }
        if (!$obj->buscar($idTanque)) {
            jsonResponse(['ok' => false, 'message' => 'El tanque no existe.'], 404);
        }

        if ($obj->cambiarEstado($idTanque, $estado) === false) {
            jsonResponse(['ok' => false, 'message' => 'No se pudo cambiar el estado.'], 500);
        }

        jsonResponse([
            'ok' => true,
            'message' => $estado === 1
                ? 'Tanque habilitado.'
                : 'Tanque inhabilitado. Ya no se podrán registrar seguimientos para este tanque.',
        ]);
    }

    // ---------- Validación compartida ----------
    private function validarTanque($body, $obj, $idExcluir)
    {
        $idZoo  = filter_var($body['id_zoocriadero'] ?? null, FILTER_VALIDATE_INT);
        $idTipo = filter_var($body['id_tipo_tanque'] ?? null, FILTER_VALIDATE_INT);
        $numero = filter_var($body['numero_tanque'] ?? null, FILTER_VALIDATE_INT);

        if (!$idZoo || !$obj->zoocriaderoExiste($idZoo)) {
            jsonResponse(['ok' => false, 'message' => 'Debe seleccionar un zoocriadero válido.'], 422);
        }
        if (!$idTipo || !$obj->tipoTanqueExiste($idTipo)) {
            jsonResponse(['ok' => false, 'message' => 'Debe seleccionar un tipo de tanque válido.'], 422);
        }
        if (!$numero || $numero <= 0) {
            jsonResponse(['ok' => false, 'message' => 'El número de tanque debe ser un entero mayor que cero.'], 422);
        }
        if ($obj->existeNumeroTanque($idZoo, $numero, $idExcluir)) {
            jsonResponse(['ok' => false, 'message' => "Ese zoocriadero ya tiene un tanque número $numero."], 422);
        }

        return [
            'id_zoocriadero' => $idZoo,
            'id_tipo_tanque' => $idTipo,
            'numero_tanque'  => $numero,
        ];
    }
}
