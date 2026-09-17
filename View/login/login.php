<<<<<<< HEAD
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
=======
>>>>>>> origin/dev
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SIGuppy</title>
    
<<<<<<< HEAD
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
        <!-- Logo institucional a la izquierda -->
        <div class="top-header">
            <img src="../../Web/assets/img/logo-secretaria-salud.png" class="logo-secretaria" alt="">
        </div>

        <!-- Logo SIGuppy centrado y más visible -->
        <div class="text-center mb-3">
            <img src="../../Web/assets/img/SIGUPPY.png" class="logo-siguppy" alt="SIGuppy">
            <h3 class="fw-bold text-dark mt-2 mb-1"></h3>
            <p class="text-muted small"></p>
=======
    <!-- Archivos CSS -->
    <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/plugins.min.css">
    <link rel="stylesheet" href="../../assets/css/kaiadmin.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/login.css">
</head>
<body class="d-flex align-items-center justify-content-center vh-100 bg-light">

    <!-- Bloque de Alertas -->
    <?php if (isset($_GET['error'])): ?>
        <div class="position-absolute top-0 start-50 translate-middle-x mt-3 style-alert" style="z-index: 1050; min-width: 320px;">
            <?php if ($_GET['error'] === 'inactive' || $_GET['error'] === 'inactivo'): ?>
                <div class="alert alert-warning py-2 small text-center mb-0 shadow-sm">
                    Su cuenta se encuentra inactiva. Contacte al Administrador.
                </div>
            <?php elseif ($_GET['error'] === 'blocked'): ?>
                <div class="alert alert-danger py-2 small text-center mb-0 shadow-sm">
                    Ha superado el límite de 5 intentos fallidos. Su cuenta ha sido bloqueada temporalmente. Intente nuevamente en <?php echo htmlspecialchars($_GET['minutos'] ?? '15'); ?> minutos.
                </div>
            <?php elseif ($_GET['error'] === 'invalid'): ?>
                <div class="alert alert-danger py-2 small text-center mb-0 shadow-sm">
                    Credenciales incorrectas. 
                    <?php if (isset($_GET['intentos'])): ?>
                        (Te quedan <?php echo htmlspecialchars($_GET['intentos']); ?> intentos)
                    <?php endif; ?>
                </div>
            <?php elseif ($_GET['error'] === 'empty' || $_GET['error'] === 'vacio'): ?>
                <div class="alert alert-info py-2 small text-center mb-0 shadow-sm">
                    Por favor, complete todos los campos requeridos.
                </div>
            <?php elseif ($_GET['error'] === 'system'): ?>
                <div class="alert alert-danger py-2 small text-center mb-0 shadow-sm">
                    Ocurrió un error en el sistema. Intente de nuevo más tarde.
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="login-card p-4 bg-white rounded shadow-sm" style="max-width: 400px; width: 100%;">
        
        <!-- Header con Logos -->
        <div class="text-center mb-3">
            <img src="../../assets/img/mas profundoo.png" class="logo-siguppy mb-2" alt="SIGuppy" style="max-height: 80px;">
>>>>>>> origin/dev
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
<<<<<<< HEAD
        <form action="controller/login_process.php" method="POST" id="formLogin">
=======
        <form action="../../Controller/login/login_process.php" method="POST" id="formLogin">
>>>>>>> origin/dev
            
            <div class="form-group mb-3 px-0">
                <label for="correo" class="form-label fw-bold small text-secondary">Usuario o correo electrónico</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-user text-muted"></i></span>
<<<<<<< HEAD
                    <input type="text" class="form-control border-start-0 bg-light" id="correo" name="correo" placeholder="ejemplo@siguppy.gov.co">
                </div>
            </div>

            <div class="form-group mb-3 px-0">
                <label for="contrasena" class="form-label fw-bold small text-secondary">Contraseña</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-lock text-muted"></i></span>
                    <input type="password" class="form-control border-start-0 border-end-0 bg-light" id="contrasena" name="contrasena" placeholder="******">
=======
                    <input type="text" class="form-control border-start-0 bg-light" id="correo" name="correo" placeholder="ejemplo@siguppy.gov.co" required>
                </div>
            </div>

            <div class="form-group mb-4 px-0">
                <label for="contrasena" class="form-label fw-bold small text-secondary">Contraseña</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-lock text-muted"></i></span>
                    <input type="password" class="form-control border-start-0 border-end-0 bg-light" id="contrasena" name="contrasena" placeholder="******" required>
>>>>>>> origin/dev
                    <button class="btn btn-light border border-start-0" type="button" onclick="togglePassword()">
                        <i class="fas fa-eye text-muted" id="iconEye"></i>
                    </button>
                </div>
            </div>

<<<<<<< HEAD
            <div class="text-end mb-4">
                <a href="recuperar.php" class="text-decoration-none small text-info">¿Olvidaste tu contraseña?</a>
            </div>

            <button type="submit" class="btn btn-primary btn-info-custom w-100">Iniciar sesión</button>

            <div class="text-center mt-3">
                <p class="small text-muted mb-0">¿No tienes una cuenta? <a href="registro.php" class="text-info fw-bold text-decoration-none">Regístrate aquí</a></p>
            </div>
        </form>
=======
            <button type="submit" class="btn btn-info text-white w-100 py-2 fw-bold" style="background-color: #17a2b8; border: none;">Iniciar sesión</button>

        </form> <!-- Cierre correcto del formulario -->

>>>>>>> origin/dev
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
<<<<<<< HEAD
=======

>>>>>>> origin/dev
</body>
</html>