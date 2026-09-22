<?php

include_once '../Model/Tanque/TanqueModel.php';
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

    public function postCreate()
    {
        sigExigirPermiso('Tanque Zoocriadero', 'crear');
        $obj = new TanqueModel();
        $body = requestJsonBody();

        // $idExcluir = null: al crear, cualquier tanque con el mismo número
        // en ese zoocriadero cuenta como choque (a diferencia de editar,
        // donde el propio tanque no debe chocar consigo mismo).
        $datos = $this->validarTanque($body, $obj, null);

        $id = $obj->crear($datos);
        if ($id === null) {
            jsonResponse(['ok' => false, 'message' => 'No se pudo registrar el tanque: ' . $obj->ultimoError()], 500);
        }

        jsonResponse([
            'ok' => true,
            'message' => 'Tanque registrado correctamente.',
            'id_tanque' => (int) $id,
        ], 201);
    }

    public function postUpdate()
    {
        sigExigirPermiso('Tanque Zoocriadero', 'editar');
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
        sigExigirPermiso('Tanque Zoocriadero', 'inhabilitar');
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
        $nombre = trim((string) ($body['nombre_tanque'] ?? ''));

        if (!$idZoo || !$obj->zoocriaderoExiste($idZoo)) {
            jsonResponse(['ok' => false, 'message' => 'Debe seleccionar un zoocriadero válido.'], 422);
        }
        if (!$idTipo || !$obj->tipoTanqueExiste($idTipo)) {
            jsonResponse(['ok' => false, 'message' => 'Debe seleccionar un tipo de tanque válido.'], 422);
        }
        if ($nombre === '' || mb_strlen($nombre) < 2) {
            jsonResponse(['ok' => false, 'message' => 'El nombre del tanque debe tener al menos 2 caracteres.'], 422);
        }
        if (mb_strlen($nombre) > 60) {
            jsonResponse(['ok' => false, 'message' => 'El nombre del tanque no puede superar 60 caracteres.'], 422);
        }
        if ($obj->existeNombreTanque($idZoo, $nombre, $idExcluir)) {
            jsonResponse(['ok' => false, 'message' => "Ese zoocriadero ya tiene un tanque llamado \"$nombre\"."], 422);
        }

        return [
            'id_zoocriadero' => $idZoo,
            'id_tipo_tanque' => $idTipo,
            'nombre_tanque'  => $nombre,
        ];
    }
}