<?php
    $basePath  = '../';
    $rutaBase  = '../';
    $pageTitle = 'Territorio Priorizado';
    $bodyPage  = 'terreno-territorio-priorizado';
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
                <h3 class="fw-bold mb-3">Territorio Priorizado</h3>
                <h6 class="op-7 mb-2">Terreno / Territorio Priorizado</h6>
              </div>
              <div class="ms-md-auto py-2 py-md-0">
                <a href="mvc.php?modulo=TerritorioPriorizado&amp;controlador=TerritorioPriorizado&amp;funcion=getCreate" class="btn btn-primary btn-round">
                  <i class="fas fa-plus me-1"></i> Registrar Territorio
                </a>
              </div>
            </div>

            <?php if (isset($_GET['ok'])): ?>
              <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php
                  $mensajes = ['creado' => 'Territorio priorizado registrado correctamente.', 'actualizado' => 'Territorio actualizado correctamente.', 'eliminado' => 'Territorio eliminado correctamente.'];
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
                    <input type="text" id="territorioBuscar" class="form-control" placeholder="Buscar por comuna, barrio o líder...">
                  </div>
                </div>

                <div class="table-responsive">
                  <table class="table align-items-center mb-0" id="tablaTerritorios">
                    <thead class="table-light">
                      <tr>
                        <th>Comuna</th>
                        <th>Barrio</th>
                        <th>Sitio</th>
                        <th>Líder comunitario</th>
                        <th>Funcionario responsable</th>
                        <th class="text-center">Estado</th>
                        <th class="text-end">Acciones</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if (empty($territorios)): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">Aún no hay territorios priorizados registrados.</td></tr>
                      <?php endif; ?>
                      <?php foreach ($territorios as $t): ?>
                        <tr data-texto="<?php echo strtolower(htmlspecialchars($t['comuna'].' '.$t['barrio'].' '.$t['nombre_lider'], ENT_QUOTES, 'UTF-8')); ?>">
                          <td><?php echo htmlspecialchars($t['comuna'], ENT_QUOTES, 'UTF-8'); ?></td>
                          <td><?php echo htmlspecialchars($t['barrio'], ENT_QUOTES, 'UTF-8'); ?></td>
                          <td><?php echo htmlspecialchars($t['sitio'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                          <td><?php echo htmlspecialchars($t['nombre_lider'], ENT_QUOTES, 'UTF-8'); ?></td>
                          <td><?php echo htmlspecialchars($t['funcionario_nombre'].' '.$t['funcionario_apellido'], ENT_QUOTES, 'UTF-8'); ?></td>
                          <td class="text-center">
                            <span class="badge-estado <?php echo $t['estado'] == 1 ? 'activo' : 'inactivo'; ?>">
                              <?php echo $t['estado'] == 1 ? 'Activo' : 'Inhabilitado'; ?>
                            </span>
                          </td>
                          <td class="text-end">
                            <a href="mvc.php?modulo=TerritorioPriorizado&amp;controlador=TerritorioPriorizado&amp;funcion=getUpdate&amp;id=<?php echo (int) $t['id_territorio']; ?>"
                               class="btn btn-icon btn-link btn-sm" title="Editar">
                              <i class="fa fa-pencil-alt text-primary"></i>
                            </a>
                            <a href="mvc.php?modulo=TerritorioPriorizado&amp;controlador=TerritorioPriorizado&amp;funcion=getDelete&amp;id=<?php echo (int) $t['id_territorio']; ?>"
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
  document.getElementById('territorioBuscar').addEventListener('input', function () {
    var texto = this.value.toLowerCase();
    document.querySelectorAll('#tablaTerritorios tbody tr[data-texto]').forEach(function (fila) {
      fila.style.display = fila.dataset.texto.indexOf(texto) !== -1 ? '' : 'none';
    });
  });
</script>

<?php
    $pageScripts = [];
    include __DIR__ . '/../partials/footer.php';
?>
