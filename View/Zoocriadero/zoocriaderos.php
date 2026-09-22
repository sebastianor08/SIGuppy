<?php
    $basePath  = '../../';
    $pageTitle = 'Zoocriaderos';
    $bodyPage  = 'zoocriaderos';
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
                <h3 class="fw-bold mb-3">Zoocriaderos</h3>
                <h6 class="op-7 mb-2">Zoocriaderos</h6>
              </div>
              <div class="ms-md-auto py-2 py-md-0 d-flex gap-2 align-items-center">
                <a href="../Seguimiento_Zoocriadero/seguimiento-zoocriadero.php" class="btn btn-outline-primary btn-round">
                  <i class="fas fa-clipboard-check me-1"></i> Registrar Seguimiento
                </a>
                <div id="registrarZoocriaderoWrap">
                </div>
              </div>
            </div>
            <div class="card">
              <div class="card-body">
                <div class="sig-table-toolbar">
                  <div class="sig-search">
                    <i class="fas fa-search"></i>
                    <input type="text" id="zoocriaderosSearch" class="form-control" placeholder="Buscar por nombre, dirección, barrio o encargado..." />
                  </div>
                  <div class="d-flex align-items-center gap-2">
                    <select id="zoocriaderosEstadoFiltro" class="form-select form-select-sm" style="width:auto;">
                      <option value="todos">Todos los estados</option>
                      <option value="activo">Activos</option>
                      <option value="inactivo">Inhabilitados</option>
                    </select>
                    <span class="small text-muted" id="zoocriaderosCount"></span>
                  </div>
                </div>

                <div id="zoocriaderosMessage" class="alert d-none mb-3" role="alert"></div>

                <div class="table-responsive">
                  <table class="table align-items-center mb-0">
                    <thead class="table-light">
                      <tr>
                        <th>Zoocriadero</th>
                        <th>Dirección</th>
                        <th class="text-center">Tanques</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center">Acciones</th>
                      </tr>
                    </thead>
                    <tbody id="zoocriaderosTableBody"></tbody>
                  </table>
                </div>
              </div>
            </div>

            <!-- Modal Registrar / Editar -->
            <div class="modal fade" id="zoocriaderoModal" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <form id="zoocriaderoForm">
                    <input type="hidden" name="id" />
                    <!-- Coordenadas: vacías al registrar (el servidor geocodifica la
                         dirección); al editar conservan las del zoocriadero. -->
                    <input type="hidden" name="latitud" />
                    <input type="hidden" name="longitud" />
                    <div class="modal-header">
                      <h5 class="modal-title" id="zoocriaderoModalLabel">Registrar Zoocriadero</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                      <div class="mb-3">
                        <label class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input type="text" name="nombre" class="form-control" minlength="4" maxlength="100" required />
                      </div>
                      <div class="mb-3">
                        <label class="form-label">Dirección <span class="text-danger">*</span></label>
                        <input type="text" name="direccion" class="form-control" minlength="5" maxlength="200" required />
                      </div>
                      <div class="row">
                        <div class="col-6 mb-3">
                          <label class="form-label">Comuna <span class="text-danger">*</span></label>
                          <select name="comuna" id="comunaSelect" class="form-select" required>
                            <option value="">Seleccione la comuna</option>
                          </select>
                        </div>
                        <div class="col-6 mb-3">
                          <label class="form-label">Barrio <span class="text-danger">*</span></label>
                          <select name="barrio" id="barrioSelect" class="form-select" disabled required>
                            <option value="">Seleccione primero la comuna</option>
                          </select>
                        </div>
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancelar</button>
                      <button type="submit" class="btn btn-primary" id="zoocriaderoSubmitBtn">Guardar Registro</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>

            <!-- Modal Ver Detalle -->
            <div class="modal fade" id="zoocriaderoDetailModal" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title">Detalle del zoocriadero</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                  </div>
                  <div class="modal-body" id="zoocriaderoDetailBody"></div>
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
    $moduloPermisos = 'Zoocriaderos';
    $pageScripts = ['Web/assets/js/siguppys-zoocriaderos.js'];
    include '../partials/footer.php';
?>
  </body>
</html>