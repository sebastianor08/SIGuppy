<?php
echo 'PASO 1';
/**
 * usuarios.controller.php
 * -------------------------
 * Este archivo NO habla directamente con la base de datos.
 * Le PIDE los datos al modelo (usuario.model.php) y arma el HTML.
 */

// 1) Traemos las herramientas que necesitamos: la conexión y el modelo
include_once '../lib/conf/conexion.php';
include_once '../model/usuario.model.php';

// 2) Nos conectamos
$con = conectar();
echo 'PASO 2';

// 3) Revisamos si llegó alguna acción (crear usuario, cambiar estado) ANTES de listar
include_once '../controller/usuarios_acciones.php';
echo 'PASO 3';

// 4) Pedimos los usuarios reales
$usuarios = obtenerUsuarios($con);
echo 'PASO 4';

// 5) Pedimos los roles reales, para llenar el <select> del formulario "Crear Usuario"
$roles = obtenerRoles($con);
echo 'PASO 5';


// --- Funciones para pintar bonito ---

function colorRol($rol) {
    switch ($rol) {
        case 'Administrador':        return 'primary';
        case 'Operario':             return 'purple';
        case 'Coordinador':          return 'warning';
        case 'Superadministrador':   return 'info';
        default:                     return 'secondary';
    }
}

function iniciales($nombre, $apellido) {
    return strtoupper(substr($nombre, 0, 1) . substr($apellido, 0, 1));
}
?>

<div class="d-flex align-items-center justify-content-between pt-2 pb-4">
    <h3 class="fw-bold mb-0">Gestión de Usuarios</h3>
    <button type="button" class="btn btn-primary btn-round" data-bs-toggle="modal" data-bs-target="#modalCrearUsuario">
        <i class="fa fa-plus me-1"></i> Crear Usuario
    </button>
</div>

<div class="card card-round">
    <div class="card-body">

        <div class="d-flex flex-column flex-md-row gap-2 mb-3">
            <div class="input-group" style="max-width: 320px;">
                <span class="input-group-text bg-transparent border-end-0">
                    <i class="fa fa-search"></i>
                </span>
                <input type="text" id="buscarUsuario" class="form-control border-start-0" placeholder="Buscar usuario...">
            </div>
            <select id="filtroRol" class="form-select" style="max-width: 200px;">
                <option value="">Todos los roles</option>
                <?php foreach ($roles as $r): ?>
                    <option value="<?php echo htmlspecialchars($r['nombre_rol']); ?>">
                        <?php echo htmlspecialchars($r['nombre_rol']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="table-responsive">
            <table class="table align-middle" id="tablaUsuarios">
                <thead>
                    <tr>
                        <th>USUARIO</th>
                        <th>CORREO</th>
                        <th>ROL</th>
                        <th>ESTADO</th>
                        <th class="text-end">ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $u): ?>
                    <?php
                        $nombreCompleto = $u['nombre'] . ' ' . $u['apellido'];
                        $estadoTexto = ($u['estado'] == 1) ? 'Activo' : 'Inactivo';
                    ?>
                    <tr data-rol="<?php echo htmlspecialchars($u['nombre_rol']); ?>"
                        data-nombre="<?php echo strtolower($nombreCompleto); ?>">
                        <td>
                            <div class="d-flex align-items-center">
                                <span class="avatar-title rounded-circle bg-light text-dark border me-2"
                                      style="width:36px;height:36px;display:inline-flex;align-items:center;justify-content:center;font-size:13px;">
                                    <?php echo iniciales($u['nombre'], $u['apellido']); ?>
                                </span>
                                <?php echo htmlspecialchars($nombreCompleto); ?>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($u['correo']); ?></td>
                        <td>
                            <span class="badge badge-<?php echo colorRol($u['nombre_rol']); ?>">
                                <?php echo htmlspecialchars($u['nombre_rol']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-<?php echo $u['estado'] == 1 ? 'success' : 'danger'; ?>">
                                <?php echo $estadoTexto; ?>
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="index.php?modulo=editar_usuario&id=<?php echo $u['id_usuario']; ?>"
                               class="btn btn-icon btn-link btn-sm" title="Editar">
                                <i class="fa fa-pencil-alt text-primary"></i>
                            </a>
                            <a href="index.php?modulo=usuarios&accion=cambiar_estado&id=<?php echo $u['id_usuario']; ?>&estado=<?php echo $u['estado'] == 1 ? 0 : 1; ?>"
                               class="btn btn-icon btn-link btn-sm" title="<?php echo $u['estado'] == 1 ? 'Desactivar' : 'Activar'; ?>">
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

<div class="modal fade" id="modalCrearUsuario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="index.php?modulo=usuarios&accion=crear">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Crear Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nombres</label>
                    <input type="text" name="nombre" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Apellidos</label>
                    <input type="text" name="apellido" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Correo</label>
                    <input type="email" name="correo" class="form-control" placeholder="correo@siguppy.com" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Rol</label>
                    <select name="id_rol" class="form-select" required>
                        <?php foreach ($roles as $r): ?>
                            <option value="<?php echo $r['id_rol']; ?>"><?php echo htmlspecialchars($r['nombre_rol']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Contraseña</label>
                    <input type="password" name="contrasena" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const buscar = document.getElementById('buscarUsuario');
    const filtroRol = document.getElementById('filtroRol');
    const filas = document.querySelectorAll('#tablaUsuarios tbody tr');

    function filtrar() {
        const texto = buscar.value.toLowerCase();
        const rol = filtroRol.value;
        filas.forEach(fila => {
            const coincideTexto = fila.dataset.nombre.includes(texto);
            const coincideRol = !rol || fila.dataset.rol === rol;
            fila.style.display = (coincideTexto && coincideRol) ? '' : 'none';
        });
    }

    buscar.addEventListener('input', filtrar);
    filtroRol.addEventListener('change', filtrar);
});
</script>