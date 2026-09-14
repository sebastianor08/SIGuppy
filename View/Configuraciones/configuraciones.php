<?php
    $basePath  = '../../';
    $pageTitle = 'Configuraciones';
    $bodyPage  = 'configuraciones';
    include '../partials/head.php';
?>
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
                    <p>Reemplaza el rojo/verde de los estados (Activo / Inhabilitado) por una paleta azul/naranja, más fácil de distinguir para personas con daltonismo rojo-verde.</p>
                  </div>
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="switchDaltonismo" />
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

<?php
    $pageScripts = ['assets/js/siguppys-settings.js'];
    include '../partials/footer.php';
?>
  </body>
</html>
