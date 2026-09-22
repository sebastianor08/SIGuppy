<?php
ob_start();
session_start();


require_once '../../Model/MasterModel.php';
function registrarIntentoLogin($masterModel, $idUsuario, $correo, $exitoso, $detalle)
{
    $masterModel->select(
        "SELECT fn_registrar_login($1, $2, $3, $4)",
        [$idUsuario, $correo, $exitoso ? 'true' : 'false', $detalle]
    );
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo'] ?? '');
    $contrasena = trim($_POST['contrasena'] ?? '');

    // Validación de campos vacíos
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

        // Consultar el usuario en PostgreSQL
        $sql = "SELECT id_usuario, nombre, apellido, correo, contrasena, estado, id_rol,
                       intentos_fallidos, bloqueo_hasta, debe_cambiar_contrasena
                FROM usuario
                WHERE LOWER(correo) = LOWER($1)";
        $usuario = $masterModel->selectOne($sql, [$correo]);

        // Si no existe el usuario
        if (!$usuario) {
            registrarIntentoLogin($masterModel, null, $correo, false, 'Correo no registrado');
            header("Location: ../../View/login/login.php?error=invalid");
            exit();
        }

        $ahora = new DateTime();

        // Bloqueo por 5 intentos fallidos (15 minutos)
        if (!empty($usuario['bloqueo_hasta'])) {
            $tiempoBloqueo = new DateTime($usuario['bloqueo_hasta']);

            if ($ahora < $tiempoBloqueo) {
                $diferencia = $ahora->diff($tiempoBloqueo);
                $minutosRestantes = $diferencia->i + 1;

                registrarIntentoLogin($masterModel, $usuario['id_usuario'], $correo, false, 'Cuenta bloqueada temporalmente');
                header("Location: ../../View/login/login.php?error=blocked&minutos=" . $minutosRestantes);
                exit();
            } else {
                $sqlReset = "UPDATE usuario SET intentos_fallidos = 0, bloqueo_hasta = NULL WHERE id_usuario = $1";
                $masterModel->update($sqlReset, [$usuario['id_usuario']]);

                $usuario['intentos_fallidos'] = 0;
            }
        }

        if ((int)$usuario['estado'] === 0) {
            registrarIntentoLogin($masterModel, $usuario['id_usuario'], $correo, false, 'Cuenta inactiva');
            header("Location: ../../View/login/login.php?error=inactive");
            exit();
        }

        $passwordValida = password_verify($contrasena, $usuario['contrasena']);

        if ($passwordValida) {
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['usuario']    = $usuario['nombre'] . ' ' . $usuario['apellido'];
            $_SESSION['id_rol']     = $usuario['id_rol'];
            $_SESSION['debe_cambiar_contrasena'] = ((int) ($usuario['debe_cambiar_contrasena'] ?? 0) === 1)
                || $usuario['debe_cambiar_contrasena'] === 't'
                || $usuario['debe_cambiar_contrasena'] === true;
            $masterModel->actualizarUsuarioAuditoria();

            $sqlReset = "UPDATE usuario SET intentos_fallidos = 0, bloqueo_hasta = NULL WHERE id_usuario = $1";
            $masterModel->update($sqlReset, [$usuario['id_usuario']]);

            registrarIntentoLogin($masterModel, $usuario['id_usuario'], $correo, true, 'Inicio de sesión exitoso');

            // Contraseña pendiente por cambiar (primer ingreso con la
            // contraseña temporal = número de documento): no se le deja
            // entrar al resto del sistema hasta que la actualice.
            if ($_SESSION['debe_cambiar_contrasena']) {
                header("Location: ../../View/login/cambio_obligatorio.php");
                exit();
            }

            header("Location: ../../Web/index.php");
            exit();
        } else {
            $intentos = (int)$usuario['intentos_fallidos'] + 1;

            if ($intentos >= 5) {
                $bloqueoHasta = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                $sqlUpdate = "UPDATE usuario SET intentos_fallidos = $1, bloqueo_hasta = $2 WHERE id_usuario = $3";
                $masterModel->update($sqlUpdate, [$intentos, $bloqueoHasta, $usuario['id_usuario']]);

                registrarIntentoLogin($masterModel, $usuario['id_usuario'], $correo, false, 'Contraseña incorrecta: cuenta bloqueada por 5 intentos');
                header("Location: ../../View/login/login.php?error=blocked&minutos=15");
                exit();
            } else {
                $sqlUpdate = "UPDATE usuario SET intentos_fallidos = $1 WHERE id_usuario = $2";
                $masterModel->update($sqlUpdate, [$intentos, $usuario['id_usuario']]);

                registrarIntentoLogin($masterModel, $usuario['id_usuario'], $correo, false, 'Contraseña incorrecta');
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