<?php
$basePath  = '../../';
$pageTitle = 'Actividades';
$bodyPage  = 'terreno-actividades';
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
            <h3 class="fw-bold mb-3">Actividades</h3>
            <h6 class="op-7 mb-2">Actividades registradas para el trabajo de terreno</h6>
          </div>

          <div class="ms-md-auto py-2 py-md-0">
            <button type="button" id="btnCrearActividad" class="btn btn-primary btn-round">
              <i class="fas fa-plus me-1"></i> Registrar Actividad
            </button>
          </div>
        </div>

        <div class="card">
          <div class="card-body">
            <div class="sig-table-toolbar">
              <div class="sig-search">
                <i class="fas fa-search"></i>
                <input
                  type="text"
                  id="actividadesSearch"
                  class="form-control"
                  placeholder="Buscar por nombre o descripción..."
                />
              </div>

              <div class="d-flex align-items-center gap-2">
                <select id="actividadesEstadoFiltro" class="form-select form-select-sm" style="width:auto;">
                  <option value="todos">Todos los estados</option>
                  <option value="activo">Activos</option>
                  <option value="inactivo">Inhabilitados</option>
                </select>
                <span class="small text-muted" id="actividadesCount"></span>
              </div>
            </div>

            <div id="actividadesMessage" class="alert d-none mb-3" role="alert"></div>

            <div class="table-responsive">
              <table class="table align-items-center mb-0">
                <thead class="table-light">
                  <tr>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th class="text-center">Estado</th>
                    <th class="text-center">Acciones</th>
                  </tr>
                </thead>
                <tbody id="actividadesTableBody">
                  <tr>
                    <td colspan="4" class="text-center text-muted py-4">Cargando...</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Modal detalle -->
        <div class="modal fade" id="actividadDetailModal" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">Detalle de la actividad</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
              </div>
              <div class="modal-body" id="actividadDetailBody"></div>
              <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cerrar</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Modal registrar / editar -->
        <div class="modal fade" id="actividadModal" tabindex="-1" aria-labelledby="actividadModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <form id="actividadForm">
                <input type="hidden" id="id_actividad" name="id_actividad" />

                <div class="modal-header">
                  <h5 class="modal-title" id="actividadModalLabel">Registrar Actividad</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body">
                  <div class="mb-3">
                    <label class="form-label" for="nombreActividad">Nombre</label>
                    <input
                      type="text"
                      class="form-control"
                      id="nombreActividad"
                      name="nombre"
                      maxlength="60"
                      placeholder="Ej. Inspección Larvaria"
                      required
                    />
                  </div>

                  <div class="mb-3">
                    <label class="form-label" for="descripcionActividad">Descripción</label>
                    <textarea
                      class="form-control"
                      id="descripcionActividad"
                      name="descripcion"
                      rows="4"
                      maxlength="200"
                      required
                    ></textarea>
                    <div class="form-text text-end"><span id="actividadDescripcionCount">0</span>/200</div>
                  </div>

                  <div id="actividadFormMessage" class="alert d-none mb-0" role="alert"></div>
                </div>

                <div class="modal-footer">
                  <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancelar</button>
                  <button type="submit" class="btn btn-primary" id="actividadSubmitBtn">
                    <i class="fas fa-save me-1"></i>Guardar
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

<?php
$pageScripts = ['Web/assets/js/siguppys-actividades.js'];
include '../partials/footer.php';
?>
</body>
</html>
