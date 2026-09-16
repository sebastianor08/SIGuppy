<?php

include_once __DIR__ . '/../../Model/TerritorioPriorizado/TerritorioPriorizadoModel.php';

// ============================================================
// Controlador del módulo Territorio Priorizado. Se llama siempre
// a través de Web/mvc.php (páginas completas, sin JSON):
//   Web/mvc.php?modulo=TerritorioPriorizado&controlador=TerritorioPriorizado&funcion=list
// Mismo patrón que SitioController.
// ============================================================
class TerritorioPriorizadoController
{
    public function list()
    {
        $obj = new TerritorioPriorizadoModel();
        $territorios = $obj->listar();
        include_once __DIR__ . '/../../View/TerritorioPriorizado/list.php';
    }

    public function getCreate()
    {
        $obj = new TerritorioPriorizadoModel();
        $funcionarios = $obj->funcionarios();
        $errores = [];
        include_once __DIR__ . '/../../View/TerritorioPriorizado/create.php';
    }

    public function postCreate()
    {
        $obj = new TerritorioPriorizadoModel();
        $datos = $this->datosDelFormulario();
        $errores = $this->validar($datos);

        if (!empty($errores)) {
            $funcionarios = $obj->funcionarios();
            include_once __DIR__ . '/../../View/TerritorioPriorizado/create.php';
            return;
        }

        $id = $obj->crear($datos);
        if ($id) {
            redirect('mvc.php?modulo=TerritorioPriorizado&controlador=TerritorioPriorizado&funcion=list&ok=creado');
        } else {
            $errores[] = 'No se pudo registrar: ' . $obj->ultimoError();
            $funcionarios = $obj->funcionarios();
            include_once __DIR__ . '/../../View/TerritorioPriorizado/create.php';
        }
    }

    public function getUpdate()
    {
        $obj = new TerritorioPriorizadoModel();
        $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
        if (!$id) { redirect('mvc.php?modulo=TerritorioPriorizado&controlador=TerritorioPriorizado&funcion=list'); }

        $territorio = $obj->buscarPorId($id);
        if (!$territorio) { redirect('mvc.php?modulo=TerritorioPriorizado&controlador=TerritorioPriorizado&funcion=list'); }

        $funcionarios = $obj->funcionarios();
        $errores = [];
        include_once __DIR__ . '/../../View/TerritorioPriorizado/update.php';
    }

    public function postUpdate()
    {
        $obj = new TerritorioPriorizadoModel();
        $id = filter_var($_POST['id_territorio'] ?? null, FILTER_VALIDATE_INT);
        if (!$id) { redirect('mvc.php?modulo=TerritorioPriorizado&controlador=TerritorioPriorizado&funcion=list'); }

        $datos = $this->datosDelFormulario();
        $errores = $this->validar($datos);

        if (!empty($errores)) {
            $territorio = $obj->buscarPorId($id);
            $funcionarios = $obj->funcionarios();
            include_once __DIR__ . '/../../View/TerritorioPriorizado/update.php';
            return;
        }

        $ok = $obj->actualizar($id, $datos);
        if ($ok) {
            redirect('mvc.php?modulo=TerritorioPriorizado&controlador=TerritorioPriorizado&funcion=list&ok=actualizado');
        } else {
            $errores[] = 'No se pudo actualizar: ' . $obj->ultimoError();
            $territorio = $obj->buscarPorId($id);
            $funcionarios = $obj->funcionarios();
            include_once __DIR__ . '/../../View/TerritorioPriorizado/update.php';
        }
    }

    public function getDelete()
    {
        $obj = new TerritorioPriorizadoModel();
        $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
        if (!$id) { redirect('mvc.php?modulo=TerritorioPriorizado&controlador=TerritorioPriorizado&funcion=list'); }

        $territorio = $obj->buscarPorId($id);
        if (!$territorio) { redirect('mvc.php?modulo=TerritorioPriorizado&controlador=TerritorioPriorizado&funcion=list'); }

        include_once __DIR__ . '/../../View/TerritorioPriorizado/delete.php';
    }

    public function postDelete()
    {
        $obj = new TerritorioPriorizadoModel();
        $id = filter_var($_POST['id_territorio'] ?? null, FILTER_VALIDATE_INT);
        if ($id) { $obj->eliminar($id); }
        redirect('mvc.php?modulo=TerritorioPriorizado&controlador=TerritorioPriorizado&funcion=list&ok=eliminado');
    }

    // ---------- Ayudas privadas ----------

    private function datosDelFormulario()
    {
        return [
            'comuna'                  => trim($_POST['comuna'] ?? ''),
            'barrio'                  => trim($_POST['barrio'] ?? ''),
            'sitio'                   => trim($_POST['sitio'] ?? ''),
            'direccion_sitio'         => trim($_POST['direccion_sitio'] ?? ''),
            'nombre_lider'            => trim($_POST['nombre_lider'] ?? ''),
            'direccion_lider'         => trim($_POST['direccion_lider'] ?? ''),
            'telefono_lider'          => trim($_POST['telefono_lider'] ?? ''),
            'correo_lider'            => trim($_POST['correo_lider'] ?? ''),
            'clase_liderazgo'         => trim($_POST['clase_liderazgo'] ?? ''),
            'id_funcionario_ecosalud' => filter_var($_POST['id_funcionario_ecosalud'] ?? null, FILTER_VALIDATE_INT),
            'latitud'                 => trim($_POST['latitud'] ?? ''),
            'longitud'                => trim($_POST['longitud'] ?? ''),
        ];
    }

    private function validar($d)
    {
        $errores = [];
        if ($d['comuna'] === '')                { $errores[] = 'La comuna es obligatoria.'; }
        if ($d['barrio'] === '')                 { $errores[] = 'El barrio es obligatorio.'; }
        if ($d['nombre_lider'] === '')           { $errores[] = 'El nombre del líder comunitario es obligatorio.'; }
        if (!$d['id_funcionario_ecosalud'])      { $errores[] = 'Debe seleccionar el funcionario de ecosalud responsable.'; }
        if ($d['correo_lider'] !== '' && !filter_var($d['correo_lider'], FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'El correo del líder no es válido.';
        }
        return $errores;
    }
}

?>
