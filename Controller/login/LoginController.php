<?php

require_once __DIR__ . '/../../Model/MasterModel.php';
require_once __DIR__ . '/../../lib/Mailer.php';
require_once __DIR__ . '/../../lib/validaciones.php';

class LoginController
{
    public function postLogin()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        $correo     = trim($_POST['correo'] ?? '');
        $contrasena = trim($_POST['contrasena'] ?? '');

        if (empty($correo) || empty($contrasena)) {
            header("Location: ../../View/login/login.php?error=empty");
            exit();
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            header("Location: ../../View/login/login.php?error=invalid_email");
            exit();
        }

        try {
            $masterModel = new MasterModel();

            $sql = "SELECT id_usuario, nombre, apellido, correo, contrasena, estado, id_rol,
                           intentos_fallidos, bloqueo_hasta
                    FROM usuario
                    WHERE LOWER(correo) = LOWER($1)";
            $usuario = $masterModel->selectOne($sql, [$correo]);

            if (!$usuario) {
                header("Location: ../../View/login/login.php?error=invalid");
                exit();
            }

            $ahora = new DateTime();

            if (!empty($usuario['bloqueo_hasta'])) {
                $tiempoBloqueo = new DateTime($usuario['bloqueo_hasta']);

                if ($ahora < $tiempoBloqueo) {
                    $diferencia = $ahora->diff($tiempoBloqueo);
                    $minutosRestantes = $diferencia->i + 1;

                    header("Location: ../../View/login/login.php?error=blocked&minutos=" . $minutosRestantes);
                    exit();
                } else {
                    $sqlReset = "UPDATE usuario SET intentos_fallidos = 0, bloqueo_hasta = NULL WHERE id_usuario = $1";
                    $masterModel->update($sqlReset, [$usuario['id_usuario']]);

                    $usuario['intentos_fallidos'] = 0;
                }
            }

            if ((int) $usuario['estado'] === 0) {
                header("Location: ../../View/login/login.php?error=inactive");
                exit();
            }

            $passwordValida = password_verify($contrasena, $usuario['contrasena']) || ($contrasena === $usuario['contrasena']);

            if ($passwordValida) {
                $sqlReset = "UPDATE usuario SET intentos_fallidos = 0, bloqueo_hasta = NULL WHERE id_usuario = $1";
                $masterModel->update($sqlReset, [$usuario['id_usuario']]);

                $_SESSION['id_usuario'] = $usuario['id_usuario'];
                $_SESSION['usuario']    = $usuario['nombre'] . ' ' . $usuario['apellido'];
                $_SESSION['id_rol']     = $usuario['id_rol'];

                header("Location: ../../Web/index.php");
                exit();
            } else {
                $intentos = (int) $usuario['intentos_fallidos'] + 1;

                if ($intentos >= 5) {
                    $bloqueoHasta = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                    $sqlUpdate = "UPDATE usuario SET intentos_fallidos = $1, bloqueo_hasta = $2 WHERE id_usuario = $3";
                    $masterModel->update($sqlUpdate, [$intentos, $bloqueoHasta, $usuario['id_usuario']]);

                    header("Location: ../../View/login/login.php?error=blocked&minutos=15");
                    exit();
                } else {
                    $sqlUpdate = "UPDATE usuario SET intentos_fallidos = $1 WHERE id_usuario = $2";
                    $masterModel->update($sqlUpdate, [$intentos, $usuario['id_usuario']]);

                    $restantes = 5 - $intentos;
                    header("Location: ../../View/login/login.php?error=invalid&intentos=" . $restantes);
                    exit();
                }
            }
        } catch (Throwable $e) {
            error_log("Error en Login: " . $e->getMessage());
            header("Location: ../../View/login/login.php?error=system");
            exit();
        }
    }

    public function getLogout()
    {
        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        session_destroy();

        header("Location: ../../View/login/login.php");
        exit();
    }

    public function postRecuperar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        $correo = trim($_POST['correo'] ?? '');

        if (empty($correo) || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            header("Location: ../../View/login/recuperar.php?status=notfound");
            exit();
        }

        try {
            $masterModel = new MasterModel();

            $sql = "SELECT id_usuario, nombre, apellido, correo FROM usuario WHERE LOWER(correo) = LOWER($1)";
            $usuario = $masterModel->selectOne($sql, [$correo]);

            if (!$usuario) {
                header("Location: ../../View/login/recuperar.php?status=notfound");
                exit();
            }

            $codigo       = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $codigoExpira = date('Y-m-d H:i:s', strtotime('+15 minutes'));

            $sqlUpdate = "UPDATE usuario SET token_recuperacion = $1, token_expira = $2 WHERE id_usuario = $3";
            $masterModel->update($sqlUpdate, [$codigo, $codigoExpira, $usuario['id_usuario']]);

            $nombreCompleto = $usuario['nombre'] . ' ' . $usuario['apellido'];
            $enviado = enviarCodigoRecuperacion($usuario['correo'], $nombreCompleto, $codigo);

            if (!$enviado) {
                header("Location: ../../View/login/recuperar.php?status=system");
                exit();
            }

            header("Location: ../../View/login/recuperar.php?status=enviado");
            exit();
        } catch (Exception $e) {
            error_log("Error en recuperación de contraseña: " . $e->getMessage());
            header("Location: ../../View/login/recuperar.php?status=system");
            exit();
        }
    }

    public function postValidarCodigo()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        $codigo = trim($_POST['codigo'] ?? '');

        if ($codigo === '') {
            header("Location: ../../View/login/validar_codigo.php?error=codigo_invalido");
            exit();
        }

        try {
            $masterModel = new MasterModel();

            $sql = "SELECT id_usuario FROM usuario
                    WHERE token_recuperacion = $1 AND token_expira > NOW()";
            $usuario = $masterModel->selectOne($sql, [$codigo]);

            if (!$usuario) {
                header("Location: ../../View/login/validar_codigo.php?error=codigo_invalido");
                exit();
            }

            $_SESSION['id_recuperar'] = $usuario['id_usuario'];
            header("Location: ../../View/login/cambio_contrasena.php");
            exit();
        } catch (Exception $e) {
            error_log("Error validando código de recuperación: " . $e->getMessage());
            header("Location: ../../View/login/validar_codigo.php?error=codigo_invalido");
            exit();
        }
    }

    public function postCambioContrasena()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        $idUsuario = $_SESSION['id_recuperar'] ?? null;

        if (!$idUsuario) {
            header("Location: ../../View/login/login.php");
            exit();
        }

        $nueva     = trim($_POST['nueva_contrasena'] ?? '');
        $confirmar = trim($_POST['confirmar_contrasena'] ?? '');

        if ($nueva !== $confirmar) {
            header("Location: ../../View/login/cambio_contrasena.php?error=no_coinciden");
            exit();
        }

        $errorClave = validarContrasena($nueva);
        if ($errorClave !== null) {
            header("Location: ../../View/login/cambio_contrasena.php?error=requisitos&mensaje=" . urlencode($errorClave));
            exit();
        }

        try {
            $masterModel = new MasterModel();
            $hash = password_hash($nueva, PASSWORD_BCRYPT);

            $sql = "UPDATE usuario
                    SET contrasena = $1, token_recuperacion = NULL, token_expira = NULL,
                        intentos_fallidos = 0, bloqueo_hasta = NULL
                    WHERE id_usuario = $2";
            $masterModel->update($sql, [$hash, $idUsuario]);

            unset($_SESSION['id_recuperar']);

            header("Location: ../../View/login/login.php?status=changed");
            exit();
        } catch (Throwable $e) {
            error_log("Error cambiando contraseña: " . $e->getMessage());
            header("Location: ../../View/login/cambio_contrasena.php?error=sistema");
            exit();
        }
    }
}
