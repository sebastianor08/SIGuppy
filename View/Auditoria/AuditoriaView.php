<?php
// La sesión se exige ANTES de consultar la base de datos (antes se
// validaba recién en head.php, con los datos ya consultados).
$basePath = '../../';
require_once __DIR__ . '/../../lib/requiere_sesion.php';

require_once __DIR__ . '/../../Controller/Auditoria/AuditoriaController.php';

$datos = obtenerDatosAuditoria();
extract($datos);

$basePath  = '../../';
$pageTitle = 'Auditoría';
$bodyPage  = 'auditoria';

$badgesOperacion = [
    'INSERT'         => 'bg-success',
    'UPDATE'         => 'bg-primary',
    'DELETE'         => 'bg-danger',
    'HABILITAR'      => 'bg-success',
    'INHABILITAR'    => 'bg-danger',
    'LOGIN_EXITOSO'  => 'bg-info',
    'LOGIN_FALLIDO'  => 'bg-warning',
    'EXPORTAR'       => 'bg-secondary',
];
$etiquetasOperacion = [
    'INSERT'         => 'Insertar',
    'UPDATE'         => 'Actualizar',
    'DELETE'         => 'Eliminar',
    'HABILITAR'      => 'Habilitar',
    'INHABILITAR'    => 'Inhabilitar',
    'LOGIN_EXITOSO'  => 'Inicio de sesión',
    'LOGIN_FALLIDO'  => 'Inicio fallido',
    'EXPORTAR'       => 'Exportar',
];

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
                        <h3 class="fw-bold mb-3">Auditoría</h3>
                        <h6 class="op-7 mb-2">Movimientos de los usuarios en el sistema</h6>
                    </div>
                </div>

                <?php if (!empty($errorRangoFechas)): ?>
                    <div class="alert alert-warning">
                        <?= htmlspecialchars($errorRangoFechas) ?> No se aplicó el filtro de fechas.
                    </div>
                <?php endif; ?>

                <!-- Filtros -->
                <div class="card card-round mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label">Módulo</label>
                                <select name="id_modulo" class="form-select">
                                    <option value="">Todos</option>
                                    <?php foreach ($modulosDisponibles as $m): ?>
                                        <option value="<?= htmlspecialchars($m['id_modulo']) ?>" <?= (string) $filtros['modulo'] === (string) $m['id_modulo'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($m['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Operación</label>
                                <select name="operacion" class="form-select">
                                    <option value="">Todas</option>
                                    <?php foreach ($etiquetasOperacion as $valor => $etiqueta): ?>
                                        <option value="<?= htmlspecialchars($valor) ?>" <?= $filtros['operacion'] === $valor ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($etiqueta) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Usuario</label>
                                <select name="usuario" class="form-select">
                                    <option value="">Todos</option>
                                    <?php foreach ($usuariosDisponibles as $u): ?>
                                        <option value="<?= $u['id_usuario'] ?>" <?= (string) $filtros['id_usuario'] === (string) $u['id_usuario'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($u['nombre_completo']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Desde</label>
                                <input type="date" name="fecha_inicio" class="form-control" value="<?= htmlspecialchars($filtros['fecha_inicio']) ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Hasta</label>
                                <input type="date" name="fecha_fin" class="form-control" value="<?= htmlspecialchars($filtros['fecha_fin']) ?>">
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter me-1"></i> Aplicar Filtros
                                </button>
                                <a href="AuditoriaView.php" class="btn btn-label-secondary">Limpiar</a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Pestañas: auditoría general, seguimiento de zoocriaderos y seguimiento de depósitos -->
                <ul class="nav nav-tabs mb-3" id="auditoriaTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabGeneral" type="button" role="tab">
                            Movimientos generales
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabSeguimiento" type="button" role="tab">
                            Seguimiento de Zoocriadero
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabDeposito" type="button" role="tab">
                            Seguimiento de Depósito
                        </button>
                    </li>
                </ul>

                <div class="tab-content">
                    <!-- Auditoría general -->
                    <div class="tab-pane fade show active" id="tabGeneral" role="tabpanel">
                        <div class="card card-round">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table align-items-center mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Fecha y hora</th>
                                                <th>Usuario</th>
                                                <th>Módulo</th>
                                                <th>Operación</th>
                                                <th>Detalle</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (count($movimientosGeneral) === 0): ?>
                                                <tr class="sig-empty-row">
                                                    <td colspan="5">No hay movimientos con esos filtros</td>
                                                </tr>
                                            <?php endif; ?>
                                            <?php foreach ($movimientosGeneral as $mov): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($mov['fecha_hora']) ?></td>
                                                    <td><?= htmlspecialchars($mov['usuario_responsable']) ?></td>
                                                    <td><?= htmlspecialchars(AuditoriaModel::etiquetaModulo($mov['modulo'], $mov['ambito'] ?? null)) ?></td>
                                                    <td>
                                                        <span class="badge <?= $badgesOperacion[$mov['accion']] ?? 'bg-secondary' ?>">
                                                            <?= htmlspecialchars($etiquetasOperacion[$mov['accion']] ?? $mov['accion']) ?>
                                                        </span>
                                                    </td>
                                                    <td><?= htmlspecialchars($mov['detalle']) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <p class="small text-muted mt-3 mb-0">Se muestran hasta 300 movimientos más recientes de todos los módulos.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Auditoría de seguimiento de zoocriaderos -->
                    <div class="tab-pane fade" id="tabSeguimiento" role="tabpanel">
                        <div class="card card-round">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table align-items-center mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Fecha y hora</th>
                                                <th>Usuario</th>
                                                <th>Seguimiento</th>
                                                <th>Operación</th>
                                                <th>Detalle</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (count($movimientosSeguimiento) === 0): ?>
                                                <tr class="sig-empty-row">
                                                    <td colspan="5">No hay movimientos con esos filtros</td>
                                                </tr>
                                            <?php endif; ?>
                                            <?php foreach ($movimientosSeguimiento as $mov): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($mov['fecha_hora']) ?></td>
                                                    <td><?= htmlspecialchars($mov['usuario_responsable']) ?></td>
                                                    <td>#<?= htmlspecialchars($mov['id_seguimiento']) ?></td>
                                                    <td>
                                                        <span class="badge <?= $badgesOperacion[$mov['accion']] ?? 'bg-secondary' ?>">
                                                            <?= htmlspecialchars($etiquetasOperacion[$mov['accion']] ?? $mov['accion']) ?>
                                                        </span>
                                                    </td>
                                                    <td><?= htmlspecialchars($mov['detalle']) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <p class="small text-muted mt-3 mb-0">Se muestran hasta 300 movimientos más recientes de seguimiento de zoocriaderos.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Auditoría de seguimiento de depósitos -->
                    <div class="tab-pane fade" id="tabDeposito" role="tabpanel">
                        <div class="card card-round">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table align-items-center mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Fecha y hora</th>
                                                <th>Usuario</th>
                                                <th>Seguimiento</th>
                                                <th>Operación</th>
                                                <th>Detalle</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (count($movimientosDeposito) === 0): ?>
                                                <tr class="sig-empty-row">
                                                    <td colspan="5">No hay movimientos con esos filtros</td>
                                                </tr>
                                            <?php endif; ?>
                                            <?php foreach ($movimientosDeposito as $mov): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($mov['fecha_hora']) ?></td>
                                                    <td><?= htmlspecialchars($mov['usuario_responsable']) ?></td>
                                                    <td>#<?= htmlspecialchars($mov['id_seguimiento']) ?></td>
                                                    <td>
                                                        <span class="badge <?= $badgesOperacion[$mov['accion']] ?? 'bg-secondary' ?>">
                                                            <?= htmlspecialchars($etiquetasOperacion[$mov['accion']] ?? $mov['accion']) ?>
                                                        </span>
                                                    </td>
                                                    <td><?= htmlspecialchars($mov['detalle']) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <p class="small text-muted mt-3 mb-0">Se muestran hasta 300 movimientos más recientes de seguimiento de depósitos.</p>
                            </div>
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
<script>

document.addEventListener('DOMContentLoaded', function () {
    var clave  = 'sigAuditoriaTab';
    var tabs   = document.querySelectorAll('#auditoriaTabs [data-bs-target]');

    tabs.forEach(function (btn) {
        btn.addEventListener('shown.bs.tab', function () {
            try { sessionStorage.setItem(clave, btn.getAttribute('data-bs-target')); } catch (e) {}
        });
    });

    var guardada = null;
    try { guardada = sessionStorage.getItem(clave); } catch (e) {}
    if (guardada) {
        var btn = document.querySelector('#auditoriaTabs [data-bs-target="' + guardada + '"]');
        if (btn && !btn.classList.contains('active')) { btn.click(); }
    }
});
</script>
</body>

</html>