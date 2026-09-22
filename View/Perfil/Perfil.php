<?php
    $basePath  = '../../';
    $pageTitle = 'Mi Perfil';
    $bodyPage  = 'perfil';
    include '../partials/head.php';
?>
    <div class="wrapper">
      <?php $rutaBase = '../../'; ?>
      <?php include '../partials/sidebar.php'; ?>
      <div class="main-panel">
        <?php include '../partials/topbar.php'; ?>

        <div class="container">
          <div class="page-inner">
            <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
              <div>
                <h3 class="fw-bold mb-3">Mi Perfil</h3>
                <h6 class="op-7 mb-2">Inicio / Mi Perfil</h6>
              </div>
            </div>

            <div class="row">
              <div class="col-md-7">
                <div class="card">
                  <div class="card-header">
                    <h4 class="card-title">Información de la cuenta</h4>
                    <p class="text-muted small mb-0">
                      Por seguridad, solo puedes actualizar tu correo electrónico desde aquí.
                      Si necesitas corregir tu nombre, documento o rol, pide a un administrador
                      que lo haga desde "Gestión de Usuarios".
                    </p>
                  </div>
                  <div class="card-body">
                    <div id="perfilMessage" class="alert d-none mb-3" role="alert"></div>

                    <div id="perfilCargando" class="text-muted small">Cargando tu información...</div>

                    <form id="perfilForm" class="d-none">
                      <div class="row">
                        <div class="col-6 mb-3">
                          <label class="form-label">Nombres</label>
                          <input type="text" class="form-control" id="perfilNombre" disabled />
                        </div>
                        <div class="col-6 mb-3">
                          <label class="form-label">Apellidos</label>
                          <input type="text" class="form-control" id="perfilApellido" disabled />
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-6 mb-3">
                          <label class="form-label">Tipo de documento</label>
                          <input type="text" class="form-control" id="perfilTipoDocumento" disabled />
                        </div>
                        <div class="col-6 mb-3">
                          <label class="form-label">Número de documento</label>
                          <input type="text" class="form-control" id="perfilDocumento" disabled />
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-6 mb-3">
                          <label class="form-label">Rol</label>
                          <input type="text" class="form-control" id="perfilRol" disabled />
                        </div>
                        <div class="col-6 mb-3">
                          <label class="form-label">Usuario desde</label>
                          <input type="text" class="form-control" id="perfilCreadoEn" disabled />
                        </div>
                      </div>

                      <hr class="my-4" />

                      <div class="mb-1">
                        <label class="form-label" for="perfilCorreo">
                          Correo electrónico <span class="text-danger">*</span>
                        </label>
                        <input type="email" class="form-control" id="perfilCorreo" name="correo"
                               placeholder="usuario@cali.gov.co" required />
                        <div class="form-text">
                          Este es el único dato editable desde tu perfil. Solo se aceptan correos @cali.gov.co o @gmail.com.
                        </div>
                      </div>

                      <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="btn btn-primary" id="perfilSubmitBtn">
                          <i class="fas fa-save me-1"></i>Guardar correo
                        </button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

<?php
    $pageScripts = ['Web/assets/js/siguppys-perfil.js'];
    include '../partials/footer.php';
?>
  </body>
</html>