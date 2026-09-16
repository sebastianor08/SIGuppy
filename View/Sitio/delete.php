<?php
    $basePath  = '../';
    $rutaBase  = '../';
    $pageTitle = 'Eliminar Sitio';
    $bodyPage  = 'terreno-sitio';
    include __DIR__ . '/../partials/head.php';
?>
    <div class="wrapper">
      <?php include __DIR__ . '/../partials/sidebar.php'; ?>
      <div class="main-panel">
        <?php include __DIR__ . '/../partials/topbar.php'; ?>

        <div class="container">
          <div class="page-inner">
            <h3 class="fw-bold mb-3">Eliminar Sitio</h3>
            <div class="card">
              <div class="card-body">
                <p>¿Seguro que deseas eliminar (inhabilitar) el sitio en
                  <strong><?php echo htmlspecialchars($sitio['direccion'], ENT_QUOTES, 'UTF-8'); ?></strong>,
                  <?php echo htmlspecialchars($sitio['barrio'].' - '.$sitio['comuna'], ENT_QUOTES, 'UTF-8'); ?>?
                </p>
                <p class="text-muted small">No se borra el registro: queda inhabilitado y ya no aparece disponible
                  para nuevos seguimientos de terreno, pero se conserva su historial.</p>
                <form method="POST" action="mvc.php?modulo=Sitio&amp;controlador=Sitio&amp;funcion=postDelete">
                  <input type="hidden" name="id_sitio" value="<?php echo (int) $sitio['id_sitio']; ?>">
                  <button type="submit" class="btn btn-danger">Sí, eliminar</button>
                  <a href="mvc.php?modulo=Sitio&amp;controlador=Sitio&amp;funcion=list" class="btn btn-secondary">Cancelar</a>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

<?php
    $pageScripts = [];
    include __DIR__ . '/../partials/footer.php';
?>
