<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - SIGuppy</title>
    
    <!-- CSS Bootstrap y Kaiadmin -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/plugins.min.css">
    <link rel="stylesheet" href="assets/css/kaiadmin.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- CSS Personalizado -->
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

        <div class="mb-3 text-center">
            <h4 class="fw-bold text-dark mb-1">Crear cuenta</h4>
            <p class="text-muted small">Ingresa tus datos para registrarte en el sistema</p>
        </div>

        <!-- Formulario de Registro -->
        <form action="controller/registro_process.php" method="POST">
            
            <div class="form-group mb-2 px-0">
                <label for="nombre" class="form-label fw-bold small text-secondary">Nombre completo</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-user text-muted"></i></span>
                    <input type="text" class="form-control border-start-0 bg-light" id="nombre" name="nombre" placeholder="Ej: Andrea Rivera" required>
                </div>
            </div>

            <div class="form-group mb-2 px-0">
                <label for="correo" class="form-label fw-bold small text-secondary">Correo electrónico</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-envelope text-muted"></i></span>
                    <input type="email" class="form-control border-start-0 bg-light" id="correo" name="correo" placeholder="ejemplo@correo.com" required>
                </div>
            </div>

            <div class="form-group mb-3 px-0">
                <label for="contrasena" class="form-label fw-bold small text-secondary">Contraseña</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-lock text-muted"></i></span>
                    <input type="password" class="form-control border-start-0 bg-light" id="contrasena" name="contrasena" required minlength="6">
                </div>
            </div>

            <button type="submit" class="btn btn-info-custom w-100 mb-3">Registrarse</button>
            <a href="login.php" class="btn btn-volver w-100 text-center text-decoration-none d-block">Volver al inicio de sesión</a>
        </form>
    </div>

    <script src="assets/js/core/bootstrap.min.js"></script>
</body>
</html>