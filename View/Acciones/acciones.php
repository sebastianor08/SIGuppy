<?php
    $basePath  = '../../';
    $pageTitle = 'Acciones';
    $bodyPage  = 'acciones-zoocriadero';
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
                <h3 class="fw-bold mb-3">Zoocriaderos</h3>
                <h6 class="op-7 mb-2">Gestiona la información de los zoocriaderos, tipos de tanque y acciones disponibles.</h6>
              </div>
            </div>

            <div class="card">
              <div class="card-body">
                <h5 class="fw-bold mb-3">Gestión de Acciones</h5>
                <form id="accionForm">
                  <input type="hidden" name="id_actividad" />
                  <div class="mb-3">
                    <label class="form-label">Nombre de la Acción*</label>
                    <input type="text" name="nombre" class="form-control" placeholder="Ej: Limpieza de tanque" required />
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Descripción</label>
                    <textarea name="descripcion" class="form-control" rows="3" placeholder="Descripción de la acción..."></textarea>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-select">
                      <option value="1">Activo</option>
                      <option value="0">Inhabilitado</option>
                    </select>
                  </div>
                  <div class="d-flex gap-2">
                    <button type="button" class="btn btn-success" id="accionGuardarBtn">
                      <i class="fas fa-save me-1"></i> Guardar Cambios
                    </button>
                    <button type="button" class="btn btn-label-secondary" id="accionLimpiarBtn">
                      <i class="fas fa-sync-alt me-1"></i> Limpiar
                    </button>
                  </div>
                </form>
              </div>
            </div>

            <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-2">
              <div>
                <h5 class="fw-bold mb-0">Acciones Registradas</h5>
                <h6 class="op-7 mb-2">Listado de acciones disponibles en el sistema.</h6>
              </div>
              <div class="ms-md-auto py-2 py-md-0" id="crearAccionWrap"></div>
            </div>
            <div class="card">
              <div class="card-body">
                <div id="accionesMessage" class="alert d-none mb-3" role="alert"></div>

                <div class="table-responsive">
                  <table class="table align-items-center mb-0">
                    <thead class="table-light">
                      <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center">Acciones</th>
                      </tr>
                    </thead>
                    <tbody id="accionesTableBody"></tbody>
                  </table>
                </div>
              </div>
            </div>

            <div class="modal fade" id="accionDetailModal" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title">Detalle de la acción</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                  </div>
                  <div class="modal-body" id="accionDetailBody"></div>
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
    $pageScripts = ['Web/assets/js/siguppys-acciones-zoocriadero.js'];
    include '../partials/footer.php';
?>
  </body>
</html>