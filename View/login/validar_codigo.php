<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validación de código - SIGuppy</title>
    
    <link rel="stylesheet" href="../../Web/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../Web/assets/css/kaiadmin.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../Web/assets/css/login.css">
</head>
<body>

    <div class="login-card">
        <!-- Header con Logos Grandes -->
        <div class="top-header">
            <img src="../../Web/assets/img/logo-secretaria-salud.png" class="logo-secretaria" alt="Secretaría de Salud">
        </div>


        <!-- Títulos exactos de tu Figma -->
        <div class="mb-4">
            <h4 class="fw-bold text-dark mb-1">Ingresa el codigo enviado al correo</h4>
            <p class="text-muted small">No te preocupes sabemos que eres tu , pero queres confirmar tu entidad</p>
        </div>

        <!-- Alerta de Error -->
        <?php if (isset($_GET['error']) && $_GET['error'] == 'codigo_invalido'): ?>
            <div class="alert alert-danger py-2 small text-center mb-3">
                El código ingresado es incorrecto o expiro.
            </div>
        <?php endif; ?>

        <!-- Formulario -->
        <form action="../../Controller/login/validar_codigo_process.php" method="POST">
            <div class="form-group mb-4 px-0">
                <label for="codigo" class="form-label fw-bold small text-secondary">Ingrese el codigo</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-user text-muted"></i></span>
                    <input type="text" class="form-control border-start-0 bg-light" id="codigo" name="codigo" required>
                </div>
            </div>

            <button type="submit" class="btn btn-info-custom w-100 mb-3">Enviar</button>
            <a href="recuperar.php" class="btn btn-volver w-100 text-center text-decoration-none d-block">Volver</a>
        </form>
    </div>

</body>
</html>