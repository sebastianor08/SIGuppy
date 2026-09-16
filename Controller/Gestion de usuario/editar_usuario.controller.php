<?php
// Datos de ejemplo (luego se reemplaza por una consulta real a la tabla usuario)
$usuariosEjemplo = [
    [
        'id' => 1, 'nombres' => 'Andrea', 'apellidos' => 'Rivera',
        'correo' => 'andrea@siguppy.com', 'telefono' => '+57 300 123 4987',
        'rol' => 'Administrador', 'activo' => true, 'miembro_desde' => '15 de agosto 2026',
    ],
    [
        'id' => 2, 'nombres' => 'Carlos', 'apellidos' => 'Gómez',
        'correo' => 'carlos@siguppy.com', 'telefono' => '+57 301 555 2211',
        'rol' => 'Auxiliar', 'activo' => false, 'miembro_desde' => '02 de julio 2026',
    ],
    [
        'id' => 3, 'nombres' => 'María', 'apellidos' => 'López',
        'correo' => 'maria@siguppy.com', 'telefono' => '+57 302 888 4432',
        'rol' => 'Coordinador', 'activo' => true, 'miembro_desde' => '20 de mayo 2026',
    ],
    [
        'id' => 4, 'nombres' => 'Juan', 'apellidos' => 'Pérez',
        'correo' => 'juan@siguppy.com', 'telefono' => '+57 304 777 9090',
        'rol' => 'Super Administrador', 'activo' => true, 'miembro_desde' => '10 de enero 2026',
    ],
];

function colorRolEditar($rol) {
    switch ($rol) {
        case 'Super Administrador': return 'info';
        case 'Administrador':       return 'primary';
        case 'Coordinador':         return 'warning';
        case 'Auxiliar':            return 'purple';
        default:                    return 'secondary';
    }
}
function inicialesEditar($nombre, $apellido) {
    return strtoupper(substr($nombre, 0, 1) . substr($apellido, 0, 1));
}
?>

<div class="pt-2 pb-4">
    <h3 class="fw-bold mb-0">Editar Usuario</h3>
</div>

<div class="row">
    <!-- Columna izquierda: selector + tarjeta del usuario -->
    <div class="col-md-4">
        <div class="card card-round">
            <div class="card-body">
                <label class="form-label fw-bold">Seleccionar Usuario Registrado</label>
                <select id="selectUsuario" class="form-select">
                    <?php foreach ($usuariosEjemplo as $u): ?>
                        <option value="<?php echo $u['id']; ?>">
                            <?php echo htmlspecialchars($u['nombres'] . ' ' . $u['apellidos']); ?>
                            (<?php echo htmlspecialchars($u['correo']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="card card-round">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <span class="avatar-title rounded-circle bg-light text-dark border me-3"
                          id="cardIniciales"
                          style="width:48px;height:48px;display:inline-flex;align-items:center;justify-content:center;font-size:16px;">
                    </span>
                    <div>
                        <div class="fw-bold" id="cardNombre"></div>
                        <div class="text-muted small" id="cardCorreo"></div>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small">Rol</span>
                    <span class="badge" id="cardRol"></span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small">Estado</span>
                    <span class="badge" id="cardEstado"></span>
                </div>
                <div class="text-muted small mt-2" id="cardMiembroDesde"></div>
            </div>
        </div>
    </div>

    <!-- Columna derecha: formulario -->
    <div class="col-md-8">
        <div class="card card-round">
            <div class="card-body">
                <h5 class="fw-bold">Editar Información de Usuario</h5>
                <p class="text-muted small mb-4">Actualiza los datos personales, institucionales y el estado de la cuenta.</p>

                <form id="formEditarUsuario">
                    <h6 class="fw-bold text-muted small text-uppercase mb-3">Información Personal</h6>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombres</label>
                            <input type="text" id="inputNombres" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Apellidos</label>
                            <input type="text" id="inputApellidos" class="form-control">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Teléfono</label>
                        <input type="text" id="inputTelefono" class="form-control">
                    </div>

                    <h6 class="fw-bold text-muted small text-uppercase mb-3">Información Institucional</h6>
                    <div class="mb-2">
                        <label class="form-label">Correo Electrónico (único)</label>
                        <input type="email" id="inputCorreo" class="form-control">
                        <div class="form-text text-success">
                            <i class="fa fa-check-circle"></i> Correo disponible en la base de datos
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Rol Asignado</label>
                        <select id="inputRol" class="form-select">
                            <option>Super Administrador</option>
                            <option>Administrador</option>
                            <option>Coordinador</option>
                            <option>Auxiliar</option>
                        </select>
                    </div>

                    <h6 class="fw-bold text-muted small text-uppercase mb-3">Estado de la Cuenta</h6>
                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" id="inputActiva">
                        <label class="form-check-label" for="inputActiva">
                            Cuenta Activa
                            <div class="text-muted small">Desactivar esta opción impedirá el acceso del usuario al sistema.</div>
                        </label>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-label-secondary btn-round px-4">Cancelar</button>
                        <button type="submit" class="btn btn-primary btn-round px-4">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Datos de ejemplo pasados desde PHP a JavaScript
const usuariosData = <?php echo json_encode($usuariosEjemplo); ?>;
const colores = {
    'Super Administrador': 'badge-info',
    'Administrador': 'badge-primary',
    'Coordinador': 'badge-warning',
    'Auxiliar': 'badge-purple'
};

function cargarUsuario(id) {
    const u = usuariosData.find(x => x.id == id);
    if (!u) return;

    document.getElementById('cardIniciales').innerText = (u.nombres[0] + u.apellidos[0]).toUpperCase();
    document.getElementById('cardNombre').innerText = u.nombres + ' ' + u.apellidos;
    document.getElementById('cardCorreo').innerText = u.correo;
    document.getElementById('cardRol').innerText = u.rol;
    document.getElementById('cardRol').className = 'badge ' + (colores[u.rol] || 'badge-secondary');
    document.getElementById('cardEstado').innerText = u.activo ? 'Activo' : 'Inactivo';
    document.getElementById('cardEstado').className = 'badge ' + (u.activo ? 'badge-success' : 'badge-danger');
    document.getElementById('cardMiembroDesde').innerText = 'Miembro desde ' + u.miembro_desde;

    document.getElementById('inputNombres').value = u.nombres;
    document.getElementById('inputApellidos').value = u.apellidos;
    document.getElementById('inputTelefono').value = u.telefono;
    document.getElementById('inputCorreo').value = u.correo;
    document.getElementById('inputRol').value = u.rol;
    document.getElementById('inputActiva').checked = u.activo;
}

document.addEventListener('DOMContentLoaded', function () {
    const select = document.getElementById('selectUsuario');
    select.addEventListener('change', () => cargarUsuario(select.value));
    cargarUsuario(select.value); // carga el primero al abrir la página

    document.getElementById('formEditarUsuario').addEventListener('submit', function (e) {
        e.preventDefault();
        alert('Esto todavía es una vista de ejemplo — falta conectar con la base de datos para guardar de verdad.');
    });
});
</script>