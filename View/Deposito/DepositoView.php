<?php

    $basePath  = '../../';
    $pageTitle = 'Depósitos';
    $bodyPage  = 'terreno-depositos';

    include '../partials/head.php';

?>

<div class="wrapper">

    <?php $rutaBase = '../../'; ?>
    <?php include '../partials/sidebar.php'; ?>

    <div class="main-panel">

        <?php include '../partials/topbar.php'; ?>

        <div class="container">

            <div class="page-inner">

                <!-- Encabezado -->
                <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">

                    <div>
                        <h3 class="fw-bold mb-3">Depósitos</h3>
                        <h6 class="op-7 mb-2">Gestión de depósitos</h6>
                    </div>

                    <div class="ms-md-auto py-2 py-md-0 d-flex gap-2 align-items-center">

                        <button
                            type="button"
                            id="btnCrearDeposito"
                            class="btn btn-primary btn-round btn-crear"
                        >
                            <i class="fas fa-plus me-1"></i>
                            Registrar Depósito
                        </button>

                    </div>

                </div>

                <!-- Tabla de depósitos -->
                <div class="card">

                    <div class="card-body">

                        <!-- Barra de búsqueda y filtro -->
                        <div class="sig-table-toolbar">

                            <div class="sig-search">
                                <i class="fas fa-search"></i>

                                <input
                                    type="text"
                                    id="depositosSearch"
                                    class="form-control"
                                    placeholder="Buscar por tipo, descripción o sitio..."
                                />

                            </div>

                            <div class="d-flex align-items-center gap-2">

                                <select
                                    id="depositosEstadoFiltro"
                                    class="form-select form-select-sm"
                                    style="width:auto;"
                                >
                                    <option value="todos">Todos los estados</option>
                                    <option value="activo">Activos</option>
                                    <option value="inactivo">Inhabilitados</option>
                                </select>

                                <span
                                    class="small text-muted"
                                    id="depositosCount"
                                ></span>

                            </div>

                        </div>

                        <!-- Mensajes -->
                        <div
                            id="depositosMessage"
                            class="alert d-none mb-3"
                            role="alert"
                        ></div>

                        <!-- Tabla -->
                        <div class="table-responsive">

                            <table class="table align-items-center mb-0">

                                <thead class="table-light">

                                    <tr>
                                        <th>Tipo de depósito</th>
                                        <th>Descripción</th>
                                        <th>Sitio</th>
                                        <th class="text-center">Estado</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>

                                </thead>

                                <tbody id="depositosTableBody">
                                    <!-- Los depósitos se cargarán mediante JavaScript -->
                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>

                <!-- Modal Registrar / Editar Depósito -->
                <div
                    class="modal fade"
                    id="depositoModal"
                    tabindex="-1"
                    aria-hidden="true"
                >

                    <div class="modal-dialog">

                        <div class="modal-content">

                            <form id="depositoForm">

                                <input
                                    type="hidden"
                                    name="id_sitio"
                                />

                                <div class="modal-header">

                                    <h5
                                        class="modal-title"
                                        id="depositoModalLabel"
                                    >
                                        Registrar Depósito
                                    </h5>

                                    <button
                                        type="button"
                                        class="btn-close"
                                        data-bs-dismiss="modal"
                                        aria-label="Cerrar"
                                    ></button>

                                </div>

                                <div class="modal-body">

                                    <!-- Tipo de depósito -->
                                    <div class="mb-3">

                                        <label
                                            class="form-label"
                                            for="idTipoDeposito"
                                        >
                                            Tipo de depósito
                                        </label>

                                        <select
                                            id="idTipoDeposito"
                                            name="id_tipo_deposito"
                                            class="form-select"
                                            required
                                        >

                                            <option value="">
                                                Seleccione el tipo de depósito
                                            </option>

                                            <!-- Las opciones se cargan desde la BD (JS) -->

                                        </select>

                                    </div>

                                    <!-- Dirección / Sitio -->
                                    <div class="mb-3">

                                        <label
                                            class="form-label"
                                            for="idDireccion"
                                        >
                                            Sitio (dirección)
                                        </label>

                                        <select
                                            id="idDireccion"
                                            name="id_direccion"
                                            class="form-select"
                                            required
                                        >

                                            <option value="">
                                                Seleccione una dirección
                                            </option>

                                            <!-- Las opciones se cargan desde la BD (JS) -->

                                        </select>

                                    </div>

                                </div>

                                <div class="modal-footer">

                                    <button
                                        type="button"
                                        class="btn btn-label-secondary"
                                        data-bs-dismiss="modal"
                                    >
                                        Cancelar
                                    </button>

                                    <button
                                        type="submit"
                                        class="btn btn-primary"
                                        id="depositoSubmitBtn"
                                    >
                                        Guardar Registro
                                    </button>

                                </div>

                            </form>

                        </div>

                    </div>

                </div>

            </div>

=======
    $basePath  = '../../';
    $pageTitle = 'Depósitos';
    $bodyPage  = 'terreno-depositos';
    $extraStyles = <<<CSS
      .depositos-card {
        border-radius: 12px;
      }

      .depositos-table th {
        white-space: nowrap;
      }

      .depositos-table td {
        vertical-align: middle;
      }

      .deposito-descripcion {
        max-width: 300px;
      }

      .deposito-descripcion .descripcion {
        color: #8a8d93;
        font-size: 12px;
      }

      .deposito-direccion {
        min-width: 180px;
      }

      #depositosTableBody .sig-empty-row td {
        padding: 45px 20px;
      }

      .btn-crear-deposito {
        min-width: 125px;
      }
CSS;
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
                <h3 class="fw-bold mb-3">Trabajo de terreno - Depósitos</h3>
                <h6 class="op-7 mb-2">Trabajo de terreno / Depósitos</h6>
              </div>
            </div>

            <div id="depositosMessage" class="alert d-none mb-3" role="alert"></div>

            <div class="card card-round depositos-card">
              <div class="card-body">
                <div class="sig-table-toolbar">
                  <div class="sig-search">
                    <i class="fas fa-search"></i>
                    <input type="text" id="depositosSearch" class="form-control" placeholder="Buscar por tipo, descripción o dirección...">
                  </div>
                  <div class="d-flex align-items-center gap-2">
                    <select id="depositosEstadoFiltro" class="form-select form-select-sm" style="width:auto;">
                      <option value="todos">Todos los estados</option>
                      <option value="activo">Activos</option>
                      <option value="inactivo">Inactivos</option>
                    </select>
                    <span class="small text-muted" id="depositosCount"></span>
                  </div>
                </div>

                <div class="table-responsive">
                  <table class="table align-items-center mb-0 depositos-table">
                    <thead class="table-light">
                      <tr>
                        <th>Tipo depósito</th>
                        <th>Descripción</th>
                        <th>Dirección</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center">Acciones</th>
                      </tr>
                    </thead>
                    <tbody id="depositosTableBody">
                      <tr class="sig-empty-row">
                        <td colspan="5">Cargando depósitos...</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

            <div class="d-flex justify-content-end mt-4 only-coordinador">
              <button type="button" id="btnCrearDeposito" class="btn btn-primary btn-round btn-crear-deposito" data-bs-toggle="modal" data-bs-target="#depositoModal">
                <i class="fas fa-plus me-1"></i>
                Crear Depósito
              </button>
            </div>

          </div>
        </div>
      </div>
    </div>

    <!-- Modal Crear / Editar Depósito -->
    <div class="modal fade" id="depositoModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <form id="depositoForm">
            <input type="hidden" name="id_sitio">
            <div class="modal-header">
              <h5 class="modal-title" id="depositoModalLabel">Crear Depósito</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
              <div class="mb-3">
                <label class="form-label">Tipo de depósito</label>
                <select name="id_tipo_deposito" id="idTipoDeposito" class="form-select" required>
                  <option value="">Cargando tipos...</option>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label">Dirección</label>
                <select name="id_direccion" id="idDireccion" class="form-select" required>
                  <option value="">Cargando direcciones...</option>
                </select>
                <div class="form-text">
                  La dirección debe existir previamente en la tabla <strong>direccion</strong>.
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancelar</button>
              <button type="submit" class="btn btn-primary" id="depositoSubmitBtn">Guardar</button>
            </div>
          </form>
>>>>>>> origin/dev
        </div>
      </div>
    </div>

</div>

<?php

    $pageScripts = ['Web/assets/js/siguppys-depositos.js'];

    include '../partials/footer.php';

?>

</body>
</html>
