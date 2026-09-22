<?php
    // =========================================================
    // Consultar Roles
    // Lista los roles registrados y el detalle de sus permisos
    // (qué puede visualizar y hacer cada uno en cada módulo).
    // =========================================================

    // Igual que en registro-roles.php: la sesión y los permisos se
    // revisan ANTES de procesar el cambio de estado por GET, no después.
    $basePath = '../../';
    require_once __DIR__ . '/../../lib/requiere_sesion.php';
    require_once __DIR__ . '/../../lib/permisos.php';
    $permisosRoles = sigPermisosDeModulo('Gestión de Roles');

    include_once '../../lib/validaciones.php';
    include_once '../../Model/Roles/RolesModel.php';

    $modelo  = new RolesModel();
    $mensaje = null;

    // Inhabilitar / habilitar rol: ?estado=ID&valor=0|1
    // En este módulo no hay "Eliminar": igual que en zoocriaderos y tanques,
    // el registro se conserva y solo se cambia su estado.
    if(isset($_GET['estado'])){
        $idRol = filter_var($_GET['estado'], FILTER_VALIDATE_INT);
        $valor = filter_var($_GET['valor'] ?? null, FILTER_VALIDATE_INT);

        if(empty($permisosRoles['inhabilitar'])){
            $mensaje = ['tipo' => 'danger', 'texto' => 'No tienes permiso para inhabilitar/habilitar roles.'];

        }elseif(!$idRol || ($valor !== 0 && $valor !== 1)){
            $mensaje = ['tipo' => 'danger', 'texto' => 'Solicitud no válida.'];

        }elseif($valor === 0 && $modelo->usuariosConRol($idRol) > 0){
            $mensaje = ['tipo' => 'warning',
                        'texto' => 'No se puede inhabilitar: hay usuarios activos con este rol.'];

        }elseif($modelo->cambiarEstado($idRol, $valor) !== false){
            $mensaje = ['tipo' => 'success',
                        'texto' => $valor === 1 ? 'Rol habilitado.' : 'Rol inhabilitado.'];

        }else{
            $mensaje = ['tipo' => 'danger', 'texto' => 'No se pudo cambiar el estado del rol.'];
        }
    }

    $roles = $modelo->listarRoles();

    // Permisos de cada rol, agrupados por módulo
    $detalle = [];
    foreach($roles as $r){
        $porModulo = [];
        foreach($modelo->permisosDelRol($r['id_rol']) as $p){
            $porModulo[$p['modulo']][] = $p['accion'];
        }
        $detalle[$r['id_rol']] = $porModulo;
    }

    function h($v){ return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); }

    $basePath       = '../../';
    $pageTitle      = 'Gestión de Roles';
    $bodyPage       = 'roles-consultar';
    $showRoleSwitch = false;
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
                <h3 class="fw-bold mb-3">Gestión de Roles</h3>
                <h6 class="op-7 mb-2">Usuarios / Gestión de Roles</h6>
              </div>
              <div class="ms-md-auto py-2 py-md-0">
                <a href="registro-roles.php" class="btn btn-primary btn-round">
                  <i class="fas fa-plus me-1"></i> Registrar rol
                </a>
              </div>
            </div>

            <?php if($mensaje): ?>
              <div class="alert alert-<?php echo h($mensaje['tipo']); ?>">
                <?php echo h($mensaje['texto']); ?>
              </div>
            <?php endif; ?>

            <div class="card">
              <div class="card-header">
                <h4 class="card-title mb-0">Roles registrados</h4>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-hover align-items-center mb-0">
                    <thead class="table-light">
                      <tr>
                        <th>#</th>
                        <th>Rol</th>
                        <th>Descripcion</th>
                        <th>Puede visualizar / hacer</th>
                        <th class="text-center">Usuarios</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center">Acciones</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if(empty($roles)): ?>
                        <tr>
                          <td colspan="7" class="text-center text-muted py-4">
                            Aún no hay roles registrados.
                          </td>
                        </tr>
                      <?php else: ?>
                        <?php foreach($roles as $r): ?>
                          <tr>
                            <td><?php echo h($r['id_rol']); ?></td>
                            <td class="fw-bold"><?php echo h($r['nombre_rol']); ?></td>
                            <td><?php echo h($r['descripcion']); ?></td>
                            <td>
                              <?php $perms = $detalle[$r['id_rol']] ?? []; ?>
                              <?php if(empty($perms)): ?>
                                <span class="text-muted">Sin permisos asignados</span>
                              <?php else: ?>
                                <?php foreach($perms as $modulo => $acciones): ?>
                                  <div class="mb-1">
                                    <strong><?php echo h($modulo); ?>:</strong>
                                    <?php foreach($acciones as $ac): ?>
                                      <span class="badge bg-light text-dark border me-1"><?php echo h($ac); ?></span>
                                    <?php endforeach; ?>
                                  </div>
                                <?php endforeach; ?>
                              <?php endif; ?>
                            </td>
                            <td class="text-center"><?php echo h($r['total_usuarios']); ?></td>
                            <td class="text-center">
                              <?php if((int) $r['estado'] === 1): ?>
                                <span class="badge-estado activo">Activo</span>
                              <?php else: ?>
                                <span class="badge-estado inactivo">Inhabilitado</span>
                              <?php endif; ?>
                            </td>
                            <td class="text-center">
                              <?php if(!empty($permisosRoles['editar'])): ?>
                              <a href="registro-roles.php?id_rol=<?php echo h($r['id_rol']); ?>"
                                 class="btn-icon" title="Editar rol y permisos">
                                <i class="fas fa-pen"></i>
                              </a>
                              <?php endif; ?>
                              <?php if(!empty($permisosRoles['inhabilitar'])): ?>
                              <?php if((int) $r['estado'] === 1): ?>
                                <a href="consultar-roles.php?estado=<?php echo h($r['id_rol']); ?>&valor=0"
                                   class="btn-icon text-danger" title="Inhabilitar"
                                   onclick="return confirm('¿Inhabilitar el rol <?php echo h($r['nombre_rol']); ?>?');">
                                  <i class="fas fa-ban"></i>
                                </a>
                              <?php else: ?>
                                <a href="consultar-roles.php?estado=<?php echo h($r['id_rol']); ?>&valor=1"
                                   class="btn-icon text-success" title="Habilitar"
                                   onclick="return confirm('¿Habilitar el rol <?php echo h($r['nombre_rol']); ?>?');">
                                  <i class="fas fa-check-circle"></i>
                                </a>
                              <?php endif; ?>
                              <?php endif; ?>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>

<?php
    $pageScripts = [];
    include '../partials/footer.php';
?>
  </body>
</html>
