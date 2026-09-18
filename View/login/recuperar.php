<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar contraseña - SIGuppy</title>
    
    <!-- CSS Bootstrap y Kaiadmin -->
    <link rel="stylesheet" href="../../Web/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../Web/assets/css/plugins.min.css">
    <link rel="stylesheet" href="../../Web/assets/css/kaiadmin.min.css">
    
    <!-- Iconos Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- CSS Personalizado -->
    <link rel="stylesheet" href="../../Web/assets/css/login.css">
</head>
<body>

    <div class="login-card">
        <div class="top-header">
            <img src="../../Web/assets/img/logo-secretaria-salud.png" class="logo-secretaria" alt="">
        </div>

        <div class="text-center mb-3">
            <img src="../../Web/assets/img/SIGUPPY.png" class="logo-siguppy" alt="SIGuppy">
            <div class="slogan-text mt-1"></div>
        </div>

        <div class="mb-4">
            <h4 class="fw-bold text-dark mb-1">¿Has olvidado tu contraseña?</h4>
            <p class="text-muted small">No te preocupes te ayudaremos en los que sea necesario</p>
        </div>

        <?php if (isset($_GET['status']) && $_GET['status'] == 'notfound'): ?>
            <div class="alert alert-danger py-2 small text-center mb-3">
                El correo electrónico no se encuentra registrado.
            </div>
        <?php elseif (isset($_GET['status']) && $_GET['status'] == 'system'): ?>
            <div class="alert alert-danger py-2 small text-center mb-3">
                No se pudo enviar el correo. Intenta de nuevo más tarde.
            </div>
        <?php endif; ?>

        <form action="../../Controller/login/recuperar_process.php" method="POST">
            <div class="form-group mb-4 px-0">
                <label for="correo" class="form-label fw-bold small text-secondary">Correo electrónico</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-user text-muted"></i></span>
                    <input type="email" class="form-control border-start-0 bg-light" id="correo" name="correo" placeholder="" required>
                </div>
            </div>

            <button type="submit" class="btn btn-info-custom w-100 mb-3">Enviar</button>
            <a href="login.php" class="btn btn-volver w-100 text-center text-decoration-none d-block">Volver</a>
        </form>
    </div>

    <!-- Modal de confirmación -->
    <div class="modal fade" id="modalConfirmacion" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-center p-4 border-0 rounded-4 shadow">
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="modal-body pt-0">
                    <div class="party-icon"></div>
                    <h5 class="fw-bold text-dark mb-4">Hemos enviado el código a tu correo</h5>
                  <button type="button" class="btn btn-info-custom w-75" onclick="window.location.href='validar_codigo.php'">Continuar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="../../Web/assets/js/core/bootstrap.min.js"></script>

    <?php if (isset($_GET['status']) && $_GET['status'] == 'enviado'): ?>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var myModal = new bootstrap.Modal(document.getElementById('modalConfirmacion'));
            myModal.show();
        });
    </script>
    <?php endif; ?>

</body>
</html>