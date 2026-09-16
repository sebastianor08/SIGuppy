<?php
session_start();
// Protección: Si no validó el código previamente, redirigir
if (!isset($_SESSION['id_recuperar'])) {
    header("Location: recuperar.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambio de contraseña - SIGuppy</title>
    
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/kaiadmin.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>

    <div class="login-card">
        <!-- Header Logos Grandes -->
        <div class="top-header">
            <img src="assets/img/logo_secretaria.png" class="logo-secretaria" alt="Secretaría de Salud">
        </div>

        <div class="text-center mb-3">
            <img src="assets/img/logo_siguppy.png" class="logo-siguppy" alt="SIGuppy">
            <div class="slogan-text mt-1">Control biológico contra el dengue</div>
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
        <?php endif; ?>

        <!-- Formulario -->
        <form action="Controller/login/cambio_contrasena_process.php" method="POST">
            
            <div class="form-group mb-3 px-0">
                <label for="nueva_contrasena" class="form-label fw-bold small text-secondary">Ingrese la nueva contraseña</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-user text-muted"></i></span>
                    <input type="password" class="form-control border-start-0 bg-light" id="nueva_contrasena" name="nueva_contrasena" required minlength="6">
                </div>
            </div>

            <div class="form-group mb-4 px-0">
                <label for="confirmar_contrasena" class="form-label fw-bold small text-secondary">Confirme nuevamente la contraseña</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-user text-muted"></i></span>
                    <input type="password" class="form-control border-start-0 bg-light" id="confirmar_contrasena" name="confirmar_contrasena" required minlength="6">
                </div>
            </div>

            <button type="submit" class="btn btn-info-custom w-100 mb-3">Enviar</button>
            <a href="login.php" class="btn btn-volver w-100 text-center text-decoration-none d-block">Volver</a>
        </form>
    </div>

</body>
</html>