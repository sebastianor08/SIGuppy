<?php
    include_once '../lib/helpers.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión - SIGuppys</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f0f2f5;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }

        .login-card {
            width: 100%;
            max-width: 400px;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .login-logo {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 20px;
        }

        .login-logo img {
            height: 56px;
            margin-bottom: 10px;
        }

        .login-logo small {
            color: #8a8d93;
            font-size: 12px;
        }
    </style>
</head>

<body>

    <div class="card login-card">
        <div class="card-body">

            <div class="login-logo">
                <img src="assets/img/logo_sistema_SIGuppy.svg" alt="SIGuppys">
                <strong>SIGuppys</strong>
                <small>Control Biológico contra el Dengue</small>
            </div>

            <h3 class="text-center mb-4">Iniciar sesión</h3>

            <form action="<?php echo getUrl("Acceso", "Acceso", "login", false, "ajax");?>" method="post">

                <div class="mb-3">
                    <label class="form-label">Correo</label>
                    <input
                        type="email"
                        class="form-control"
                        placeholder="Ingresa tu correo"
                        name="correo"
                        id="correo">
                </div>

                <div class="mb-3">
                    <label class="form-label">Contraseña</label>
                    <input
                        type="password"
                        class="form-control"
                        placeholder="Ingresa tu contraseña"
                        name="clave"
                        id="clave">
                </div>

                <?php
                    if (isset($_SESSION['error'])) {
                        echo "<div class='alert alert-danger'>".$_SESSION['error']."</div>";
                        unset($_SESSION['error']);
                    }
                ?>

                <input type="submit" class="btn btn-primary w-100 mt-2" value="Ingresar">

            </form>

        </div>
    </div>

    <!-- Bootstrap JS (opcional) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
