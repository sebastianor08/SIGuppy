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
                  <!-- el botón Registrar Zoocriadero lo arma assets/js/siguppys-zoocriaderos.js según el rol -->
                </div>
                <div id="registrarTanqueWrap">
                  <!-- el botón Registrar Tanque lo arma assets/js/siguppys-zoocriaderos.js según el rol -->
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
                    <div class="modal-header">
                      <h5 class="modal-title" id="zoocriaderoModalLabel">Registrar Zoocriadero</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                      <div class="mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="nombre" class="form-control" required />
                      </div>
                      <div class="mb-3">
                        <label class="form-label">Dirección</label>
                        <input type="text" name="direccion" class="form-control" required />
                      </div>
                      <div class="row">
                        <div class="col-6 mb-3">
                          <label class="form-label">Comuna</label>
                          <select name="comuna" id="comunaSelect" class="form-select" required>
                            <option value="">Seleccione la comuna</option>
                          </select>
                        </div>
                        <div class="col-6 mb-3">
                          <label class="form-label">Barrio</label>
                          <select name="barrio" id="barrioSelect" class="form-select" disabled required>
                            <option value="">Seleccione primero la comuna</option>
                          </select>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-6 mb-1">
                          <label class="form-label">Latitud <span class="text-muted small">(opcional)</span></label>
                          <input type="number" step="0.00000001" name="latitud" class="form-control" placeholder="3.42158000" />
                        </div>
                        <div class="col-6 mb-1">
                          <label class="form-label">Longitud <span class="text-muted small">(opcional)</span></label>
                          <input type="number" step="0.00000001" name="longitud" class="form-control" placeholder="-76.52050000" />
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

            <!-- Modal Registrar Tanque -->
            <div class="modal fade" id="tanqueModal" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <form id="tanqueForm">
                    <div class="modal-header">
                      <h5 class="modal-title">Registrar Tanque</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                      <div class="mb-3">
                        <label class="form-label">Zoocriadero</label>
                        <select name="id_zoocriadero" class="form-select" required></select>
                      </div>
                      <div class="mb-3">
                        <label class="form-label">Número de tanque</label>
                        <input type="number" min="1" step="1" name="numero_tanque" class="form-control" placeholder="Ej. 1" required />
                      </div>
                      <div class="mb-1">
                        <label class="form-label">Tipo de tanque</label>
                        <select name="id_tipo_tanque" class="form-select" required></select>
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancelar</button>
                      <button type="submit" class="btn btn-primary">Guardar Tanque</button>
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
    $pageScripts = ['assets/js/siguppys-zoocriaderos.js'];
    include '../partials/footer.php';
?>
  </body>
</html>
