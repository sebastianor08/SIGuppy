<?php
$basePath  = '../../';
$pageTitle = 'Sitios';
$bodyPage  = 'sitios';
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
            <h3 class="fw-bold mb-3">Sitios</h3>
            <h6 class="op-7 mb-2">Sitios registrados para el trabajo de terreno</h6>
          </div>

          <div class="ms-md-auto py-2 py-md-0">
            <a href="../RegistrarSitio/RegistrarSitioView.php" class="btn btn-primary btn-round">
              <i class="fas fa-plus me-1"></i> Registrar Sitio
            </a>
          </div>
        </div>

        <div class="card">
          <div class="card-body">
            <div class="sig-table-toolbar">
              <div class="sig-search">
                <i class="fas fa-search"></i>
                <input
                  type="text"
                  id="sitiosSearch"
                  class="form-control"
                  placeholder="Buscar por nombre, descripción o dirección..."
                />
              </div>

              <div class="d-flex align-items-center gap-2">
                <select id="sitiosEstadoFiltro" class="form-select form-select-sm" style="width:auto;">
                  <option value="todos">Todos los estados</option>
                  <option value="activo">Activos</option>
                  <option value="inactivo">Inhabilitados</option>
                </select>
                <span class="small text-muted" id="sitiosCount"></span>
              </div>
            </div>

            <div id="sitiosMessage" class="alert d-none mb-3" role="alert"></div>

            <div class="table-responsive">
              <table class="table align-items-center mb-0">
                <thead class="table-light">
                  <tr>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Dirección</th>
                    <th>Fecha</th>
                    <th class="text-center">Estado</th>
                    <th class="text-center">Acciones</th>
                  </tr>
                </thead>
                <tbody id="sitiosTableBody">
                  <tr>
                    <td colspan="6" class="text-center text-muted py-4">Cargando...</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Modal detalle -->
        <div class="modal fade" id="sitioDetailModal" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">Detalle del sitio</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
              </div>
              <div class="modal-body" id="sitioDetailBody"></div>
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
$pageScripts = ['Web/assets/js/siguppys-sitios.js'];
include '../partials/footer.php';
?>
</body>
</html>
