<?php
    $basePath  = '../';
    $rutaBase  = '../';
    $pageTitle = 'Gestión de Usuarios';
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
                <h3 class="fw-bold mb-3">Gestión de Usuarios</h3>
                <h6 class="op-7 mb-2">Usuarios / Gestión de Usuarios</h6>
              </div>
              <div class="ms-md-auto py-2 py-md-0">
                <a href="mvc.php?modulo=Usuarios&amp;controlador=Usuarios&amp;funcion=getCreate" class="btn btn-primary btn-round">
                  <i class="fas fa-plus me-1"></i> Registrar Usuario
                </a>
              </div>
            </div>

            <?php if (isset($_SESSION['usuario_creado_sin_correo'])): ?>
              <?php $info = $_SESSION['usuario_creado_sin_correo']; unset($_SESSION['usuario_creado_sin_correo']); ?>
              <div class="alert alert-warning">
                <strong>Usuario creado, pero el correo no se pudo enviar.</strong><br>
                Entrégale manualmente estos datos de acceso a <?php echo htmlspecialchars($info['correo'], ENT_QUOTES, 'UTF-8'); ?>:
                contraseña temporal <code><?php echo htmlspecialchars($info['contrasena'], ENT_QUOTES, 'UTF-8'); ?></code>.
                <br><small class="text-muted">Motivo: <?php echo htmlspecialchars($info['motivo'] ?? 'desconocido', ENT_QUOTES, 'UTF-8'); ?>.
                Revisa lib/conf/mail_conf.php y que PHPMailer esté instalado (composer install).</small>
              </div>
            <?php elseif (isset($_GET['ok'])): ?>
              <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php
                  $mensajes = [
                    'creado'      => 'Usuario registrado correctamente. Se le envió la contraseña temporal por correo.',
                    'actualizado' => 'Usuario actualizado correctamente.',
                    'estado'      => 'Estado del usuario actualizado.',
                  ];
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
                    <input type="text" id="usuarioBuscar" class="form-control" placeholder="Buscar por nombre o correo...">
                  </div>
                </div>

                <div class="table-responsive">
                  <table class="table align-items-center mb-0" id="tablaUsuarios">
                    <thead class="table-light">
                      <tr>
                        <th>Usuario</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th class="text-center">Estado</th>
                        <th class="text-end">Acciones</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if (empty($usuarios)): ?>
                        <tr><td colspan="5" class="text-center text-muted py-4">Aún no hay usuarios registrados.</td></tr>
                      <?php endif; ?>
                      <?php foreach ($usuarios as $u): ?>
                        <?php $nombreCompleto = $u['nombre'] . ' ' . $u['apellido']; ?>
                        <tr data-texto="<?php echo strtolower(htmlspecialchars($nombreCompleto.' '.$u['correo'], ENT_QUOTES, 'UTF-8')); ?>">
                          <td>
                            <span class="avatar-title rounded-circle bg-light text-dark border me-2"
                                  style="width:32px;height:32px;display:inline-flex;align-items:center;justify-content:center;font-size:12px;">
                              <?php echo strtoupper(substr($u['nombre'], 0, 1) . substr($u['apellido'], 0, 1)); ?>
                            </span>
                            <?php echo htmlspecialchars($nombreCompleto, ENT_QUOTES, 'UTF-8'); ?>
                          </td>
                          <td><?php echo htmlspecialchars($u['correo'], ENT_QUOTES, 'UTF-8'); ?></td>
                          <td><span class="badge badge-primary"><?php echo htmlspecialchars($u['nombre_rol'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                          <td class="text-center">
                            <span class="badge-estado <?php echo $u['estado'] == 1 ? 'activo' : 'inactivo'; ?>">
                              <?php echo $u['estado'] == 1 ? 'Activo' : 'Inactivo'; ?>
                            </span>
                          </td>
                          <td class="text-end">
                            <a href="mvc.php?modulo=Usuarios&amp;controlador=Usuarios&amp;funcion=getUpdate&amp;id=<?php echo (int) $u['id_usuario']; ?>"
                               class="btn btn-icon btn-link btn-sm" title="Editar">
                              <i class="fa fa-pencil-alt text-primary"></i>
                            </a>
                            <a href="mvc.php?modulo=Usuarios&amp;controlador=Usuarios&amp;funcion=cambiarEstado&amp;id=<?php echo (int) $u['id_usuario']; ?>&amp;estado=<?php echo $u['estado'] == 1 ? 0 : 1; ?>"
                               class="btn btn-icon btn-link btn-sm" title="<?php echo $u['estado'] == 1 ? 'Desactivar' : 'Activar'; ?>"
                               onclick="return confirm('¿<?php echo $u['estado'] == 1 ? 'Desactivar' : 'Activar'; ?> a <?php echo htmlspecialchars(addslashes($nombreCompleto), ENT_QUOTES, 'UTF-8'); ?>?');">
                              <i class="fa fa-ban <?php echo $u['estado'] == 1 ? 'text-danger' : 'text-success'; ?>"></i>
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
  document.getElementById('usuarioBuscar').addEventListener('input', function () {
    var texto = this.value.toLowerCase();
    document.querySelectorAll('#tablaUsuarios tbody tr[data-texto]').forEach(function (fila) {
      fila.style.display = fila.dataset.texto.indexOf(texto) !== -1 ? '' : 'none';
    });
  });
</script>

<?php
    $pageScripts = [];
    include __DIR__ . '/../partials/footer.php';
?>
