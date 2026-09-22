<?php
    $basePath  = '../../';
    $pageTitle = 'Tanques';
    $bodyPage  = 'tanques';
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
                <h3 class="fw-bold mb-3">Tanques</h3>
                <h6 class="op-7 mb-2">Todos los tanques de todos los zoocriaderos</h6>
              </div>
              <div class="ms-md-auto py-2 py-md-0 d-flex gap-2 align-items-center">
                <div id="registrarTanqueWrap"></div>
              </div>
            </div>
            <div class="card">
              <div class="card-body">
                <div class="sig-table-toolbar">
                  <div class="sig-search">
                    <i class="fas fa-search"></i>
                    <input type="text" id="tanquesSearch" class="form-control" placeholder="Buscar por zoocriadero, tipo o nombre de tanque..." />
                  </div>
                  <div class="d-flex align-items-center gap-2">
                    <select id="tanquesEstadoFiltro" class="form-select form-select-sm" style="width:auto;">
                      <option value="todos">Todos los estados</option>
                      <option value="activo">Activos</option>
                      <option value="inactivo">Inhabilitados</option>
                    </select>
                    <span class="small text-muted" id="tanquesCount"></span>
                  </div>
                </div>

                <div id="tanquesMessage" class="alert d-none mb-3" role="alert"></div>

                <div class="table-responsive">
                  <table class="table align-items-center mb-0">
                    <thead class="table-light">
                      <tr>
                        <th>Zoocriadero</th>
                        <th>Nombre</th>
                        <th>Tipo de tanque</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center">Acciones</th>
                      </tr>
                    </thead>
                    <tbody id="tanquesTableBody"></tbody>
                  </table>
                </div>
              </div>
            </div>

            <!-- Modal Registrar Tanque -->
            <div class="modal fade" id="tanqueCreateModal" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <form id="tanqueCreateForm">
                    <div class="modal-header">
                      <h5 class="modal-title">Registrar Tanque</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                      <div class="mb-3">
                        <label class="form-label">Zoocriadero <span class="text-danger">*</span></label>
                        <select name="id_zoocriadero" class="form-select" required></select>
                      </div>
                      <div class="mb-3">
                        <label class="form-label">Nombre del tanque <span class="text-danger">*</span></label>
                        <input type="text" minlength="2" maxlength="60" name="nombre_tanque" class="form-control" placeholder="Ej. Tanque Norte, Tanque de cría 1" required />
                        <div class="form-text" id="tanqueCreateNumeroHint">Seleccione primero el zoocriadero.</div>
                      </div>
                      <div class="mb-1">
                        <label class="form-label">Tipo de tanque <span class="text-danger">*</span></label>
                        <select name="id_tipo_tanque" class="form-select" required></select>
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancelar</button>
                      <button type="submit" class="btn btn-primary" id="tanqueCreateSubmitBtn">Guardar Tanque</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>

            <!-- Modal Editar Tanque -->
            <div class="modal fade" id="tanqueEditModal" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <form id="tanqueEditForm">
                    <input type="hidden" name="id_tanque" />
                    <div class="modal-header">
                      <h5 class="modal-title">Editar Tanque</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                      <div class="mb-3">
                        <label class="form-label">Zoocriadero <span class="text-danger">*</span></label>
                        <select name="id_zoocriadero" class="form-select" required></select>
                      </div>
                      <div class="mb-3">
                        <label class="form-label">Nombre del tanque <span class="text-danger">*</span></label>
                        <input type="text" minlength="2" maxlength="60" name="nombre_tanque" class="form-control" required />
                      </div>
                      <div class="mb-1">
                        <label class="form-label">Tipo de tanque <span class="text-danger">*</span></label>
                        <select name="id_tipo_tanque" class="form-select" required></select>
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancelar</button>
                      <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

<?php
    $moduloPermisos = 'Tanque Zoocriadero';
    $pageScripts = ['Web/assets/js/siguppys-tanques.js'];
    include '../partials/footer.php';
?>
  </body>
</html>