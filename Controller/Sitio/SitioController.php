<?php

include_once '../Model/Sitio/SitioModel.php';

class SitioController
{
    public function lista()
    {
        $obj = new SitioModel();
        jsonResponse(['ok' => true, 'data' => $obj->listar()]);
    }

    public function buscar()
    {
        $obj = new SitioModel();
        $id = filter_var($_GET['id_sitio'] ?? null, FILTER_VALIDATE_INT);

        if (!$id) {
            jsonResponse(['ok' => false, 'message' => 'id_sitio es obligatorio.'], 422);
        }

        $sitio = $obj->buscar($id);

        if (!$sitio) {
            jsonResponse(['ok' => false, 'message' => 'El sitio no existe.'], 404);
        }

        jsonResponse(['ok' => true, 'data' => $sitio]);
    }

    public function postEstado()
    {
        $obj = new SitioModel();
        $body = requestJsonBody();

        $id = filter_var($body['id_sitio'] ?? null, FILTER_VALIDATE_INT);
        $estado = filter_var($body['estado'] ?? null, FILTER_VALIDATE_INT);

        if (!$id || ($estado !== 0 && $estado !== 1)) {
            jsonResponse(['ok' => false, 'message' => 'Datos incompletos para cambiar el estado.'], 422);
        }

        if (!$obj->cambiarEstado($id, $estado)) {
            jsonResponse(['ok' => false, 'message' => 'No se pudo cambiar el estado: ' . $obj->ultimoError()], 500);
        }

        jsonResponse([
            'ok' => true,
            'message' => $estado === 1 ? 'Sitio habilitado.' : 'Sitio inhabilitado.'
        ]);
    }
}
