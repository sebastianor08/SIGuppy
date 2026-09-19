<?php
$basePath  = '../../';
$pageTitle = 'Tipos de Depósito';
$bodyPage  = 'terreno-tipo-depositos';
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
            <h3 class="fw-bold mb-3">Tipos de Depósito</h3>
            <h6 class="op-7 mb-2">Tipos de depósito registrados para el trabajo de terreno</h6>
          </div>

          <div class="ms-md-auto py-2 py-md-0">
            <button type="button" id="btnCrearTipoDeposito" class="btn btn-primary btn-round">
              <i class="fas fa-plus me-1"></i> Registrar Tipo de Depósito
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
                  id="tiposDepositoSearch"
                  class="form-control"
                  placeholder="Buscar por nombre o descripción..."
                />
              </div>

              <div class="d-flex align-items-center gap-2">
                <select id="tiposDepositoEstadoFiltro" class="form-select form-select-sm" style="width:auto;">
                  <option value="todos">Todos los estados</option>
                  <option value="activo">Activos</option>
                  <option value="inactivo">Inhabilitados</option>
                </select>
                <span class="small text-muted" id="tiposDepositoCount"></span>
              </div>
            </div>

            <div id="tiposDepositoMessage" class="alert d-none mb-3" role="alert"></div>

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
                <tbody id="tiposDepositoTableBody">
                  <tr>
                    <td colspan="4" class="text-center text-muted py-4">Cargando...</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Modal detalle -->
        <div class="modal fade" id="tipoDepositoDetailModal" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">Detalle del tipo de depósito</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
              </div>
              <div class="modal-body" id="tipoDepositoDetailBody"></div>
              <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cerrar</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Modal registrar / editar -->
        <div class="modal fade" id="tipoDepositoModal" tabindex="-1" aria-labelledby="tipoDepositoModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <form id="tipoDepositoForm">
                <input type="hidden" id="id_tipo_deposito" name="id_tipo_deposito" />

                <div class="modal-header">
                  <h5 class="modal-title" id="tipoDepositoModalLabel">Registrar Tipo de Depósito</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body">
                  <div class="mb-3">
                    <label class="form-label" for="nombreTipoDeposito">Nombre</label>
                    <input
                      type="text"
                      class="form-control"
                      id="nombreTipoDeposito"
                      name="nombre"
                      maxlength="60"
                      placeholder="Ej. Llanta / Neumático desechado"
                      required
                    />
                  </div>

                  <div class="mb-3">
                    <label class="form-label" for="descripcionTipoDeposito">Descripción</label>
                    <textarea
                      class="form-control"
                      id="descripcionTipoDeposito"
                      name="descripcion"
                      rows="4"
                      maxlength="200"
                      required
                    ></textarea>
                    <div class="form-text text-end"><span id="tipoDepositoDescripcionCount">0</span>/200</div>
                  </div>

                  <div id="tipoDepositoFormMessage" class="alert d-none mb-0" role="alert"></div>
                </div>

                <div class="modal-footer">
                  <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancelar</button>
                  <button type="submit" class="btn btn-primary" id="tipoDepositoSubmitBtn">
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
$pageScripts = ['Web/assets/js/siguppys-tipos-deposito.js'];
include '../partials/footer.php';
?>
</body>
</html>
