<?php
    $basePath  = '../';
    $rutaBase  = '../';
    $pageTitle = 'Registrar Territorio Priorizado';
    $bodyPage  = 'terreno-territorio-priorizado';
    include __DIR__ . '/../partials/head.php';

    // Ayuda para no repetir htmlspecialchars() en cada input.
    // $v puede venir de $_POST (si hubo un error de validación) o llegar vacío.
    function tpVal($campo) {
        return htmlspecialchars($_POST[$campo] ?? '', ENT_QUOTES, 'UTF-8');
    }
?>
    <div class="wrapper">
      <?php include __DIR__ . '/../partials/sidebar.php'; ?>
      <div class="main-panel">
        <?php include __DIR__ . '/../partials/topbar.php'; ?>

        <div class="container">
          <div class="page-inner">
            <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
              <div>
                <h3 class="fw-bold mb-3">Registrar Territorio Priorizado</h3>
                <h6 class="op-7 mb-2">Terreno / Territorio Priorizado / Registrar</h6>
              </div>
            </div>

            <?php if (!empty($errores)): ?>
              <div class="alert alert-danger">
                <ul class="mb-0">
                  <?php foreach ($errores as $e): ?><li><?php echo htmlspecialchars($e, ENT_QUOTES, 'UTF-8'); ?></li><?php endforeach; ?>
                </ul>
              </div>
            <?php endif; ?>

            <div class="card">
              <div class="card-body">
                <form method="POST" action="mvc.php?modulo=TerritorioPriorizado&amp;controlador=TerritorioPriorizado&amp;funcion=postCreate">

                  <h6 class="fw-bold mb-3">Ubicación</h6>
                  <div class="row">
                    <div class="col-md-4 mb-3">
                      <label class="form-label">Comuna</label>
                      <input type="text" name="comuna" class="form-control" value="<?php echo tpVal('comuna'); ?>" required>
                    </div>
                    <div class="col-md-4 mb-3">
                      <label class="form-label">Barrio</label>
                      <input type="text" name="barrio" class="form-control" value="<?php echo tpVal('barrio'); ?>" required>
                    </div>
                    <div class="col-md-4 mb-3">
                      <label class="form-label">Sitio de referencia <small class="text-muted">(opcional)</small></label>
                      <input type="text" name="sitio" class="form-control" placeholder="Ej: Cancha comunitaria" value="<?php echo tpVal('sitio'); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                      <label class="form-label">Dirección del sitio <small class="text-muted">(opcional)</small></label>
                      <input type="text" name="direccion_sitio" class="form-control" value="<?php echo tpVal('direccion_sitio'); ?>">
                    </div>
                    <div class="col-md-3 mb-3">
                      <label class="form-label">Latitud <small class="text-muted">(opcional)</small></label>
                      <input type="text" name="latitud" class="form-control" value="<?php echo tpVal('latitud'); ?>">
                    </div>
                    <div class="col-md-3 mb-3">
                      <label class="form-label">Longitud <small class="text-muted">(opcional)</small></label>
                      <input type="text" name="longitud" class="form-control" value="<?php echo tpVal('longitud'); ?>">
                    </div>
                  </div>

                  <hr>
                  <h6 class="fw-bold mb-3">Líder comunitario</h6>
                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <label class="form-label">Nombre del líder</label>
                      <input type="text" name="nombre_lider" class="form-control" value="<?php echo tpVal('nombre_lider'); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                      <label class="form-label">Dirección del líder <small class="text-muted">(opcional)</small></label>
                      <input type="text" name="direccion_lider" class="form-control" value="<?php echo tpVal('direccion_lider'); ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                      <label class="form-label">Teléfono <small class="text-muted">(opcional)</small></label>
                      <input type="text" name="telefono_lider" class="form-control" value="<?php echo tpVal('telefono_lider'); ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                      <label class="form-label">Correo <small class="text-muted">(opcional)</small></label>
                      <input type="email" name="correo_lider" class="form-control" value="<?php echo tpVal('correo_lider'); ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                      <label class="form-label">Clase de liderazgo <small class="text-muted">(opcional)</small></label>
                      <input type="text" name="clase_liderazgo" class="form-control" placeholder="Ej: JAC, religioso, deportivo..." value="<?php echo tpVal('clase_liderazgo'); ?>">
                    </div>
                  </div>

                  <hr>
                  <h6 class="fw-bold mb-3">Responsable de ecosalud</h6>
                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <label class="form-label">Funcionario responsable</label>
                      <select name="id_funcionario_ecosalud" class="form-select" required>
                        <option value="">Seleccione...</option>
                        <?php foreach ($funcionarios as $f): ?>
                          <option value="<?php echo (int) $f['id_usuario']; ?>"
                            <?php echo (isset($_POST['id_funcionario_ecosalud']) && $_POST['id_funcionario_ecosalud'] == $f['id_usuario']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($f['nombre'].' '.$f['apellido'], ENT_QUOTES, 'UTF-8'); ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>

                  <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                    <a href="mvc.php?modulo=TerritorioPriorizado&amp;controlador=TerritorioPriorizado&amp;funcion=list" class="btn btn-secondary">Cancelar</a>
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
