<?php

include_once __DIR__ . '/../../Model/Sitio/SitioModel.php';

// ============================================================
// Controlador del módulo Sitio. Se llama SIEMPRE a través de
// Web/mvc.php (páginas completas, no JSON):
//
//   Web/mvc.php?modulo=Sitio&controlador=Sitio&funcion=list
//   Web/mvc.php?modulo=Sitio&controlador=Sitio&funcion=getCreate
//   Web/mvc.php?modulo=Sitio&controlador=Sitio&funcion=getUpdate&id=5
//   Web/mvc.php?modulo=Sitio&controlador=Sitio&funcion=getDelete&id=5
//
// Cada método hace: 1) pide datos al modelo, 2) hace include de
// la vista correspondiente. Las acciones que escriben (postCreate,
// postUpdate, postDelete) redirigen siempre al listado al terminar.
// ============================================================
class SitioController
{
    public function list()
    {
        $obj = new SitioModel();
        $sitios = $obj->listar();

        include_once __DIR__ . '/../../View/Sitio/list.php';
    }

    public function getCreate()
    {
        $obj = new SitioModel();
        $tiposDeposito = $obj->tiposDeposito();
        $errores = [];

        include_once __DIR__ . '/../../View/Sitio/create.php';
    }

    public function postCreate()
    {
        $obj = new SitioModel();

        $datos = $this->datosDelFormulario();
        $errores = $this->validar($datos);

        if (!empty($errores)) {
            $tiposDeposito = $obj->tiposDeposito();
            include_once __DIR__ . '/../../View/Sitio/create.php';
            return;
        }

        $id = $obj->crear($datos);

        if ($id) {
            redirect('mvc.php?modulo=Sitio&controlador=Sitio&funcion=list&ok=creado');
        } else {
            $errores[] = 'No se pudo registrar el sitio: ' . $obj->ultimoError();
            $tiposDeposito = $obj->tiposDeposito();
            include_once __DIR__ . '/../../View/Sitio/create.php';
        }
    }

    public function getUpdate()
    {
        $obj = new SitioModel();
        $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);

        if (!$id) { redirect('mvc.php?modulo=Sitio&controlador=Sitio&funcion=list'); }

        $sitio = $obj->buscarPorId($id);
        if (!$sitio) { redirect('mvc.php?modulo=Sitio&controlador=Sitio&funcion=list'); }

        $tiposDeposito = $obj->tiposDeposito();
        $errores = [];

        include_once __DIR__ . '/../../View/Sitio/update.php';
    }

    public function postUpdate()
    {
        $obj = new SitioModel();
        $id = filter_var($_POST['id_sitio'] ?? null, FILTER_VALIDATE_INT);

        if (!$id) { redirect('mvc.php?modulo=Sitio&controlador=Sitio&funcion=list'); }

        $datos = $this->datosDelFormulario();
        $errores = $this->validar($datos);

        if (!empty($errores)) {
            $sitio = $obj->buscarPorId($id);
            $tiposDeposito = $obj->tiposDeposito();
            include_once __DIR__ . '/../../View/Sitio/update.php';
            return;
        }

        $ok = $obj->actualizar($id, $datos);

        if ($ok) {
            redirect('mvc.php?modulo=Sitio&controlador=Sitio&funcion=list&ok=actualizado');
        } else {
            $errores[] = 'No se pudo actualizar el sitio: ' . $obj->ultimoError();
            $sitio = $obj->buscarPorId($id);
            $tiposDeposito = $obj->tiposDeposito();
            include_once __DIR__ . '/../../View/Sitio/update.php';
        }
    }

    public function getDelete()
    {
        $obj = new SitioModel();
        $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);

        if (!$id) { redirect('mvc.php?modulo=Sitio&controlador=Sitio&funcion=list'); }

        $sitio = $obj->buscarPorId($id);
        if (!$sitio) { redirect('mvc.php?modulo=Sitio&controlador=Sitio&funcion=list'); }

        include_once __DIR__ . '/../../View/Sitio/delete.php';
    }

    public function postDelete()
    {
        $obj = new SitioModel();
        $id = filter_var($_POST['id_sitio'] ?? null, FILTER_VALIDATE_INT);

        if ($id) { $obj->eliminar($id); }

        redirect('mvc.php?modulo=Sitio&controlador=Sitio&funcion=list&ok=eliminado');
    }

    // ---------- Ayudas privadas ----------

    private function datosDelFormulario()
    {
        return [
            'id_tipo_deposito' => filter_var($_POST['id_tipo_deposito'] ?? null, FILTER_VALIDATE_INT),
            'direccion'        => trim($_POST['direccion'] ?? ''),
            'comuna'           => trim($_POST['comuna'] ?? ''),
            'barrio'           => trim($_POST['barrio'] ?? ''),
            'latitud'          => trim($_POST['latitud'] ?? ''),
            'longitud'         => trim($_POST['longitud'] ?? ''),
        ];
    }

    private function validar($datos)
    {
        $errores = [];
        if (!$datos['id_tipo_deposito']) { $errores[] = 'Debe seleccionar el tipo de depósito.'; }
        if ($datos['direccion'] === '')  { $errores[] = 'La dirección es obligatoria.'; }
        if ($datos['comuna'] === '')     { $errores[] = 'La comuna es obligatoria.'; }
        if ($datos['barrio'] === '')     { $errores[] = 'El barrio es obligatorio.'; }
        return $errores;
    }
}

?>
