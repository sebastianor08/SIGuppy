<?php
session_start();
if (isset($_SESSION['id_usuario'])) {
    header("Location: view/usuarios.controller.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SIGuppy</title>
    
    <!-- CSS de Kaiadmin Lite -->
    <link rel="stylesheet" href="kaiadmin-lite-1.2.0/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="kaiadmin-lite-1.2.0/assets/css/plugins.min.css">
    <link rel="stylesheet" href="kaiadmin-lite-1.2.0/assets/css/kaiadmin.min.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Tu archivo login.css (Ubicación actual según tu captura) -->
    <link rel="stylesheet" href="kaiadmin-lite-1.2.0/assets/css/login.css">
</head>
<body>
<div class="login-card">
    <!-- Encabezado de Logos centrados y más grandes -->
    <div class="logo-header">
        <img src="kaiadmin-lite-1.2.0/assets/img/logo secretaria de salud.png" alt="Secretaría de Salud" title="Secretaría de Salud">
        <img src="kaiadmin-lite-1.2.0/assets/img/SIGUPPY.png" alt="SIGuppy" title="SIGuppy">
    </div>

    <!-- Título centrado abajo de los logos -->
    <div class="text-center mb-4">
        <h3 class="fw-bold text-dark mb-1"></h3>
        <p class="text-muted small"></p>
    </div>

    <!-- Mensajes de Error PHP -->
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
    <form action="controller/login_process.php" method="POST" id="formLogin" onsubmit="return validarLogin()">
        
        <!-- Campo Correo -->
        <div class="form-group mb-3 px-0">
            <label for="correo" class="form-label fw-bold small text-secondary">Usuario o correo electrónico</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="fas fa-user text-muted"></i></span>
                <input type="text" class="form-control border-start-0 bg-light" id="correo" name="correo" placeholder="ejemplo@siguppy.gov.co">
            </div>
            <small class="text-danger" id="errorCorreo"></small>
        </div>

        <!-- Campo Contraseña -->
        <div class="form-group mb-3 px-0">
            <label for="contrasena" class="form-label fw-bold small text-secondary">Contraseña</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="fas fa-lock text-muted"></i></span>
                <input type="password" class="form-control border-start-0 border-end-0 bg-light" id="contrasena" name="contrasena" placeholder="******">
                <button class="btn btn-light border border-start-0" type="button" onclick="togglePassword()">
                    <i class="fas fa-eye text-muted" id="iconEye"></i>
                </button>
            </div>
            <small class="text-danger" id="errorContrasena"></small>
        </div>

        <div class="text-end mb-4">
            <a href="#" class="text-decoration-none small text-info">¿Olvidaste tu contraseña?</a>
        </div>

        <button type="submit" class="btn btn-info-custom w-100">Iniciar sesión</button>
    </form>
</div>

<script>
function togglePassword() {
    let passInput = document.getElementById('contrasena');
    let icon = document.getElementById('iconEye');
    if (passInput.type === 'password') {
        passInput.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        passInput.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

function validarLogin() {
    let correo = document.getElementById('correo').value.trim();
    let contrasena = document.getElementById('contrasena').value.trim();
    let errorCorreo = document.getElementById('errorCorreo');
    let errorContrasena = document.getElementById('errorContrasena');
    
    let regexCorreo = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    let valido = true;

    errorCorreo.innerText = "";
    errorContrasena.innerText = "";

    if (correo === "") {
        errorCorreo.innerText = "El correo es obligatorio.";
        valido = false;
    } else if (!regexCorreo.test(correo)) {
        errorCorreo.innerText = "Ingresa un correo válido.";
        valido = false;
    }

    if (contrasena === "") {
        errorContrasena.innerText = "La contraseña es obligatoria.";
        valido = false;
    }

    return valido;
}
</script>

</body>
</html>