<?php
session_start();
require_once '../../model/conexion.php'; // Tu conexión PDO a PostgreSQL

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo'] ?? '');
    $contrasena = trim($_POST['contrasena'] ?? '');

    // Excepción 1: Campos vacíos
    if (empty($correo) || empty($contrasena)) {
        header("Location: ../../login.php?error=empty");
        exit();
    }

    try {
        // Consultar el usuario en PostgreSQL
        $sql = "SELECT id_usuario, nombre, correo, contrasena, estado, intentos_fallidos, bloqueo_hasta 
                FROM usuario 
                WHERE correo = :correo";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':correo' => $correo]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        // Si el usuario no existe
        if (!$usuario) {
            header("Location: ../../login.php?error=invalid");
            exit();
        }

        $ahora = new DateTime();

        // Excepción 2: Bloqueo por 5 intentos fallidos (15 minutos)
        if (!empty($usuario['bloqueo_hasta'])) {
            $tiempoBloqueo = new DateTime($usuario['bloqueo_hasta']);
            
            if ($ahora < $tiempoBloqueo) {
                // Calcular cuántos minutos faltan
                $diferencia = $ahora->diff($tiempoBloqueo);
                $minutosRestantes = $diferencia->i + 1;
                
                header("Location: ../../login.php?error=blocked&minutos=" . $minutosRestantes);
                exit();
            } else {
                // El tiempo de 15 minutos ya pasó: Reiniciamos el contador de intentos
                $resetSql = "UPDATE usuario SET intentos_fallidos = 0, bloqueo_hasta = NULL WHERE id_usuario = :id";
                $resetStmt = $conexion->prepare($resetSql);
                $resetStmt->execute([':id' => $usuario['id_usuario']]);
                
                $usuario['intentos_fallidos'] = 0;
            }
        }

        // Excepción 3: Cuenta Inactiva o Suspendida
        if ($usuario['estado'] === 'Inactivo' || $usuario['estado'] === 'Suspendido') {
            header("Location: ../../login.php?error=inactive");
            exit();
        }

        // Validación de Contraseña
        $passwordValida = password_verify($contrasena, $usuario['contrasena']) || ($contrasena === $usuario['contrasena']);

        if ($passwordValida) {
            // LOGIN EXITOSO: Resetear intentos fallidos y crear sesión
            $resetSql = "UPDATE usuario SET intentos_fallidos = 0, bloqueo_hasta = NULL WHERE id_usuario = :id";
            $resetStmt = $conexion->prepare($resetSql);
            $resetStmt->execute([':id' => $usuario['id_usuario']]);

            $_SESSION['usuario'] = $usuario['nombre'] ?? $usuario['correo'];
            $_SESSION['id_usuario'] = $usuario['id_usuario'];

            header("Location: ../../index.php");
            exit();
        } else {
            // CONTRASEÑA INCORRECTA: Incrementar intentos fallidos
            $intentos = $usuario['intentos_fallidos'] + 1;

            if ($intentos >= 5) {
                // Bloquear por 15 minutos exactos
                $bloqueoHasta = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                $updateSql = "UPDATE usuario SET intentos_fallidos = :intentos, bloqueo_hasta = :bloqueo WHERE id_usuario = :id";
                $updateStmt = $conexion->prepare($updateSql);
                $updateStmt->execute([
                    ':intentos' => $intentos,
                    ':bloqueo' => $bloqueoHasta,
                    ':id' => $usuario['id_usuario']
                ]);

                header("Location: ../../login.php?error=blocked&minutos=15");
                exit();
            } else {
                // Guardar Intento fallido
                $updateSql = "UPDATE usuario SET intentos_fallidos = :intentos WHERE id_usuario = :id";
                $updateStmt = $conexion->prepare($updateSql);
                $updateStmt->execute([
                    ':intentos' => $intentos,
                    ':id' => $usuario['id_usuario']
                ]);

                $restantes = 5 - $intentos;
                header("Location: ../../login.php?error=invalid&intentos=" . $restantes);
                exit();
            }
        }

    } catch (PDOException $e) {
        // Excepción del sistema / Servidor de BD
        error_log("Error de BD: " . $e->getMessage());
        header("Location: ../../login.php?error=system");
        exit();
    }
}