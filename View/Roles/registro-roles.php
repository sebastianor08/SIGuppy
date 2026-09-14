<?php
    // =========================================================
    // Registro / Edición de Roles
    // Formulario con la matriz Acción / Módulo: las FILAS son las
    // acciones y las COLUMNAS son los módulos.
    //   - Sin ?id_rol  -> INSERT en "rol" + INSERT en "rol_permiso"
    //   - Con  ?id_rol -> UPDATE del rol y reemplazo de sus permisos
    // =========================================================
    include_once '../../lib/validaciones.php';
    include_once '../../Model/Roles/RolesModel.php';

    $modelo  = new RolesModel();
    $mensaje = null;            // ['tipo' => 'success|danger', 'texto' => '...']

    $nombre      = '';
    $descripcion = '';
    $marcados    = [];          // permisos marcados en la matriz

    // ---------- ¿Estamos editando? ----------
    $idRol = filter_var($_POST['id_rol'] ?? $_GET['id_rol'] ?? null, FILTER_VALIDATE_INT);
    $rolEditado = $idRol ? $modelo->buscarRol($idRol) : null;
    if(!$rolEditado){
        $idRol = null;
    }
    $editando = $idRol !== null;

    if($_SERVER['REQUEST_METHOD'] === 'POST'){

        // limpiar() recorta los extremos y colapsa los espacios repetidos
        $nombre      = limpiar($_POST['nombre_rol'] ?? '');
        $descripcion = limpiar($_POST['descripcion'] ?? '');
        $marcados    = $_POST['permisos'] ?? [];

        // Validaciones: un campo lleno solo de espacios NO cuenta como lleno.
        $errorNombre      = validarTexto($nombre, 'Nombre', 3, 50);
        $errorDescripcion = validarTextoOpcional($descripcion, 'Descripcion', 200);

        if($errorNombre !== null){
            $mensaje = ['tipo' => 'danger', 'texto' => $errorNombre];

        }elseif($errorDescripcion !== null){
            $mensaje = ['tipo' => 'danger', 'texto' => $errorDescripcion];

        }elseif($modelo->existeNombreRol($nombre, $idRol)){
            $mensaje = ['tipo' => 'danger', 'texto' => 'Ya existe un rol con el nombre "' . $nombre . '".'];

        }elseif(empty($marcados)){
            $mensaje = ['tipo' => 'danger', 'texto' => 'Debe marcar al menos un permiso para el rol.'];

        }elseif($editando){
            // ---------- UPDATE ----------
            if($modelo->actualizarRolConPermisos($idRol, $nombre, $descripcion, $marcados)){
                $mensaje = [
                    'tipo'  => 'success',
                    'texto' => 'Rol "' . $nombre . '" actualizado correctamente con '
                             . count($marcados) . ' permiso(s).'
                ];
                $rolEditado = $modelo->buscarRol($idRol);
            }else{
                $mensaje = ['tipo' => 'danger', 'texto' => 'No se pudo actualizar el rol: ' . $modelo->ultimoError()];
            }

        }else{
            // ---------- INSERT ----------
            $nuevoId = $modelo->registrarRolConPermisos($nombre, $descripcion, $marcados);

            if($nuevoId){
                $mensaje = [
                    'tipo'  => 'success',
                    'texto' => 'Rol "' . $nombre . '" registrado correctamente con '
                             . count($marcados) . ' permiso(s).'
                ];
                // Se limpia el formulario tras guardar
                $nombre = '';
                $descripcion = '';
                $marcados = [];
            }else{
                $mensaje = ['tipo' => 'danger', 'texto' => 'No se pudo registrar el rol: ' . $modelo->ultimoError()];
            }
        }

    }elseif($editando){
        // Primera carga en modo edición: se traen los datos y la matriz guardada
        $nombre      = $rolEditado['nombre_rol'];
        $descripcion = $rolEditado['descripcion'];
        $marcados    = $modelo->matrizDelRol($idRol);
    }

    $acciones = $modelo->acciones();
    $modulos  = $modelo->modulos();

    // Ayuda para imprimir texto sin romper el HTML
    function h($v){ return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title><?php echo $editando ? "Editar Rol" : "Registro Roles"; ?> · SIGuppys</title>
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
  <body data-page="roles-registrar">
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
                <h3 class="fw-bold mb-3"><?php echo $editando ? 'Editar Rol' : 'Registro Roles'; ?></h3>
                <h6 class="op-7 mb-2">Usuarios / Roles y Permisos<?php echo $editando ? ' / Editar' : ''; ?></h6>
              </div>
              <div class="ms-md-auto py-2 py-md-0">
                <a href="consultar-roles.php" class="btn btn-label-primary btn-round">
                  <i class="fas fa-list me-1"></i> Consultar roles
                </a>
              </div>
            </div>

            <?php if($mensaje): ?>
              <div class="alert alert-<?php echo h($mensaje['tipo']); ?>">
                <?php echo h($mensaje['texto']); ?>
              </div>
            <?php endif; ?>

            <form method="POST" action="registro-roles.php" id="formRol">
              <?php if($editando): ?>
                <input type="hidden" name="id_rol" value="<?php echo h($idRol); ?>">
              <?php endif; ?>
              <div class="card">
                <div class="card-body">

                  <div class="row">
                    <div class="col-md-4">
                      <div class="form-group">
                        <label for="nombre_rol">Nombre:</label>
                        <input type="text" class="form-control" id="nombre_rol" name="nombre_rol"
                               maxlength="50" minlength="3" required placeholder="Ej: Auxiliar"
                               value="<?php echo h($nombre); ?>">
                      </div>
                    </div>
                    <div class="col-md-5">
                      <div class="form-group">
                        <label for="descripcion">Descripcion:</label>
                        <input type="text" class="form-control" id="descripcion" name="descripcion"
                               maxlength="200" placeholder="Ingrese la descripcion"
                               value="<?php echo h($descripcion); ?>">
                      </div>
                    </div>
                  </div>

                  <div class="table-responsive mt-3">
                    <table class="table table-striped align-items-center mb-0" id="tablaPermisos">
                      <thead>
                        <tr>
                          <th>Accion/Modulo</th>
                          <?php foreach($modulos as $m): ?>
                            <th class="text-center"><?php echo h($m['nombre']); ?></th>
                          <?php endforeach; ?>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach($acciones as $a): ?>
                          <tr>
                            <td><?php echo h($a['nombre']); ?></td>
                            <?php foreach($modulos as $m):
                                    $valor = $m['id_modulo'] . '-' . $a['id_accion_permiso'];
                                    $check = in_array($valor, $marcados, true) ? 'checked' : '';
                            ?>
                              <td class="text-center">
                                <input type="checkbox" class="form-check-input"
                                       name="permisos[]" value="<?php echo h($valor); ?>" <?php echo $check; ?>>
                              </td>
                            <?php endforeach; ?>
                          </tr>
                        <?php endforeach; ?>

                        <?php if(empty($acciones) || empty($modulos)): ?>
                          <tr>
                            <td colspan="<?php echo count($modulos) + 1; ?>" class="text-center text-muted py-4">
                              Faltan datos en las tablas <strong>accion_permiso</strong> y/o <strong>modulo</strong>.
                              Ejecute la sección "DATOS INICIALES DE CATÁLOGO" de BD_Dengue_SIGuppy.sql.
                            </td>
                          </tr>
                        <?php endif; ?>
                      </tbody>
                    </table>
                  </div>

                </div>
                <div class="card-action">
                  <button type="submit" class="btn btn-success">
                    <?php echo $editando ? 'Guardar cambios' : 'Registrar'; ?>
                  </button>
                  <?php if($editando): ?>
                    <a href="registro-roles.php" class="btn btn-border">Cancelar edición</a>
                  <?php endif; ?>
                </div>
              </div>
            </form>

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
    <script>
      // Si se marca Registrar, Editar o Eliminar, se marca Consultar
      // del mismo módulo: no tiene sentido poder editar sin poder ver.
      (function(){
        var tabla = document.getElementById('tablaPermisos');
        if(!tabla) return;

        var filas = tabla.querySelectorAll('tbody tr');
        var filaConsultar = null;
        filas.forEach(function(tr){
          if(tr.cells[0] && tr.cells[0].textContent.trim() === 'Consultar'){
            filaConsultar = tr;
          }
        });

        tabla.querySelectorAll('tbody input[type=checkbox]').forEach(function(chk){
          chk.addEventListener('change', function(){
            if(!this.checked || !filaConsultar) return;
            var celda = this.closest('td');
            var col = Array.prototype.indexOf.call(celda.parentNode.cells, celda);
            var destino = filaConsultar.cells[col];
            if(destino){
              var c = destino.querySelector('input[type=checkbox]');
              if(c) c.checked = true;
            }
          });
        });

        document.getElementById('formRol').addEventListener('submit', function(ev){
          var nombre = document.getElementById('nombre_rol');
          // .trim() evita que pase un nombre hecho solo de espacios
          if(nombre.value.trim().length < 3){
            ev.preventDefault();
            nombre.focus();
            alert('El nombre del rol es obligatorio y debe tener al menos 3 caracteres.');
            return;
          }
          if(!tabla.querySelector('tbody input[type=checkbox]:checked')){
            ev.preventDefault();
            alert('Debe marcar al menos un permiso para el rol.');
          }
        });
      })();
    </script>
  </body>
</html>
