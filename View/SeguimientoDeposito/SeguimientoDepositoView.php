<?php
$basePath  = '../../';
$pageTitle = 'Seguimiento de Depósito';
$bodyPage  = 'terreno-seguimiento-deposito';
$extraCss  = ['Web/assets/css/leaflet.css'];
$moduloPermisos = 'Seguimiento de Depósito';
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
            <h3 class="fw-bold mb-3">Seguimiento de Depósito</h3>
            <h6 class="op-7 mb-2">Registre las visitas de seguimiento a un depósito y consulte dónde está cada uno en el mapa</h6>
          </div>

          <div class="ms-md-auto py-2 py-md-0">
            <a href="../Deposito/DepositoView.php" class="btn btn-label-secondary btn-round">
              <i class="fas fa-arrow-left me-1"></i> Ir a Depósitos
            </a>
          </div>
        </div>

        <div class="row g-4">
          <!-- ===================== Formulario ===================== -->
          <div class="col-lg-5">
            <div class="card sig-followup-card h-100">
              <div class="card-header">
                <h4 class="card-title mb-0" id="sdFormTitulo">Registrar seguimiento</h4>
              </div>
              <div class="card-body">
                <form id="seguimientoDepositoForm" novalidate>
                  <input type="hidden" id="id_seguimiento_deposito" name="id_seguimiento_deposito" value="" />

                  <div class="row g-3">
                    <div class="col-12">
                      <label class="form-label" for="sdDeposito">Depósito <span class="text-danger">*</span></label>
                      <select class="form-select" id="sdDeposito" name="id_deposito" required>
                        <option value="">Seleccione el depósito</option>
                      </select>
                      <div class="form-text">También puede elegirlo haciendo clic en un punto del mapa.</div>
                    </div>

                    <div class="col-12">
                      <label class="form-label" for="sdDireccion">Dirección</label>
                      <input type="text" class="form-control" id="sdDireccion" readonly
                             placeholder="Se carga desde el sitio del depósito" />
                    </div>

                    <div class="col-md-6">
                      <label class="form-label" for="sdFecha">Fecha <span class="text-danger">*</span></label>
                      <input type="date" class="form-control" id="sdFecha" name="fecha" required />
                    </div>

                    <div class="col-md-6">
                      <label class="form-label" for="sdActividad">Acción realizada <span class="text-danger">*</span></label>
                      <select class="form-select" id="sdActividad" name="id_actividad" required>
                        <option value="">Seleccione la acción</option>
                      </select>
                    </div>

                    <div class="col-md-6">
                      <label class="form-label" for="sdLarvas">¿Se encontraron larvas? <span class="text-danger">*</span></label>
                      <select class="form-select" id="sdLarvas" name="presencia_larvas" required>
                        <option value="">Seleccione</option>
                        <option value="1">Sí, hay larvas</option>
                        <option value="0">No, sin larvas</option>
                      </select>
                    </div>

                    <div class="col-md-6">
                      <label class="form-label" for="sdPeces">Peces sembrados</label>
                      <input type="number" class="form-control" id="sdPeces" name="numero_peces_sembrados"
                             min="0" max="1000000" step="1" value="0" />
                    </div>

                    <div class="col-12">
                      <label class="form-label" for="sdObservaciones">Observaciones <span class="text-muted small">(opcional)</span></label>
                      <textarea class="form-control" id="sdObservaciones" name="observaciones" rows="3"
                                maxlength="300" placeholder="Escriba aquí las observaciones..."></textarea>
                      <div class="form-text text-end"><span id="sdObservacionesCount">0</span>/300</div>
                    </div>
                  </div>

                  <div id="sdFormMessage" class="alert d-none mt-3 mb-0" role="alert"></div>

                  <div class="d-flex justify-content-end mt-4 gap-2">
                    <button type="button" id="sdBtnCancelarEdicion" class="btn btn-label-secondary d-none">Cancelar edición</button>
                    <button type="submit" id="sdBtnGuardar" class="btn btn-primary">
                      <i class="fas fa-save me-1"></i>Guardar
                    </button>
                  </div>
                </form>
              </div>
            </div>
          </div>

          <!-- ======================== Mapa ======================== -->
          <div class="col-lg-7">
            <div class="card h-100">
              <div class="card-header">
                <h4 class="card-title mb-0">Mapa de depósitos registrados</h4>
              </div>
              <div class="card-body d-flex flex-column">
                <div id="mapaSeguimientoDeposito" class="flex-grow-1"></div>
                <div id="sdLeyenda" class="mt-2">
                  <span class="text-muted small">Cargando depósitos…</span>
                </div>
                <div id="sdSinUbicacion" class="alert alert-warning small d-none mt-2 mb-0" role="alert"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- ====================== Historial ====================== -->
        <div class="card mt-4">
          <div class="card-header">
            <h4 class="card-title mb-0">Seguimientos registrados</h4>
          </div>
          <div class="card-body">
            <div class="sig-table-toolbar">
              <div class="sig-search">
                <i class="fas fa-search"></i>
                <input
                  type="text"
                  id="sdSearch"
                  class="form-control"
                  placeholder="Buscar por depósito, sitio, acción o responsable..."
                />
              </div>

              <div class="d-flex align-items-center gap-2">
                <select id="sdEstadoFiltro" class="form-select form-select-sm" style="width:auto;">
                  <option value="todos">Todos los estados</option>
                  <option value="activo">Activos</option>
                  <option value="inactivo">Inhabilitados</option>
                </select>
                <span class="small text-muted" id="sdCount"></span>
              </div>
            </div>

            <div id="sdMessage" class="alert d-none mb-3" role="alert"></div>

            <div class="table-responsive">
              <table class="table align-items-center mb-0 sig-followup-table">
                <thead class="table-light">
                  <tr>
                    <th>Fecha</th>
                    <th>Depósito</th>
                    <th>Sitio</th>
                    <th>Acción</th>
                    <th>Responsable</th>
                    <th class="text-center">Estado</th>
                    <th class="text-center">Acciones</th>
                  </tr>
                </thead>
                <tbody id="sdTableBody">
                  <tr>
                    <td colspan="7" class="text-center text-muted py-4">Cargando...</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Modal detalle -->
        <div class="modal fade" id="sdDetailModal" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">Detalle del seguimiento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
              </div>
              <div class="modal-body" id="sdDetailBody"></div>
              <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cerrar</button>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<?php
// Leaflet primero (define L) y después el JS de esta página.
$pageScripts = [
    'Web/assets/js/plugin/leaflet/leaflet.js',
    'Web/assets/js/siguppys-seguimiento-deposito.js'
];
include '../partials/footer.php';
?>
</body>
</html>