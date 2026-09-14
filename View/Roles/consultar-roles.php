<?php
    // =========================================================
    // Consultar Roles
    // Lista los roles registrados y el detalle de sus permisos
    // (qué puede visualizar y hacer cada uno en cada módulo).
    // =========================================================
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

        if(!$idRol || ($valor !== 0 && $valor !== 1)){
            $mensaje = ['tipo' => 'danger', 'texto' => 'Solicitud no válida.'];

        }elseif($valor === 0 && $modelo->usuariosConRol($idRol) > 0){
            $mensaje = ['tipo' => 'warning',
                        'texto' => 'No se puede inhabilitar: hay usuarios activos con este rol.'];

        }elseif($modelo->cambiarEstado($idRol, $valor) !== false){
            $mensaje = ['tipo' => 'success',
                        'texto' => $valor === 1 ? 'Rol habilitado.' : 'Rol inhabilitado.'];

        }else{
            $mensaje = ['tipo' => 'danger', 'texto' => 'No se pudo eliminar el rol.'];
    // Inhabilitar / habilitar rol: ?estado=ID&valor=0|1
    // En este módulo no hay "Eliminar": igual que en zoocriaderos y tanques,
    // el registro se conserva y solo se cambia su estado.
    if(isset($_GET['estado'])){
        $idRol = filter_var($_GET['estado'], FILTER_VALIDATE_INT);
        $valor = filter_var($_GET['valor'] ?? null, FILTER_VALIDATE_INT);

        if(!$idRol || ($valor !== 0 && $valor !== 1)){
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
?>
<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Consultar Roles · SIGuppys</title>
    <meta content="width=device-width, initial-scale=1.0, shrink-to-fit=no" name="viewport" />
    <link rel="icon" href="../../assets/img/siguppys/favicon-32.png" type="image/png" />
    <link rel="apple-touch-icon" href="../../assets/img/siguppys/favicon-180.png" />

    <script src="../../assets/js/plugin/webfont/webfont.min.js"></script>
    <script>
      WebFont.load({
        google: { families: ["Public Sans:300,400,500,600,700"] },
        custom: {
          families: ["Font Awesome 5 Solid","Font Awesome 5 Regular","Font Awesome 5 Brands","simple-line-icons"],
          urls: ["../../assets/css/fonts.min.css"],
        },
        active: function () { sessionStorage.fonts = true; },
      });
    </script>

    <link rel="stylesheet" href="../../assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="../../assets/css/plugins.min.css" />
    <link rel="stylesheet" href="../../assets/css/kaiadmin.min.css" />
    <link rel="stylesheet" href="../../assets/css/siguppys.css" />
  </head>
  <body data-page="roles-consultar">
    <div class="wrapper">
      <!-- Sidebar -->
      <div class="sidebar sidebar-style-2 siguppys-sidebar" data-background-color="white">
        <div class="sidebar-logo">
          <div class="logo-header siguppys-logo-header">
            <a href="../../Web/index.php" class="logo siguppys-logo">
              <span class="siguppys-pin">
                <img src="../../assets/img/siguppys/logo-pin.png" alt="SIGuppys" />
              </span>
              <span class="siguppys-brand">
                <strong>SIGuppys</strong>
                <small>Control Biológico contra el Dengue</small>
              </span>
            </a>
            <div class="nav-toggle">
              <button class="btn btn-toggle toggle-sidebar"><i class="gg-menu-right"></i></button>
              <button class="btn btn-toggle sidenav-toggler"><i class="gg-menu-left"></i></button>
            </div>
          </div>
        </div>

        <div class="sidebar-wrapper scrollbar scrollbar-inner">
          <div class="sidebar-content">
            <ul class="nav nav-secondary">
              <li class="nav-section">
                <span class="sidebar-mini-icon"><i class="fa fa-ellipsis-h"></i></span>
                <h4 class="text-section">Menú</h4>
              </li>

              <li class="nav-item">
                <a href="../../Web/index.php" data-page="resumen">
                  <i class="fas fa-home"></i>
                  <p>Resumen</p>
                </a>
              </li>

              <li class="nav-item submenu">
                <a data-bs-toggle="collapse" href="#navReportes" aria-expanded="false">
                  <i class="fas fa-chart-bar"></i>
                  <p>Reportes</p>
                  <span class="caret"></span>
                </a>
                <div class="collapse" id="navReportes">
                  <ul class="nav nav-collapse">
                    <li><a href="#" data-page="rep-actividades-zoo"><span class="sub-item">Seguimiento de Actividades en los Zoocriaderos</span></a></li>
                    <li><a href="#" data-page="rep-peces-tanque"><span class="sub-item">Peces nacidos o muertos por tanque</span></a></li>
                    <li><a href="#" data-page="rep-tanques-zoo"><span class="sub-item">Tanques por Zoocriadero</span></a></li>
                    <li><a href="#" data-page="rep-terreno-tipo"><span class="sub-item">Actividades de Terreno por Tipo</span></a></li>
                    <li><a href="#" data-page="rep-terreno-auxiliar"><span class="sub-item">Actividades De Terreno Por Auxiliar Responsable</span></a></li>
                    <li><a href="#" data-page="rep-sitios-deposito"><span class="sub-item">Gráfico de Sitios por Tipo de Depósito</span></a></li>
                  </ul>
                </div>
              </li>

              <li class="nav-item">
                <a href="../../View/Zoocriadero/zoocriaderos.php" data-page="zoocriaderos">
                  <i class="fas fa-warehouse"></i>
                  <p>Zoocriaderos</p>
                </a>
              </li>

              <li class="nav-item submenu">
                <a data-bs-toggle="collapse" href="#navTerreno" aria-expanded="false">
                  <i class="fas fa-map-marker-alt"></i>
                  <p>Terreno</p>
                  <span class="caret"></span>
                </a>
                <div class="collapse" id="navTerreno">
                  <ul class="nav nav-collapse">
                    <li><a href="#" data-page="terreno-depositos"><span class="sub-item">Depósitos</span></a></li>
                    <li><a href="#" data-page="terreno-actividades"><span class="sub-item">Actividades</span></a></li>
                    <li><a href="#" data-page="terreno-tipo-depositos"><span class="sub-item">Tipo Depósitos</span></a></li>
                  </ul>
                </div>
              </li>

              <li class="nav-item submenu active">
                <a data-bs-toggle="collapse" href="#navUsuarios" aria-expanded="true">
                  <i class="fas fa-users"></i>
                  <p>Usuarios</p>
                  <span class="caret"></span>
                </a>
                <div class="collapse show" id="navUsuarios">
                  <ul class="nav nav-collapse">
                    <li><a href="#" data-page="usuarios-registrar"><span class="sub-item">Registrar Usuario</span></a></li>
                    <li><a href="#" data-page="usuarios-consultar"><span class="sub-item">Consultar Usuarios</span></a></li>
                    <li><a href="../../View/Roles/registro-roles.php" data-page="roles-registrar"><span class="sub-item">Roles y Permisos</span></a></li>
                    <li><a href="../../View/Roles/consultar-roles.php" data-page="roles-consultar"><span class="sub-item">Consultar Roles</span></a></li>
                  </ul>
                </div>
              </li>

              <li class="nav-item">
                <a href="#" data-page="copia-seguridad">
                  <i class="fas fa-cloud-upload-alt"></i>
                  <p>Copia de seguridad</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="../../View/Configuraciones/configuraciones.php" data-page="configuraciones">
                  <i class="fas fa-cogs"></i>
                  <p>Configuraciones</p>
                </a>
              </li>
            </ul>
          </div>

          <div class="sidebar-footer">
            <a href="#" class="btn-logout">
              <i class="fas fa-sign-out-alt"></i>
              Cerrar Sesión
            </a>
          </div>
        </div>
      </div>
      <!-- End Sidebar -->

      <div class="main-panel">
        <div class="main-header">
          <div class="main-header-logo">
            <div class="logo-header siguppys-logo-header" data-background-color="white">
              <a href="../../Web/index.php" class="logo siguppys-logo">
                <span class="siguppys-pin">
                  <img src="../../assets/img/siguppys/logo-pin.png" alt="SIGuppys" />
                </span>
                <span class="siguppys-brand">
                  <strong>SIGuppys</strong>
                  <small>Control Biológico contra el Dengue</small>
                </span>
              </a>
              <div class="nav-toggle">
                <button class="btn btn-toggle toggle-sidebar"><i class="gg-menu-right"></i></button>
                <button class="btn btn-toggle sidenav-toggler"><i class="gg-menu-left"></i></button>
              </div>
            </div>
          </div>
          <nav class="navbar navbar-header navbar-header-transparent navbar-expand-lg border-bottom">
            <div class="container-fluid"></div>
          </nav>
        </div>

        <div class="container">
          <div class="page-inner">

            <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
              <div>
                <h3 class="fw-bold mb-3">Consultar Roles</h3>
                <h6 class="op-7 mb-2">Usuarios / Roles y Permisos</h6>
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
                          <td colspan="5" class="text-center text-muted py-4">
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
                              <a href="registro-roles.php?id_rol=<?php echo h($r['id_rol']); ?>"
                                 class="btn-icon" title="Editar rol y permisos">
                                <i class="fas fa-pen"></i>
                              </a>
                            <td class="text-center"><?php echo h($r['total_usuarios']); ?></td>
                            <td class="text-center">
                              <?php if((int) $r['estado'] === 1): ?>
                                <span class="badge-estado activo">Activo</span>
                              <?php else: ?>
                                <span class="badge-estado inactivo">Inhabilitado</span>
                              <?php endif; ?>
                            </td>
                            <td class="text-center">
                              <a href="registro-roles.php?id_rol=<?php echo h($r['id_rol']); ?>"
                                 class="btn-icon" title="Editar rol y permisos">
                                <i class="fas fa-pen"></i>
                              </a>
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

    <script src="../../assets/js/core/jquery-3.7.1.min.js"></script>
    <script src="../../assets/js/core/popper.min.js"></script>
    <script src="../../assets/js/core/bootstrap.min.js"></script>
    <script src="../../assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>
    <script src="../../assets/js/kaiadmin.min.js"></script>
    <!-- Aplica el modo oscuro / daltonismo guardado en Configuraciones -->
    <script src="../../assets/js/siguppys-nav.js"></script>
  </body>
</html>
