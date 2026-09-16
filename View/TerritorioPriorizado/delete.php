<?php
    $basePath  = '../';
    $rutaBase  = '../';
    $pageTitle = 'Eliminar Territorio Priorizado';
    $bodyPage  = 'terreno-territorio-priorizado';
    include __DIR__ . '/../partials/head.php';
?>
    <div class="wrapper">
      <?php include __DIR__ . '/../partials/sidebar.php'; ?>
      <div class="main-panel">
        <?php include __DIR__ . '/../partials/topbar.php'; ?>

        <div class="container">
          <div class="page-inner">
            <h3 class="fw-bold mb-3">Eliminar Territorio Priorizado</h3>
            <div class="card">
              <div class="card-body">
                <p>¿Seguro que deseas eliminar (inhabilitar) el territorio priorizado de
                  <strong><?php echo htmlspecialchars($territorio['barrio'].' - '.$territorio['comuna'], ENT_QUOTES, 'UTF-8'); ?></strong>,
                  liderado por <?php echo htmlspecialchars($territorio['nombre_lider'], ENT_QUOTES, 'UTF-8'); ?>?
                </p>
                <p class="text-muted small">No se borra el registro: queda inhabilitado pero se conserva su historial.</p>
                <form method="POST" action="mvc.php?modulo=TerritorioPriorizado&amp;controlador=TerritorioPriorizado&amp;funcion=postDelete">
                  <input type="hidden" name="id_territorio" value="<?php echo (int) $territorio['id_territorio']; ?>">
                  <button type="submit" class="btn btn-danger">Sí, eliminar</button>
                  <a href="mvc.php?modulo=TerritorioPriorizado&amp;controlador=TerritorioPriorizado&amp;funcion=list" class="btn btn-secondary">Cancelar</a>
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
