<!-- Mensajes de Excepciones según requerimientos -->
<?php if (isset($_GET['error'])): ?>
    <?php if ($_GET['error'] === 'inactive'): ?>
        <div class="alert alert-warning py-2 small text-center mb-3">
            Su cuenta se encuentra inactiva. Contacte al Administrador.
        </div>
    <?php elseif ($_GET['error'] === 'blocked'): ?>
        <div class="alert alert-danger py-2 small text-center mb-3">
            Ha superado el límite de 5 intentos fallidos. Su cuenta ha sido bloqueada temporalmente. Intente nuevamente en <?php echo htmlspecialchars($_GET['minutos'] ?? '15'); ?> minutos.
        </div>
    <?php elseif ($_GET['error'] === 'invalid'): ?>
        <div class="alert alert-danger py-2 small text-center mb-3">
            Credenciales incorrectas. 
            <?php if (isset($_GET['intentos'])): ?>
                (Te quedan <?php echo htmlspecialchars($_GET['intentos']); ?> intentos)
            <?php endif; ?>
        </div>
    <?php elseif ($_GET['error'] === 'empty'): ?>
        <div class="alert alert-info py-2 small text-center mb-3">
            Por favor, complete todos los campos requeridos.
        </div>
    <?php elseif ($_GET['error'] === 'system'): ?>
        <div class="alert alert-danger py-2 small text-center mb-3">
            Ocurrió un error en el sistema. Intente de nuevo más tarde.
        </div>
    <?php endif; ?>
<?php endif; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SIGuppy</title>
    
    <!-- CSS de Bootstrap y Kaiadmin (Ruta relativa desde la raíz SIGuppy) -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/plugins.min.css">
    <link rel="stylesheet" href="assets/css/kaiadmin.min.css">
    
    <!-- Iconos Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Estilos personalizados -->
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>

    <div class="login-card">
        <!-- Logo institucional a la izquierda -->
        <div class="top-header">
            <img src="assets/img/mas profundoo.png" class="logo-secretaria" alt="Secretaría de Salud">
        </div>

        <!-- Logo SIGuppy centrado y más visible -->
        <div class="text-center mb-3">
            <img src="assets/img/SIGUPPY.png" class="logo-siguppy" alt="SIGuppy">
            <h3 class="fw-bold text-dark mt-2 mb-1"></h3>
            <p class="text-muted small"></p>
        </div>

        <!-- Alertas PHP -->
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger py-2 small text-center mb-3">
                <?php 
                    if ($_GET['error'] == 'vacio') echo "Por favor, complete todos los campos.";
                    elseif ($_GET['error'] == 'invalid') echo "Correo o contraseña incorrectos.";
                    elseif ($_GET['error'] == 'inactivo') echo "Tu usuario se encuentra inactivo.";
                ?>
            </div>
        <?php endif; ?>

        <!-- Formulario -->
        <form action="controller/login_process.php" method="POST" id="formLogin">
            
            <div class="form-group mb-3 px-0">
                <label for="correo" class="form-label fw-bold small text-secondary">Usuario o correo electrónico</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-user text-muted"></i></span>
                    <input type="text" class="form-control border-start-0 bg-light" id="correo" name="correo" placeholder="ejemplo@siguppy.gov.co">
                </div>
            </div>

            <div class="form-group mb-3 px-0">
                <label for="contrasena" class="form-label fw-bold small text-secondary">Contraseña</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-lock text-muted"></i></span>
                    <input type="password" class="form-control border-start-0 border-end-0 bg-light" id="contrasena" name="contrasena" placeholder="******">
                    <button class="btn btn-light border border-start-0" type="button" onclick="togglePassword()">
                        <i class="fas fa-eye text-muted" id="iconEye"></i>
                    </button>
                </div>
            </div>

            <div class="text-end mb-4">
                <a href="#" class="text-decoration-none small text-info">¿Olvidaste tu contraseña?</a>
            </div>

            <button type="submit" class="btn btn-primary btn-info-custom w-100">Iniciar sesión</button>

            <div class="text-center mt-3">
                <p class="small text-muted mb-0">¿No tienes una cuenta? <a href="registro.php" class="text-info fw-bold text-decoration-none">Regístrate aquí</a></p>
            </div>
        </form>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('contrasena');
            const icon = document.getElementById('iconEye');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>