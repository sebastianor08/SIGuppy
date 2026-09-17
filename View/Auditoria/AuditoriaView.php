<?php
require_once __DIR__ . '/../../Controller/Auditoria/AuditoriaController.php';

$datos = obtenerDatosAuditoria();
extract($datos);

$basePath  = '../../';
$pageTitle = 'Auditoría';
$bodyPage  = 'auditoria';

$badgesOperacion = [
    'INSERTAR'  => 'bg-success',
    'ACTUALIZAR' => 'bg-primary',
    'ELIMINAR'  => 'bg-danger',
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

                <!-- Filtros -->
                <div class="card card-round mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label">Tabla / módulo</label>
                                <select name="tabla" class="form-select">
                                    <option value="">Todas</option>
                                    <?php foreach ($tablasDisponibles as $t): ?>
                                        <option value="<?= htmlspecialchars($t['tabla_afectada']) ?>" <?= $filtros['tabla'] === $t['tabla_afectada'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($t['tabla_afectada']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Operación</label>
                                <select name="operacion" class="form-select">
                                    <option value="">Todas</option>
                                    <option value="INSERTAR" <?= $filtros['operacion'] === 'INSERTAR' ? 'selected' : '' ?>>Insertar</option>
                                    <option value="ACTUALIZAR" <?= $filtros['operacion'] === 'ACTUALIZAR' ? 'selected' : '' ?>>Actualizar</option>
                                    <option value="ELIMINAR" <?= $filtros['operacion'] === 'ELIMINAR' ? 'selected' : '' ?>>Eliminar</option>
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

                <!-- Pestañas: auditoría general vs. seguimiento de zoocriaderos -->
                <ul class="nav nav-tabs mb-3" id="auditoriaTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabGeneral" type="button" role="tab">
                            Movimientos generales
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabSeguimiento" type="button" role="tab">
                            Seguimiento de Zoocriaderos
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
                                                <th>Tabla / módulo</th>
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
                                                    <td><?= htmlspecialchars($mov['tabla_afectada']) ?></td>
                                                    <td>
                                                        <span class="badge <?= $badgesOperacion[$mov['operacion']] ?? 'bg-secondary' ?>">
                                                            <?= htmlspecialchars($mov['operacion']) ?>
                                                        </span>
                                                    </td>
                                                    <td><?= htmlspecialchars($mov['detalle']) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <p class="small text-muted mt-3 mb-0">Se muestran hasta 300 movimientos más recientes de actividad, tipo de depósito, sitio y actividades de terreno.</p>
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
                                                        <span class="badge <?= $badgesOperacion[$mov['operacion']] ?? 'bg-secondary' ?>">
                                                            <?= htmlspecialchars($mov['operacion']) ?>
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
