<?php
    $basePath  = '../';
    $rutaBase  = '../';
    $pageTitle = 'Registrar Sitio';
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
                <h3 class="fw-bold mb-3">Registrar Sitio</h3>
                <h6 class="op-7 mb-2">Terreno / Sitio / Registrar</h6>
              </div>
            </div>

            <?php if (!empty($errores)): ?>
              <div class="alert alert-danger">
                <ul class="mb-0">
                  <?php foreach ($errores as $e): ?>
                    <li><?php echo htmlspecialchars($e, ENT_QUOTES, 'UTF-8'); ?></li>
                  <?php endforeach; ?>
                </ul>
              </div>
            <?php endif; ?>

            <div class="card">
              <div class="card-body">
                <form method="POST" action="mvc.php?modulo=Sitio&amp;controlador=Sitio&amp;funcion=postCreate">
                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <label class="form-label">Tipo de depósito</label>
                      <select name="id_tipo_deposito" class="form-select" required>
                        <option value="">Seleccione...</option>
                        <?php foreach ($tiposDeposito as $t): ?>
                          <option value="<?php echo (int) $t['id_tipo_deposito']; ?>"
                            <?php echo (isset($_POST['id_tipo_deposito']) && $_POST['id_tipo_deposito'] == $t['id_tipo_deposito']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($t['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div class="col-md-6 mb-3">
                      <label class="form-label">Dirección</label>
                      <input type="text" name="direccion" class="form-control"
                             value="<?php echo htmlspecialchars($_POST['direccion'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                      <label class="form-label">Comuna</label>
                      <input type="text" name="comuna" class="form-control"
                             value="<?php echo htmlspecialchars($_POST['comuna'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                      <label class="form-label">Barrio</label>
                      <input type="text" name="barrio" class="form-control"
                             value="<?php echo htmlspecialchars($_POST['barrio'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                      <label class="form-label">Latitud <small class="text-muted">(opcional)</small></label>
                      <input type="text" name="latitud" class="form-control" placeholder="Ej: 3.43720000"
                             value="<?php echo htmlspecialchars($_POST['latitud'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                      <label class="form-label">Longitud <small class="text-muted">(opcional)</small></label>
                      <input type="text" name="longitud" class="form-control" placeholder="Ej: -76.52250000"
                             value="<?php echo htmlspecialchars($_POST['longitud'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                  </div>
                  <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                    <a href="mvc.php?modulo=Sitio&amp;controlador=Sitio&amp;funcion=list" class="btn btn-secondary">Cancelar</a>
                  </div>
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
