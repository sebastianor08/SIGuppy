<?php
    $basePath  = '../../';
    $pageTitle = 'Copia de seguridad';
    $bodyPage  = 'copia-seguridad';
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
                <h3 class="fw-bold mb-3">Gestión de Copias de Seguridad</h3>
                <h6 class="op-7 mb-2">Descargue y restaure los puntos de respaldo de BD_Dengue_SIGuppy.</h6>
              </div>
              <div class="ms-md-auto py-2 py-md-0">
                <span class="badge-estado activo" id="bdEstadoPill">
                  <i class="fas fa-circle" style="font-size:7px; vertical-align:1px; margin-right:5px;"></i>
                  <span id="bdEstadoTexto">Comprobando conexión...</span>
                </span>
              </div>
            </div>

            <!-- Tarjetas de estado -->
            <div class="row">
              <div class="col-sm-6 col-md-4">
                <div class="card card-stats card-round">
                  <div class="card-body">
                    <div class="row align-items-center">
                      <div class="col-icon">
                        <div class="icon-big text-center icon-info bubble-shadow-small">
                          <i class="fas fa-clock"></i>
                        </div>
                      </div>
                      <div class="col col-stats ms-3 ms-sm-0">
                        <div class="numbers">
                          <p class="card-category">Último Backup</p>
                          <h4 class="card-title" id="statUltimoBackup">—</h4>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-sm-6 col-md-4">
                <div class="card card-stats card-round">
                  <div class="card-body">
                    <div class="row align-items-center">
                      <div class="col-icon">
                        <div class="icon-big text-center icon-success bubble-shadow-small">
                          <i class="fas fa-database"></i>
                        </div>
                      </div>
                      <div class="col col-stats ms-3 ms-sm-0">
                        <div class="numbers">
                          <p class="card-category">Tamaño de BD</p>
                          <h4 class="card-title" id="statTamanoBD">—</h4>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-sm-6 col-md-4">
                <div class="card card-stats card-round">
                  <div class="card-body">
                    <div class="row align-items-center">
                      <div class="col-icon">
                        <div class="icon-big text-center icon-warning bubble-shadow-small">
                          <i class="fas fa-box-archive"></i>
                        </div>
                      </div>
                      <div class="col col-stats ms-3 ms-sm-0">
                        <div class="numbers">
                          <p class="card-category">Respaldos disponibles</p>
                          <h4 class="card-title" id="statRespaldos">—</h4>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Acciones de seguridad -->
            <div class="card">
              <div class="card-body">
                <h6 class="fw-bold mb-3">Acciones de seguridad</h6>
                <div id="copiaSeguridadMessage" class="alert d-none mb-3" role="alert"></div>
                <div class="d-flex flex-wrap gap-2">
                  <button type="button" class="btn btn-primary btn-round" id="btnDescargarCopia">
                    <i class="fas fa-download me-1"></i> Descargar Copia
                  </button>
                  <button type="button" class="btn btn-outline-warning btn-round" id="btnAbrirRestaurar">
                    <i class="fas fa-rotate me-1"></i> Restaurar Base de Datos
                  </button>
                </div>
                <p class="small text-muted mt-3 mb-0">
                  "Descargar Copia" genera un respaldo nuevo de <strong>BD_Dengue_SIGuppy</strong> en este momento
                  y lo descarga a tu equipo. Además de esto, el sistema puede generar un respaldo automático cada
                  24 horas (ver <code>cron/backup_automatico.php</code>).
                </p>
              </div>
            </div>

            <!-- Historial de auditoría -->
            <div class="card">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <h6 class="fw-bold mb-0">Historial de Auditoría de Backups</h6>
                  <span class="small text-muted text-uppercase">Últimas operaciones realizadas</span>
                </div>
                <div class="table-responsive">
                  <table class="table align-items-center mb-0">
                    <thead class="table-light">
                      <tr>
                        <th>Fecha y Hora</th>
                        <th>Tipo de Operación</th>
                        <th>Nombre de Archivo</th>
                        <th>Ejecutado por</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center">Acciones</th>
                      </tr>
                    </thead>
                    <tbody id="historialTableBody">
                      <tr class="sig-empty-row"><td colspan="6">Cargando historial...</td></tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Restaurar Base de Datos -->
    <div class="modal fade" id="restaurarModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <form id="restaurarForm">
            <div class="modal-header">
              <h5 class="modal-title">Restaurar Base de Datos</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
              <div class="alert alert-warning">
                <i class="fas fa-triangle-exclamation me-1"></i>
                Esta acción reemplaza los datos actuales de <strong>BD_Dengue_SIGuppy</strong>. Te recomendamos
                descargar una copia reciente antes de continuar.
              </div>

              <div class="mb-3">
                <label class="form-label">Elegir un respaldo existente</label>
                <select id="restaurarSelectExistente" name="archivo_existente" class="form-select">
                  <option value="">Seleccione un respaldo...</option>
                </select>
              </div>

              <div class="text-center text-muted small mb-3">— o —</div>

              <div class="mb-1">
                <label class="form-label">Subir un archivo .sql</label>
                <input type="file" id="restaurarArchivoInput" name="archivo" class="form-control" accept=".sql" />
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancelar</button>
              <button type="submit" class="btn btn-warning" id="restaurarSubmitBtn">Restaurar</button>
            </div>
          </form>
        </div>
      </div>
    </div>

<?php
    $pageScripts = ['Web/assets/js/siguppys-copia-seguridad.js'];
    include '../partials/footer.php';
?>
  </body>
</html>
