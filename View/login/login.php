<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SIGuppy</title>
    
    <!-- CSS de Bootstrap y Kaiadmin (Ruta relativa desde la raíz SIGuppy) -->
    <link rel="stylesheet" href="../../Web/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../Web/assets/css/plugins.min.css">
    <link rel="stylesheet" href="../../Web/assets/css/kaiadmin.min.css">
    
    <!-- Iconos Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Estilos personalizados -->
    <link rel="stylesheet" href="../../Web/assets/css/login.css">
</head>
<body>

    <div class="login-card">
        <!-- Único logo institucional: Secretaría de Salud, centrado arriba -->
        <div class="top-header">
            <img src="../../Web/assets/img/logo-secretaria-salud-transparente.png" class="logo-secretaria" alt="Secretaría de Salud Pública - Alcaldía de Santiago de Cali">
        </div>

        <div class="mb-4 text-center">
            <h4 class="login-title fw-bold mb-0">Iniciar Sesión</h4>
        </div>

        <!-- Mensajes de Excepciones según requerimientos -->
        <?php if (isset($_GET['status']) && $_GET['status'] === 'changed'): ?>
            <div class="alert alert-success py-2 small text-center mb-3">
                Contraseña actualizada. Ya puedes iniciar sesión con ella.
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['motivo']) && $_GET['motivo'] === 'inactividad'): ?>
            <div class="alert alert-warning py-2 small text-center mb-3">
                Tu sesión se cerró por 15 minutos de inactividad. Vuelve a iniciar sesión.
            </div>
        <?php endif; ?>
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
            <?php elseif ($_GET['error'] === 'invalid_email'): ?>
                <div class="alert alert-info py-2 small text-center mb-3">
                    El correo electrónico ingresado no tiene un formato válido (ejemplo: usuario@dominio.com).
                </div>
            <?php elseif ($_GET['error'] === 'system'): ?>
                <div class="alert alert-danger py-2 small text-center mb-3">
                    No fue posible procesar su inicio de sesión en este momento. Intente de nuevo en unos minutos; si el problema persiste, contacte al Administrador.
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Formulario -->
        <form action="../../Controller/login/login_process.php" method="POST" id="formLogin">
            
            <div class="form-group mb-3 px-0">
                <label for="correo" class="form-label fw-bold small text-secondary">Correo electrónico <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-envelope text-muted"></i></span>
                    <input type="text" class="form-control border-start-0 bg-light" id="correo" name="correo" placeholder="ejemplo@cali.gov.co" required>
                </div>
            </div>

            <div class="form-group mb-3 px-0">
                <label for="contrasena" class="form-label fw-bold small text-secondary">Contraseña <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-lock text-muted"></i></span>
                    <input type="password" class="form-control border-start-0 border-end-0 bg-light" id="contrasena" name="contrasena" placeholder="●●●●●●●●" required>
                    <button class="btn btn-light border border-start-0" type="button" onclick="togglePassword()">
                        <i class="fas fa-eye text-muted" id="iconEye"></i>
                    </button>
                </div>
            </div>

            <div class="text-end mb-4">
                <a href="recuperar.php" class="text-decoration-none small text-info">¿Olvidaste tu contraseña?</a>
            </div>

            <button type="submit" class="btn btn-primary btn-info-custom w-100">Iniciar sesión</button>
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