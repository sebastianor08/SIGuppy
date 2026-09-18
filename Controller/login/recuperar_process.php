<?php
ob_start();
session_start();

// Carga el MasterModel (conexión nativa pgsql, sin PDO) y el envío de correo
require_once '../../Model/MasterModel.php';
require_once '../../lib/Mailer.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

        // Código de 6 dígitos, igual que el de verificación de login,
        // válido por 15 minutos (reutiliza token_recuperacion/token_expira)
        $codigo = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
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
