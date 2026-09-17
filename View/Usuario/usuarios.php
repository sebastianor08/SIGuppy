<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
    $basePath  = '../../';
    $pageTitle = 'Usuarios';
    $bodyPage  = 'usuarios';
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
                <h3 class="fw-bold mb-3">Gestión de Usuarios</h3>
                <h6 class="op-7 mb-2">Usuarios</h6>
              </div>
              <div class="ms-md-auto py-2 py-md-0" id="crearUsuarioWrap">
                <!-- el botón "Crear Usuario" lo arma assets/js/siguppys-usuarios.js según el rol -->
              </div>
            </div>

            <div class="card">
              <div class="card-body">
                <div class="sig-table-toolbar">
                  <div class="sig-search">
                    <i class="fas fa-search"></i>
                    <input type="text" id="usuariosSearch" class="form-control" placeholder="Buscar por nombre o correo..." />
                  </div>
                  <div class="d-flex align-items-center gap-2">
                    <select id="usuariosRolFiltro" class="form-select form-select-sm" style="width:auto;">
                      <option value="todos">Todos los roles</option>
                    </select>
                    <span class="small text-muted" id="usuariosCount"></span>
                  </div>
                </div>

                <div id="usuariosMessage" class="alert d-none mb-3" role="alert"></div>

                <div class="table-responsive">
                  <table class="table align-items-center mb-0">
                    <thead class="table-light">
                      <tr>
                        <th>Usuario</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center">Acciones</th>
                      </tr>
                    </thead>
                    <tbody id="usuariosTableBody"></tbody>
                  </table>
                </div>
              </div>
            </div>

            <!-- Modal Registrar / Editar -->
            <div class="modal fade" id="usuarioModal" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <form id="usuarioForm">
                    <input type="hidden" name="id_usuario" />
                    <div class="modal-header">
                      <h5 class="modal-title" id="usuarioModalLabel">Registrar Usuario</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                      <div class="row">
                        <div class="col-6 mb-3">
                          <label class="form-label">Nombres</label>
                          <input type="text" name="nombre" class="form-control" required />
                        </div>
                        <div class="col-6 mb-3">
                          <label class="form-label">Apellidos</label>
                          <input type="text" name="apellido" class="form-control" required />
                        </div>
                      </div>
                      <div class="mb-3">
                        <label class="form-label">Correo</label>
                        <input type="email" name="correo" class="form-control" required />
                      </div>
                      <div class="mb-3">
                        <label class="form-label">Teléfono</label>
                        <input type="text" name="telefono" class="form-control" />
                      </div>
                      <div class="mb-3">
                        <label class="form-label">Rol</label>
                        <select name="id_rol" id="rolSelect" class="form-select" required>
                          <option value="">Seleccione...</option>
                        </select>
                      </div>
                      <div class="mb-3" id="contrasenaWrap">
                        <label class="form-label">Contraseña</label>
                        <input type="password" name="contrasena" class="form-control" minlength="6" />
                        <div class="form-text">Mínimo 6 caracteres. Al editar, déjala en blanco para no cambiarla.</div>
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancelar</button>
                      <button type="submit" class="btn btn-primary">Guardar</button>
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
    $pageScripts = ['assets/js/siguppys-usuarios.js'];
    include '../partials/footer.php';
?>