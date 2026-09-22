<?php
session_start();

// Solo accesible justo después de un login válido cuya cuenta todavía
// tiene la contraseña temporal (el número de documento) pendiente de
// cambio. Sin esa condición, se manda a login: esta pantalla no sirve
// como puerta trasera para cambiar la contraseña de cualquiera.
if (empty($_SESSION['id_usuario']) || empty($_SESSION['debe_cambiar_contrasena'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualiza tu contraseña - SIGuppy</title>

    <link rel="stylesheet" href="../../Web/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../Web/assets/css/kaiadmin.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../Web/assets/css/login.css">
</head>
<body>

    <div class="login-card">
        <div class="top-header">
            <img src="../../Web/assets/img/logo-secretaria-salud-transparente.png" class="logo-secretaria" alt="Secretaría de Salud">
        </div>

        <div class="mb-4">
            <h4 class="fw-bold text-dark mb-1">Actualiza tu contraseña</h4>
            <p class="text-muted small">
                Por seguridad, debes cambiar la contraseña temporal (tu número de documento)
                antes de continuar. Esto solo se pide una vez.
            </p>
        </div>

        <?php if (isset($_GET['error']) && $_GET['error'] == 'no_coinciden'): ?>
            <div class="alert alert-danger py-2 small text-center mb-3">
                Las contraseñas no coinciden. Inténtalo de nuevo.
            </div>
        <?php elseif (isset($_GET['error']) && $_GET['error'] == 'igual'): ?>
            <div class="alert alert-danger py-2 small text-center mb-3">
                La nueva contraseña no puede ser igual a tu número de documento.
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

        <form action="../../Controller/login/cambio_obligatorio_process.php" method="POST">

            <div class="form-group mb-3 px-0">
                <label for="nueva_contrasena" class="form-label fw-bold small text-secondary">Nueva contraseña <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-lock text-muted"></i></span>
                    <input type="password" class="form-control border-start-0 bg-light" id="nueva_contrasena" name="nueva_contrasena" required minlength="8">
                </div>
                <div class="form-text">
                    Mínimo 8 caracteres, con al menos una minúscula, una mayúscula y un carácter especial.
                </div>
            </div>

            <div class="form-group mb-4 px-0">
                <label for="confirmar_contrasena" class="form-label fw-bold small text-secondary">Confirma la nueva contraseña <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-lock text-muted"></i></span>
                    <input type="password" class="form-control border-start-0 bg-light" id="confirmar_contrasena" name="confirmar_contrasena" required minlength="8">
                </div>
            </div>

            <button type="submit" class="btn btn-info-custom w-100 mb-3">Guardar y continuar</button>
            <a href="../../Controller/login/logout.php" class="btn btn-volver w-100 text-center text-decoration-none d-block">Cancelar y cerrar sesión</a>
        </form>
    </div>

</body>
</html>
