<?php
ob_start();
session_start();

// Carga el MasterModel (conexión nativa pgsql, sin PDO)
require_once '../../Model/MasterModel.php';
require_once '../../lib/Mailer.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo'] ?? '');
    $contrasena = trim($_POST['contrasena'] ?? '');

    // Validación de campos vacíos
    if (empty($correo) || empty($contrasena)) {
        header("Location: ../../View/login/login.php?error=empty");
        exit();
    }

    try {
        $masterModel = new MasterModel();

        // Consultar el usuario en PostgreSQL
        $sql = "SELECT id_usuario, nombre, apellido, correo, contrasena, estado, id_rol,
                       intentos_fallidos, bloqueo_hasta
                FROM usuario
                WHERE LOWER(correo) = LOWER($1)";
        $usuario = $masterModel->selectOne($sql, [$correo]);

        // Si no existe el usuario
        if (!$usuario) {
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

                header("Location: ../../View/login/login.php?error=blocked&minutos=" . $minutosRestantes);
                exit();
            } else {
                $sqlReset = "UPDATE usuario SET intentos_fallidos = 0, bloqueo_hasta = NULL WHERE id_usuario = $1";
                $masterModel->update($sqlReset, [$usuario['id_usuario']]);

                $usuario['intentos_fallidos'] = 0;
            }
        }

        if ((int)$usuario['estado'] === 0) {
            header("Location: ../../View/login/login.php?error=inactive");
            exit();
        }

        $passwordValida = password_verify($contrasena, $usuario['contrasena']) || ($contrasena === $usuario['contrasena']);

        if ($passwordValida) {
            $sqlReset = "UPDATE usuario SET intentos_fallidos = 0, bloqueo_hasta = NULL WHERE id_usuario = $1";
            $masterModel->update($sqlReset, [$usuario['id_usuario']]);

            // ---- Código de verificación por correo (2FA) ----
            $codigo = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $codigoExpira = date('Y-m-d H:i:s', strtotime('+10 minutes'));

            $sqlCodigo = "UPDATE usuario SET codigo_verificacion = $1, codigo_verificacion_expira = $2 WHERE id_usuario = $3";
            $masterModel->update($sqlCodigo, [$codigo, $codigoExpira, $usuario['id_usuario']]);

            $nombreCompleto = $usuario['nombre'] . ' ' . $usuario['apellido'];
            $enviado = enviarCodigoVerificacion($usuario['correo'], $nombreCompleto, $codigo);

            if (!$enviado) {
                // No se pudo enviar el correo: no dejamos al usuario a medias
                // con un código que nunca le va a llegar.
                header("Location: ../../View/login/login.php?error=system");
                exit();
            }

            // Ojo: todavía NO se inicia sesión. Solo queda "pendiente de
            // verificar" hasta que ingrese el código correcto.
            $_SESSION['pendiente_verificacion'] = $usuario['id_usuario'];
            $_SESSION['pendiente_intentos'] = 0;

            header("Location: ../../View/login/verificar_codigo.php");
            exit();
        } else {
            $intentos = (int)$usuario['intentos_fallidos'] + 1;

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

    } catch (Exception $e) {
        error_log("Error en Login: " . $e->getMessage());
        header("Location: ../../View/login/login.php?error=system");
        exit();
    }
}