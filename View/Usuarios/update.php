<?php
    $basePath  = '../';
    $rutaBase  = '../';
    $pageTitle = 'Editar Usuario';
    $bodyPage  = 'usuarios-registrar';
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
                <h3 class="fw-bold mb-3">Editar Usuario</h3>
                <h6 class="op-7 mb-2">Usuarios / Gestión de Usuarios / Editar</h6>
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
                <form method="POST" action="mvc.php?modulo=Usuarios&amp;controlador=Usuarios&amp;funcion=postUpdate">
                  <input type="hidden" name="id_usuario" value="<?php echo (int) $usuario['id_usuario']; ?>">
                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <label class="form-label">Nombres</label>
                      <input type="text" name="nombre" class="form-control"
                             value="<?php echo htmlspecialchars($usuario['nombre'], ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                      <label class="form-label">Apellidos</label>
                      <input type="text" name="apellido" class="form-control"
                             value="<?php echo htmlspecialchars($usuario['apellido'], ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                      <label class="form-label">Tipo de documento</label>
                      <select name="id_tipodocumento" class="form-select" required>
                        <?php foreach ($tiposDocumento as $td): ?>
                          <option value="<?php echo (int) $td['id_tipodocumento']; ?>"
                            <?php echo ($usuario['id_tipodocumento'] == $td['id_tipodocumento']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($td['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div class="col-md-6 mb-3">
                      <label class="form-label">Rol</label>
                      <select name="id_rol" class="form-select" required>
                        <?php foreach ($roles as $r): ?>
                          <option value="<?php echo (int) $r['id_rol']; ?>"
                            <?php echo ($usuario['id_rol'] == $r['id_rol']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($r['nombre_rol'], ENT_QUOTES, 'UTF-8'); ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div class="col-md-6 mb-3">
                      <label class="form-label">Correo</label>
                      <input type="email" name="correo" class="form-control"
                             value="<?php echo htmlspecialchars($usuario['correo'], ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                  </div>
                  <p class="text-muted small">
                    Para restablecer la contraseña de este usuario, usa la opción "Olvidé mi contraseña"
                    desde la pantalla de inicio de sesión (ya existente en el sistema), con este correo.
                  </p>
                  <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Guardar cambios</button>
                    <a href="mvc.php?modulo=Usuarios&amp;controlador=Usuarios&amp;funcion=list" class="btn btn-secondary">Cancelar</a>
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
