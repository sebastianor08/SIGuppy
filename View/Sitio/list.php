<?php
    // Esta vista se sirve siempre a través de Web/mvc.php (la URL real en el
    // navegador es Web/mvc.php?modulo=Sitio&...), por eso basePath/rutaBase
    // son '../' (un solo nivel hasta la raíz del proyecto) y no '../../'
    // como en las vistas que SÍ son un archivo suelto dentro de View/.
    $basePath  = '../';
    $rutaBase  = '../';
    $pageTitle = 'Sitio';
    $bodyPage  = 'terreno-sitio';
    include __DIR__ . '/../partials/head.php';
?>
    <div class="wrapper">
      <?php include __DIR__ . '/../partials/sidebar.php'; ?>
      <div class="main-panel">
        <?php include __DIR__ . '/../partials/topbar.php'; ?>

        <div class="container">
          <div class="page-inner">
            <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
              <div>
                <h3 class="fw-bold mb-3">Sitio</h3>
                <h6 class="op-7 mb-2">Terreno / Sitio</h6>
              </div>
              <div class="ms-md-auto py-2 py-md-0">
                <a href="mvc.php?modulo=Sitio&amp;controlador=Sitio&amp;funcion=getCreate" class="btn btn-primary btn-round">
                  <i class="fas fa-plus me-1"></i> Registrar Sitio
                </a>
              </div>
            </div>

            <?php if (isset($_GET['ok'])): ?>
              <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php
                  $mensajes = ['creado' => 'Sitio registrado correctamente.', 'actualizado' => 'Sitio actualizado correctamente.', 'eliminado' => 'Sitio eliminado correctamente.'];
                  echo htmlspecialchars($mensajes[$_GET['ok']] ?? 'Listo.', ENT_QUOTES, 'UTF-8');
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>
            <?php endif; ?>

            <div class="card">
              <div class="card-body">
                <div class="sig-table-toolbar mb-3">
                  <div class="sig-search">
                    <i class="fas fa-search"></i>
                    <input type="text" id="sitioBuscar" class="form-control" placeholder="Buscar por dirección, comuna o barrio...">
                  </div>
                </div>

                <div class="table-responsive">
                  <table class="table align-items-center mb-0" id="tablaSitios">
                    <thead class="table-light">
                      <tr>
                        <th>Dirección</th>
                        <th>Comuna</th>
                        <th>Barrio</th>
                        <th>Tipo de depósito</th>
                        <th class="text-center">Estado</th>
                        <th class="text-end">Acciones</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if (empty($sitios)): ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">Aún no hay sitios registrados.</td></tr>
                      <?php endif; ?>
                      <?php foreach ($sitios as $s): ?>
                        <tr data-texto="<?php echo strtolower(htmlspecialchars($s['direccion'].' '.$s['comuna'].' '.$s['barrio'], ENT_QUOTES, 'UTF-8')); ?>">
                          <td><?php echo htmlspecialchars($s['direccion'], ENT_QUOTES, 'UTF-8'); ?></td>
                          <td><?php echo htmlspecialchars($s['comuna'], ENT_QUOTES, 'UTF-8'); ?></td>
                          <td><?php echo htmlspecialchars($s['barrio'], ENT_QUOTES, 'UTF-8'); ?></td>
                          <td><?php echo htmlspecialchars($s['tipo_deposito'], ENT_QUOTES, 'UTF-8'); ?></td>
                          <td class="text-center">
                            <span class="badge-estado <?php echo $s['estado'] == 1 ? 'activo' : 'inactivo'; ?>">
                              <?php echo $s['estado'] == 1 ? 'Activo' : 'Inhabilitado'; ?>
                            </span>
                          </td>
                          <td class="text-end">
                            <a href="mvc.php?modulo=Sitio&amp;controlador=Sitio&amp;funcion=getUpdate&amp;id=<?php echo (int) $s['id_sitio']; ?>"
                               class="btn btn-icon btn-link btn-sm" title="Editar">
                              <i class="fa fa-pencil-alt text-primary"></i>
                            </a>
                            <a href="mvc.php?modulo=Sitio&amp;controlador=Sitio&amp;funcion=getDelete&amp;id=<?php echo (int) $s['id_sitio']; ?>"
                               class="btn btn-icon btn-link btn-sm" title="Eliminar">
                              <i class="fa fa-ban text-danger"></i>
                            </a>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

<script>
  // Filtro de búsqueda simple, en el navegador (sin llamadas al servidor).
  document.getElementById('sitioBuscar').addEventListener('input', function () {
    var texto = this.value.toLowerCase();
    document.querySelectorAll('#tablaSitios tbody tr[data-texto]').forEach(function (fila) {
      fila.style.display = fila.dataset.texto.indexOf(texto) !== -1 ? '' : 'none';
    });
  });
</script>

<?php
    $pageScripts = [];
    include __DIR__ . '/../partials/footer.php';
?>
