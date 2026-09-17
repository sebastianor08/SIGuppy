<?php
ob_start();
session_start();

// Carga el MasterModel (conexión nativa pgsql, sin PDO)
require_once '../../Model/MasterModel.php';

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
                // Calcular cuántos minutos faltan
                $diferencia = $ahora->diff($tiempoBloqueo);
                $minutosRestantes = $diferencia->i + 1;

                header("Location: ../../View/login/login.php?error=blocked&minutos=" . $minutosRestantes);
                exit();
            } else {
                // El tiempo de 15 minutos ya pasó: reiniciamos el contador de intentos
                $sqlReset = "UPDATE usuario SET intentos_fallidos = 0, bloqueo_hasta = NULL WHERE id_usuario = $1";
                $masterModel->update($sqlReset, [$usuario['id_usuario']]);

                $usuario['intentos_fallidos'] = 0;
            }
        }

        if ((int)$usuario['estado'] === 0) {
            header("Location: ../../View/login/login.php?error=inactive");
            exit();
        }

        // Validación de contraseña
        $passwordValida = password_verify($contrasena, $usuario['contrasena']) || ($contrasena === $usuario['contrasena']);

        if ($passwordValida) {
            // LOGIN EXITOSO: resetear intentos fallidos y crear sesión
            $sqlReset = "UPDATE usuario SET intentos_fallidos = 0, bloqueo_hasta = NULL WHERE id_usuario = $1";
            $masterModel->update($sqlReset, [$usuario['id_usuario']]);

            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['usuario']    = $usuario['nombre'] . ' ' . $usuario['apellido'];
            $_SESSION['id_rol']     = $usuario['id_rol'];

            header("Location: ../../Web/index.php");
            exit();
        } else {
            // CONTRASEÑA INCORRECTA: incrementar intentos fallidos
            $intentos = (int)$usuario['intentos_fallidos'] + 1;

            if ($intentos >= 5) {
                // Bloquear por 15 minutos exactos
                $bloqueoHasta = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                $sqlUpdate = "UPDATE usuario SET intentos_fallidos = $1, bloqueo_hasta = $2 WHERE id_usuario = $3";
                $masterModel->update($sqlUpdate, [$intentos, $bloqueoHasta, $usuario['id_usuario']]);

                header("Location: ../../View/login/login.php?error=blocked&minutos=15");
                exit();
            } else {
                // Guardar intento fallido
                $sqlUpdate = "UPDATE usuario SET intentos_fallidos = $1 WHERE id_usuario = $2";
                $masterModel->update($sqlUpdate, [$intentos, $usuario['id_usuario']]);

                $restantes = 5 - $intentos;
                header("Location: ../../View/login/login.php?error=invalid&intentos=" . $restantes);
                exit();
            }
        }

    } catch (Exception $e) {
        // Excepción del sistema / servidor de BD
        error_log("Error en Login: " . $e->getMessage());
        header("Location: ../../View/login/login.php?error=system");
        exit();
    }
}