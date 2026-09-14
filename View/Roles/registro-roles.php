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

    $basePath       = '../../';
    $pageTitle      = $editando ? 'Editar Rol' : 'Registro Roles';
    $bodyPage       = 'roles-registrar';
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

<?php
    $pageScripts = [];
    include '../partials/footer.php';
?>
  </body>
</html>
