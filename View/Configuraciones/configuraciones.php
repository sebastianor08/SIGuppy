<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Configuraciones · SIGuppys</title>
    <meta
      content="width=device-width, initial-scale=1.0, shrink-to-fit=no"
      name="viewport"
    />
    <link rel="icon" href="../../assets/img/siguppys/favicon-32.png" type="image/png" />
    <link rel="apple-touch-icon" href="../../assets/img/siguppys/favicon-180.png" />

    <!-- Fonts and icons -->
    <script src="../../assets/js/plugin/webfont/webfont.min.js"></script>
    <script>
      WebFont.load({
        google: { families: ["Public Sans:300,400,500,600,700"] },
        custom: {
          families: [
            "Font Awesome 5 Solid",
            "Font Awesome 5 Regular",
            "Font Awesome 5 Brands",
            "simple-line-icons",
          ],
          urls: ["../../assets/css/fonts.min.css"],
        },
        active: function () {
          sessionStorage.fonts = true;
        },
      });
    </script>

    <!-- CSS Files (mismos del template, sin modificar) -->
    <link rel="stylesheet" href="../../assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="../../assets/css/plugins.min.css" />
    <link rel="stylesheet" href="../../assets/css/kaiadmin.min.css" />

    <!-- Estilos propios de SIGuppys: solo AGREGAN reglas encima del kaiadmin.css original -->
    <link rel="stylesheet" href="../../assets/css/siguppys.css" />
  </head>
  <body data-page="configuraciones">
    <?php $rutaBase = '../../'; ?>
    <div class="wrapper">
      <?php include '../partials/sidebar.php'; ?>
      <div class="main-panel">
        <?php include '../partials/topbar.php'; ?>

        <div class="container">
          <div class="page-inner">
            <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
              <div>
                <h3 class="fw-bold mb-3">Configuraciones</h3>
                <h6 class="op-7 mb-2">Configuraciones</h6>
              </div>
            </div>
            <div class="card">
              <div class="card-body">
                <h6 class="fw-bold mb-1">Apariencia y accesibilidad</h6>
                <p class="text-muted small mb-4">Estas preferencias se guardan en este navegador y aplican a todo el sitio.</p>

                <div class="settings-option">
                  <div>
                    <strong><i class="fas fa-moon me-2"></i>Modo oscuro</strong>
                    <p>Cambia los colores de fondo por tonos oscuros para reducir el brillo en ambientes con poca luz.</p>
                  </div>
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="switchDarkMode" />
                  </div>
                </div>

                <div class="settings-option">
                  <div>
                    <strong><i class="fas fa-eye me-2"></i>Modo daltonismo</strong>
                    <p>Ajusta la paleta de los estados (Activo / Inhabilitado) según el tipo de daltonismo.</p>
                  </div>
                  <div class="sig-daltonismo-select">
                    <select class="form-select form-select-sm" id="selectDaltonismo">
                      <option value="ninguno">Ninguno</option>
                      <option value="protanopia">Protanopia (dificultad con el rojo)</option>
                      <option value="deuteranopia">Deuteranopia (dificultad con el verde)</option>
                      <option value="tritanopia">Tritanopia (dificultad con el azul/amarillo)</option>
                      <option value="acromatopsia">Acromatopsia (visión sin color)</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Core JS Files -->
    <script src="../../assets/js/core/jquery-3.7.1.min.js"></script>
    <script src="../../assets/js/core/popper.min.js"></script>
    <script src="../../assets/js/core/bootstrap.min.js"></script>
    <script src="../../assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>
    <script src="../../assets/js/kaiadmin.min.js"></script>

    <!-- SIGuppys -->
    <script src="../../assets/js/siguppys-nav.js"></script>
    <script src="../../assets/js/siguppys-settings.js"></script>
  </body>
</html>