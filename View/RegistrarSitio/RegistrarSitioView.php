<?php
$basePath  = '../../';
$pageTitle = 'Registrar Sitio';
$bodyPage  = 'registrar-sitio';
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
            <h3 class="fw-bold mb-3" id="sitioFormTitulo">Registrar Sitio</h3>
            <h6 class="op-7 mb-2">Dirección e información general del sitio</h6>
          </div>
        </div>

        <form id="registrarSitioForm">
          <input type="hidden" id="id_sitio" name="id_sitio" />

          <!-- PRIMERA PARTE: DIRECCIÓN -->
          <div class="card mb-4">
            <div class="card-header">
              <h4 class="card-title mb-1">1. Dirección</h4>
              <p class="text-muted mb-0">Seleccione la ubicación desde los catálogos registrados en la base de datos.</p>
            </div>

            <div class="card-body">
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label" for="id_departamento">Departamento</label>
                  <select class="form-select" id="id_departamento" name="id_departamento" required>
                    <option value="">Seleccione el departamento</option>
                  </select>
                </div>

                <div class="col-md-6 mb-3">
                  <label class="form-label" for="id_ciudad">Ciudad</label>
                  <select class="form-select" id="id_ciudad" name="id_ciudad" required disabled>
                    <option value="">Seleccione primero el departamento</option>
                  </select>
                </div>

                <div class="col-md-6 mb-3">
                  <label class="form-label" for="id_comuna">Comuna</label>
                  <select class="form-select" id="id_comuna" name="id_comuna" required disabled>
                    <option value="">Seleccione primero la ciudad</option>
                  </select>
                </div>

                <div class="col-md-6 mb-3">
                  <label class="form-label" for="id_barrio">Barrio</label>
                  <select class="form-select" id="id_barrio" name="id_barrio" required disabled>
                    <option value="">Seleccione primero la comuna</option>
                  </select>
                </div>

                <div class="col-md-6 mb-3">
                  <label class="form-label" for="id_nomenclatura">Nomenclatura</label>
                  <select class="form-select" id="id_nomenclatura" name="id_nomenclatura" required>
                    <option value="">Seleccione la nomenclatura</option>
                  </select>
                </div>

                <div class="col-md-6 mb-3">
                  <label class="form-label" for="numero_direccion">Número de dirección</label>
                  <input
                    type="text"
                    class="form-control"
                    id="numero_direccion"
                    name="numero_direccion"
                    maxlength="200"
                    placeholder="Ej. 12 Oeste # 4-20"
                    required
                  />
                  <div class="form-text">
                    Este campo es manual. Se puede escribir numeración con letras, # o guiones.
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- SEGUNDA PARTE: SITIO -->
          <div class="card mb-4">
            <div class="card-header">
              <h4 class="card-title mb-1">2. Información del sitio</h4>
              <p class="text-muted mb-0">La fecha y el estado no se solicitan al usuario.</p>
            </div>

            <div class="card-body">
              <div class="mb-3">
                <label class="form-label" for="nombre">Nombre</label>
                <input
                  type="text"
                  class="form-control"
                  id="nombre"
                  name="nombre"
                  maxlength="100"
                  required
                />
              </div>

              <div class="mb-3">
                <label class="form-label" for="descripcion">Descripción</label>
                <textarea
                  class="form-control"
                  id="descripcion"
                  name="descripcion"
                  rows="4"
                  maxlength="200"
                  required
                ></textarea>
                <div class="form-text text-end"><span id="descripcionCount">0</span>/200</div>
              </div>
            </div>
          </div>

          <div id="registrarSitioMessage" class="alert d-none mb-4" role="alert"></div>

          <div class="d-flex justify-content-end gap-2">
            <a href="../Sitio/SitioView.php" class="btn btn-label-secondary">Volver</a>
            <button type="submit" class="btn btn-primary" id="btnGuardarSitio">
              <i class="fas fa-save me-1"></i>Guardar Sitio
            </button>
          </div>
        </form>

      </div>
    </div>
  </div>
</div>

<?php
$pageScripts = ['Web/assets/js/siguppys-registrar-sitio.js'];
include '../partials/footer.php';
?>
</body>
</html>
