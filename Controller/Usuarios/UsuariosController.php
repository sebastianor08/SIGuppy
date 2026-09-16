<?php

include_once __DIR__ . '/../../Model/Usuarios/UsuariosModel.php';
include_once __DIR__ . '/../../lib/Mailer.php';

// ============================================================
// Controlador del módulo Usuarios (Gestión de Usuarios).
// Se llama siempre a través de Web/mvc.php:
//   Web/mvc.php?modulo=Usuarios&controlador=Usuarios&funcion=list
//
// Aquí es donde el administrador registra personas nuevas: al
// crear un usuario se genera una contraseña temporal aleatoria,
// se guarda cifrada (password_hash, igual que ya hacía el login)
// y se le envía esa contraseña por correo con PHPMailer.
//
// Si el correo no se pudo enviar (por ejemplo, porque todavía no
// se instaló PHPMailer con "composer require phpmailer/phpmailer"),
// el usuario igual queda creado: no se pierde la funcionalidad
// principal por culpa del correo. En ese caso, la contraseña se
// muestra una sola vez en pantalla para que el administrador se
// la pueda dar manualmente.
// ============================================================
class UsuariosController
{
    public function list()
    {
        $obj = new UsuariosModel();
        $usuarios = $obj->listar();
        include_once __DIR__ . '/../../View/Usuarios/list.php';
    }

    public function getCreate()
    {
        $obj = new UsuariosModel();
        $roles = $obj->roles();
        $tiposDocumento = $obj->tiposDocumento();
        $errores = [];
        include_once __DIR__ . '/../../View/Usuarios/create.php';
    }

    public function postCreate()
    {
        $obj = new UsuariosModel();
        $datos = $this->datosDelFormulario();
        $errores = $this->validar($datos, $obj);

        if (!empty($errores)) {
            $roles = $obj->roles();
            $tiposDocumento = $obj->tiposDocumento();
            include_once __DIR__ . '/../../View/Usuarios/create.php';
            return;
        }

        $contrasenaTemporal = $this->generarContrasenaTemporal();
        $hash = password_hash($contrasenaTemporal, PASSWORD_BCRYPT);

        $id = $obj->crear($datos, $hash);

        if (!$id) {
            $errores[] = 'No se pudo registrar el usuario: ' . $obj->ultimoError();
            $roles = $obj->roles();
            $tiposDocumento = $obj->tiposDocumento();
            include_once __DIR__ . '/../../View/Usuarios/create.php';
            return;
        }

        // Intentar avisarle al usuario nuevo su contraseña por correo.
        $mailer = new Mailer();
        $correoEnviado = $mailer->enviar(
            $datos['correo'],
            $datos['nombre'] . ' ' . $datos['apellido'],
            'Tu cuenta en SIGuppys',
            $this->plantillaCorreoBienvenida($datos, $contrasenaTemporal)
        );

        // Pase lo que pase con el correo, el usuario ya quedó creado.
        // Si el correo falló, mostramos la contraseña UNA sola vez en
        // pantalla (vía sesión) para que el administrador se la entregue.
        if (!$correoEnviado) {
            $_SESSION['usuario_creado_sin_correo'] = [
                'correo' => $datos['correo'],
                'contrasena' => $contrasenaTemporal,
                'motivo' => $mailer->ultimoError(),
            ];
            redirect('mvc.php?modulo=Usuarios&controlador=Usuarios&funcion=list&ok=creado_sin_correo');
        }

        redirect('mvc.php?modulo=Usuarios&controlador=Usuarios&funcion=list&ok=creado');
    }

    public function getUpdate()
    {
        $obj = new UsuariosModel();
        $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
        if (!$id) { redirect('mvc.php?modulo=Usuarios&controlador=Usuarios&funcion=list'); }

        $usuario = $obj->buscarPorId($id);
        if (!$usuario) { redirect('mvc.php?modulo=Usuarios&controlador=Usuarios&funcion=list'); }

        $roles = $obj->roles();
        $tiposDocumento = $obj->tiposDocumento();
        $errores = [];
        include_once __DIR__ . '/../../View/Usuarios/update.php';
    }

    public function postUpdate()
    {
        $obj = new UsuariosModel();
        $id = filter_var($_POST['id_usuario'] ?? null, FILTER_VALIDATE_INT);
        if (!$id) { redirect('mvc.php?modulo=Usuarios&controlador=Usuarios&funcion=list'); }

        $datos = $this->datosDelFormulario();
        $errores = $this->validar($datos, $obj, $id);

        if (!empty($errores)) {
            $usuario = $obj->buscarPorId($id);
            $roles = $obj->roles();
            $tiposDocumento = $obj->tiposDocumento();
            include_once __DIR__ . '/../../View/Usuarios/update.php';
            return;
        }

        $ok = $obj->actualizar($id, $datos);
        if ($ok) {
            redirect('mvc.php?modulo=Usuarios&controlador=Usuarios&funcion=list&ok=actualizado');
        } else {
            $errores[] = 'No se pudo actualizar el usuario: ' . $obj->ultimoError();
            $usuario = $obj->buscarPorId($id);
            $roles = $obj->roles();
            $tiposDocumento = $obj->tiposDocumento();
            include_once __DIR__ . '/../../View/Usuarios/update.php';
        }
    }

    // Activar / Desactivar, en un solo clic (con confirmación en el
    // propio enlace vía JavaScript). No borra al usuario: solo cambia
    // su estado, igual que en el resto del sistema.
    public function cambiarEstado()
    {
        $obj = new UsuariosModel();
        $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
        $estado = filter_var($_GET['estado'] ?? null, FILTER_VALIDATE_INT);

        if ($id !== false && $id !== null && ($estado === 0 || $estado === 1)) {
            $obj->cambiarEstado($id, $estado);
        }

        redirect('mvc.php?modulo=Usuarios&controlador=Usuarios&funcion=list&ok=estado');
    }

    // ---------- Ayudas privadas ----------

    private function datosDelFormulario()
    {
        return [
            'id_tipodocumento' => filter_var($_POST['id_tipodocumento'] ?? null, FILTER_VALIDATE_INT),
            'id_rol'           => filter_var($_POST['id_rol'] ?? null, FILTER_VALIDATE_INT),
            'nombre'           => trim($_POST['nombre'] ?? ''),
            'apellido'         => trim($_POST['apellido'] ?? ''),
            'correo'           => trim($_POST['correo'] ?? ''),
        ];
    }

    private function validar($d, $obj, $idExcluir = null)
    {
        $errores = [];
        if ($d['nombre'] === '')   { $errores[] = 'El nombre es obligatorio.'; }
        if ($d['apellido'] === '') { $errores[] = 'El apellido es obligatorio.'; }
        if (!$d['id_tipodocumento']) { $errores[] = 'Debe seleccionar el tipo de documento.'; }
        if (!$d['id_rol'])         { $errores[] = 'Debe seleccionar el rol.'; }

        if ($d['correo'] === '' || !filter_var($d['correo'], FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'El correo no es válido.';
        } elseif ($obj->existeCorreo($d['correo'], $idExcluir)) {
            $errores[] = 'Ya existe un usuario registrado con ese correo.';
        }

        return $errores;
    }

    // Contraseña temporal legible (evita caracteres confusos como 0/O, 1/l/I).
    private function generarContrasenaTemporal($longitud = 10)
    {
        $alfabeto = 'ABCDEFGHJKMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
        $contrasena = '';
        for ($i = 0; $i < $longitud; $i++) {
            $contrasena .= $alfabeto[random_int(0, strlen($alfabeto) - 1)];
        }
        return $contrasena;
    }

    private function plantillaCorreoBienvenida($datos, $contrasenaTemporal)
    {
        $nombre = htmlspecialchars($datos['nombre'], ENT_QUOTES, 'UTF-8');
        $correo = htmlspecialchars($datos['correo'], ENT_QUOTES, 'UTF-8');
        $clave  = htmlspecialchars($contrasenaTemporal, ENT_QUOTES, 'UTF-8');

        return "
            <div style='font-family:Arial,sans-serif;font-size:14px;color:#333;'>
                <h2 style='color:#1e88e5;'>Bienvenido(a) a SIGuppys</h2>
                <p>Hola {$nombre},</p>
                <p>Un administrador creó una cuenta para ti en el sistema SIGuppys
                   (Control Biológico contra el Dengue).</p>
                <p><strong>Usuario (correo):</strong> {$correo}<br>
                   <strong>Contraseña temporal:</strong> {$clave}</p>
                <p>Por seguridad, cambia esta contraseña la primera vez que ingreses.</p>
            </div>
        ";
    }
}

?>
