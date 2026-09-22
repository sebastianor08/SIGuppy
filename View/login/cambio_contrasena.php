<?php
session_start();
require_once __DIR__ . '/../../Model/MasterModel.php';

// El enlace del correo trae ?token=...; se valida aquí (existencia y
// vigencia) en vez de depender de una sesión previa de "código validado".
$token = trim($_GET['token'] ?? $_POST['token'] ?? '');
$tokenValido = false;

if ($token !== '') {
    try {
        $masterModel = new MasterModel();
        $usuarioToken = $masterModel->selectOne(
            "SELECT id_usuario FROM usuario WHERE token_recuperacion = $1 AND token_expira > NOW()",
            [$token]
        );
        $tokenValido = $usuarioToken !== null;
    } catch (Throwable $e) {
        error_log("Error validando token de recuperación: " . $e->getMessage());
        $tokenValido = false;
    }
}

if (!$tokenValido) {
    header("Location: recuperar.php?status=token_invalido");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambio de contraseña - SIGuppy</title>
    
    <link rel="stylesheet" href="../../Web/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../Web/assets/css/kaiadmin.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../Web/assets/css/login.css">
</head>
<body>

    <div class="login-card">
        <!-- Header Logos Grandes -->
        <div class="top-header">
            <img src="../../Web/assets/img/logo-secretaria-salud-transparente.png" class="logo-secretaria" alt="Secretaría de Salud">
        </div>



        <!-- Títulos exactos de tu Figma -->
        <div class="mb-4">
            <h4 class="fw-bold text-dark mb-1">Cambio de contraseña</h4>
            <p class="text-muted small">Gracias por confirmar que eres tu, en este caso nos gustaria saber</p>
        </div>

        <!-- Alertas -->
        <?php if (isset($_GET['error']) && $_GET['error'] == 'no_coinciden'): ?>
            <div class="alert alert-danger py-2 small text-center mb-3">
                Las contraseñas no coinciden. Inténtalo de nuevo.
            </div>
        <?php elseif (isset($_GET['error']) && $_GET['error'] == 'requisitos'): ?>
            <div class="alert alert-danger py-2 small text-center mb-3">
                <?php echo htmlspecialchars($_GET['mensaje'] ?? 'La contraseña no cumple los requisitos de seguridad.'); ?>
            </div>
        <?php elseif (isset($_GET['error']) && $_GET['error'] == 'sistema'): ?>
            <div class="alert alert-danger py-2 small text-center mb-3">
                No fue posible actualizar tu contraseña en este momento. Intenta de nuevo en unos minutos; si el problema persiste, contacta al Administrador.
            </div>
        <?php endif; ?>

        <!-- Formulario -->
        <form action="../../Controller/login/cambio_contrasena_process.php" method="POST">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">
            
            <div class="form-group mb-3 px-0">
                <label for="nueva_contrasena" class="form-label fw-bold small text-secondary">Ingrese la nueva contraseña <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-lock text-muted"></i></span>
                    <input type="password" class="form-control border-start-0 bg-light" id="nueva_contrasena" name="nueva_contrasena" required minlength="8">
                </div>
                <div class="form-text">
                    Mínimo 8 caracteres, con al menos una minúscula, una mayúscula y un carácter especial.
                </div>
            </div>

            <div class="form-group mb-4 px-0">
                <label for="confirmar_contrasena" class="form-label fw-bold small text-secondary">Confirme nuevamente la contraseña <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-lock text-muted"></i></span>
                    <input type="password" class="form-control border-start-0 bg-light" id="confirmar_contrasena" name="confirmar_contrasena" required minlength="8">
                </div>
            </div>

            <button type="submit" class="btn btn-info-custom w-100 mb-3">Enviar</button>
            <a href="login.php" class="btn btn-volver w-100 text-center text-decoration-none d-block">Volver</a>
        </form>
    </div>

</body>
</html>